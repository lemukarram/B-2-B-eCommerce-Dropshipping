<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Models\Product;
use Core\Request;
use Core\Response;
use Core\View;

class ProductController
{
    public function index(Request $request): void
    {
        $page   = max(1, (int)$request->get('page', 1));
        $result = Product::guestList($page, 20);

        View::render('guest/products/index', [
            'products'   => $result['data'],
            'pagination' => $result,
        ], 'guest');
    }

    public function show(Request $request): void
    {
        // guestList uses a custom query that excludes base_price
        // For the show page, query without base_price explicitly
        $product = Product::findBySlug($request->param('slug'));

        if (!$product) {
            Response::abort(404, 'Product not found.');
        }

        // Strip base_price from the product data for guests
        unset($product['base_price']);

        $images = Product::images($product['id']);

        View::render('guest/products/show', [
            'product' => $product,
            'images'  => $images,
        ], 'guest');
    }
}
