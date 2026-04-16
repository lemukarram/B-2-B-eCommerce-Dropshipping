<?php

declare(strict_types=1);

namespace App\Controllers\Guest;

use App\Models\Category;
use App\Models\Product;
use Core\Request;
use Core\View;

class HomeController
{
    public function index(Request $request): void
    {
        $categories = Category::topLevel();
        $products   = Product::guestList(1, 8)['data']; // Featured products on homepage

        View::render('guest/home', [
            'categories' => $categories,
            'products'   => $products,
        ], 'guest');
    }
}
