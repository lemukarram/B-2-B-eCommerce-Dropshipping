<?php

declare(strict_types=1);

namespace App\Controllers\Store;

use App\Models\Category;
use App\Models\Product;
use Core\Auth;
use Core\Request;
use Core\View;
use Core\Response;

class ProductController
{
    public function index(Request $request): void
    {
        $page       = max(1, (int)$request->get('page', 1));
        $categoryId = $request->get('category_id') ? (int)$request->get('category_id') : null;
        
        // Default to admin (id 1) if no parent seller is assigned
        $sellerId   = Auth::parentId() ?: 1;

        $result     = Product::storeList($sellerId, $page, 24, $categoryId);
        $categories = Category::allActive();

        View::render('store/products/index', [
            'products'   => $result['data'],
            'pagination' => $result,
            'categories' => $categories,
        ], 'store');
    }

    public function show(Request $request): void
    {
        $product  = Product::findBySlug($request->param('slug'));
        $sellerId = Auth::parentId() ?: 1;

        if (!$product) {
            Response::abort(404, 'Product not found.');
        }

        // Fetch markup for this specific product's category
        $rows = Product::query(
            "SELECT scm.markup_type, scm.markup_value
             FROM seller_category_markups scm
             WHERE scm.category_id = ? AND scm.seller_id = ?
             LIMIT 1",
            [$product['category_id'], $sellerId]
        )->fetch();

        $product['wholesale_price'] = Product::calculateWholesalePrice(
            (float)$product['base_price'],
            $rows['markup_type'] ?? null,
            (float)($rows['markup_value'] ?? 0)
        );

        $images = Product::images($product['id']);

        View::render('store/products/show', [
            'product' => $product,
            'images'  => $images,
        ], 'store');
    }
}
