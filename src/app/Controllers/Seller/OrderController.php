<?php

declare(strict_types=1);

namespace App\Controllers\Seller;

use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
use Core\View;

class OrderController
{
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    public function index(Request $request): void
    {
        $page   = max(1, (int)$request->get('page', 1));
        $result = Order::forUser(Auth::id(), $page, 20);

        View::render('seller/orders/index', [
            'orders'     => $result['data'],
            'pagination' => $result,
            'success'    => Session::getFlash('success'),
        ], 'seller');
    }

    public function create(Request $request): void
    {
        // Sellers can search/browse products and add to order form
        $products = Product::sellerList(1, 200)['data'];

        View::render('seller/orders/create', [
            'products' => $products,
            'errors'   => Session::errors(),
            'old'      => Session::getFlash('old', []),
        ], 'seller');
    }

    public function store(Request $request): void
    {
        $v = new Validator($request->all(), [
            'customer_name'     => 'required|max:150',
            'customer_phone'    => 'required|regex:/^03[0-9]{9}$/',
            'customer_address'  => 'required',
            'customer_city'     => 'required|max:100',
            'customer_province' => 'required|max:100',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Session::flashOld($request->only([
                'customer_name', 'customer_phone', 'customer_address',
                'customer_city', 'customer_province', 'notes',
            ]));
            Response::redirect('/seller/orders/create');
        }

        // Parse items from POST: items[0][product_id], items[0][quantity], items[0][selling_price]
        $rawItems = $request->post('items', []);

        if (empty($rawItems)) {
            Session::flashErrors(['items' => ['At least one product item is required.']]);
            Response::redirect('/seller/orders/create');
        }

        // Validate each item
        $items = [];
        foreach ($rawItems as $i => $item) {
            if (empty($item['product_id']) || empty($item['selling_price'])) {
                continue;
            }
            $sellingPrice = filter_var($item['selling_price'], FILTER_VALIDATE_FLOAT);
            if ($sellingPrice === false || $sellingPrice <= 0) {
                Session::flashErrors(['items' => ["Item " . ($i + 1) . ": selling price must be a positive number."]]);
                Response::redirect('/seller/orders/create');
            }
            $items[] = [
                'product_id'    => (int)$item['product_id'],
                'quantity'      => max(1, (int)($item['quantity'] ?? 1)),
                'selling_price' => number_format($sellingPrice, 2, '.', ''),
            ];
        }

        if (empty($items)) {
            Session::flashErrors(['items' => ['No valid items provided.']]);
            Response::redirect('/seller/orders/create');
        }

        try {
            $orderId = $this->orderService->create([
                'customer_name'     => trim($request->post('customer_name')),
                'customer_phone'    => trim($request->post('customer_phone')),
                'customer_address'  => trim($request->post('customer_address')),
                'customer_city'     => trim($request->post('customer_city')),
                'customer_province' => trim($request->post('customer_province')),
                'notes'             => trim($request->post('notes', '')),
                'items'             => $items,
            ], Auth::id());

            Session::flash('success', 'Order placed successfully.');
            Response::redirect('/seller/orders/' . $orderId);
        } catch (\Throwable $e) {
            Session::flashErrors(['items' => [$e->getMessage()]]);
            Response::redirect('/seller/orders/create');
        }
    }

    public function show(Request $request): void
    {
        $order = Order::find((int)$request->param('id'));

        if (!$order || (int)$order['user_id'] !== Auth::id()) {
            Response::abort(404, 'Order not found.');
        }

        $items = Order::itemsFor($order['id']);

        View::render('seller/orders/show', [
            'order' => $order,
            'items' => $items,
        ], 'seller');
    }
}
