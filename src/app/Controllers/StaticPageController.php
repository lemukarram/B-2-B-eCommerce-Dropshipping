<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\Request;
use Core\View;

class StaticPageController
{
    public function terms(Request $request): void
    {
        View::render('guest/pages/terms', ['pageTitle' => 'Terms & Conditions'], 'guest');
    }

    public function privacy(Request $request): void
    {
        View::render('guest/pages/privacy', ['pageTitle' => 'Privacy Policy'], 'guest');
    }

    public function about(Request $request): void
    {
        View::render('guest/pages/about', ['pageTitle' => 'Who We Are'], 'guest');
    }

    public function howItWorks(Request $request): void
    {
        View::render('guest/pages/how_it_works', ['pageTitle' => 'How We Work'], 'guest');
    }

    public function howToRegister(Request $request): void
    {
        View::render('guest/pages/how_to_register', ['pageTitle' => 'How to Register'], 'guest');
    }

    public function faqs(Request $request): void
    {
        View::render('guest/pages/faqs', ['pageTitle' => 'Frequently Asked Questions'], 'guest');
    }

    public function contact(Request $request): void
    {
        View::render('guest/pages/contact', ['pageTitle' => 'Contact Us'], 'guest');
    }
}
