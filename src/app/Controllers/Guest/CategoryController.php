<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Models\Category;
use App\Models\Product;
use Core\Request;
use Core\Response;
use Core\View;

class CategoryController
{
    public function index(Request $request): void
    {
        View::render('guest/categories/index', [
            'categories' => Category::topLevel(),
        ], 'guest');
    }

    public function show(Request $request): void
    {
        $category = Category::findBySlug($request->param('slug'));

        if (!$category) {
            Response::abort(404, 'Category not found.');
        }

        $page     = max(1, (int)$request->get('page', 1));
        $result   = Product::guestList($page, 20, $category['id']);
        $children = Category::children($category['id']);

        View::render('guest/categories/show', [
            'category'   => $category,
            'products'   => $result['data'],
            'pagination' => $result,
            'children'   => $children,
        ], 'guest');
    }
}
