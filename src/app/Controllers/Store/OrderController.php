<?php

declare(strict_types=1);

namespace App\Controllers\Store;

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

        View::render('store/orders/index', [
            'orders'     => $result['data'],
            'pagination' => $result,
            'success'    => Session::getFlash('success'),
        ], 'store');
    }

    public function create(Request $request): void
    {
        $sellerId = Auth::parentId() ?: 1;

        // Stores see products with their specific wholesale price
        $products = Product::storeList($sellerId, 1, 500)['data'];

        // Load items from session cart
        $cartItems = Session::get('store_cart', []);
        
        // If product_id is in GET, add it to cart if not already there
        $productId = $request->get('product_id');
        if ($productId) {
            $productId = (int)$productId;
            $exists = false;
            foreach ($cartItems as $item) {
                if ((int)$item['product_id'] === $productId) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $cartItems[] = [
                    'product_id' => $productId,
                    'quantity' => 1,
                    'selling_price' => 0
                ];
                Session::set('store_cart', $cartItems);
            }
        }

        View::render('store/orders/create', [
            'products' => $products,
            'errors'   => Session::errors(),
            'old'      => Session::getFlash('old', []),
            'cartItems' => $cartItems,
        ], 'store');
    }

    public function addToCart(Request $request): void
    {
        $productId = (int)$request->param('id');
        $cartItems = Session::get('store_cart', []);
        
        $exists = false;
        foreach ($cartItems as $item) {
            if ((int)$item['product_id'] === $productId) {
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $cartItems[] = [
                'product_id' => $productId,
                'quantity' => 1,
                'selling_price' => 0
            ];
            Session::set('store_cart', $cartItems);
        }
        
        Response::redirect('/store/orders/create');
    }

    public function removeFromCart(Request $request): void
    {
        $index = (int)$request->param('index');
        $cartItems = Session::get('store_cart', []);
        
        if (isset($cartItems[$index])) {
            unset($cartItems[$index]);
            Session::set('store_cart', array_values($cartItems));
        }
        
        Response::redirect('/store/orders/create');
    }

    public function store(Request $request): void
    {
        $sellerId = Auth::parentId() ?: 1;

        // Clean the phone number of spaces and hyphens before validation
        $phone = $request->post('customer_phone', '');
        $cleanPhone = str_replace([' ', '-'], '', $phone);
        
        // Merge clean phone back into request data for validator to see it
        $requestData = $request->all();
        $requestData['customer_phone'] = $cleanPhone;

        // Regex for: 03..., 923..., +923..., 00923...
        // Total digits (excluding prefix): 3 + 9 digits = 10 digits
        $phoneRegex = '/^((\+92)|(0092)|(92)|(0))?3[0-9]{9}$/';

        $v = new Validator($requestData, [
            'customer_name'     => 'required|max:150',
            'customer_phone'    => 'required|regex:' . $phoneRegex,
            'customer_address'  => 'required',
            'customer_city'     => 'required|max:100',
            'customer_province' => 'required|max:100',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Session::flashOld($request->all()); // Keep the user's original input with spaces/hyphens
            Response::redirect('/store/orders/create');
        }

        $rawItems = $request->post('items', []);

        if (empty($rawItems)) {
            Session::flashErrors(['items' => ['At least one product item is required.']]);
            Response::redirect('/store/orders/create');
        }

        $items = [];
        foreach ($rawItems as $i => $item) {
            if (empty($item['product_id']) || empty($item['selling_price'])) {
                continue;
            }
            $sellingPrice = filter_var($item['selling_price'], FILTER_VALIDATE_FLOAT);
            if ($sellingPrice === false || $sellingPrice <= 0) {
                Session::flashErrors(['items' => ["Item " . ($i + 1) . ": selling price must be a positive number."]]);
                Response::redirect('/store/orders/create');
            }
            $items[] = [
                'product_id'    => (int)$item['product_id'],
                'quantity'      => max(1, (int)($item['quantity'] ?? 1)),
                'selling_price' => number_format($sellingPrice, 2, '.', ''),
            ];
        }

        if (empty($items)) {
            Session::flashErrors(['items' => ['No valid items provided.']]);
            Response::redirect('/store/orders/create');
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
            ], Auth::id(), $sellerId);

            // Clear the cart after successful order
            Session::remove('store_cart');

            Session::flash('success', 'Dropshipping order placed successfully.');
            Response::redirect('/store/orders/' . $orderId);
        } catch (\Throwable $e) {
            Session::flashErrors(['items' => [$e->getMessage()]]);
            Response::redirect('/store/orders/create');
        }
    }

    public function show(Request $request): void
    {
        $order = Order::find((int)$request->param('id'));

        if (!$order || (int)$order['user_id'] !== Auth::id()) {
            Response::abort(404, 'Order not found.');
        }

        $items = Order::itemsFor($order['id']);

        View::render('store/orders/show', [
            'order' => $order,
            'items' => $items,
        ], 'store');
    }
}
