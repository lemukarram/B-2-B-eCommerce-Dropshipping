<?php

declare(strict_types=1);

namespace App\Controllers\Seller;

use App\Models\Category;
use App\Models\CategoryMarkup;
use Core\Auth;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
use Core\View;

class MarkupController
{
    public function index(Request $request): void
    {
        $sellerId = Auth::id();
        $markups  = CategoryMarkup::forSeller($sellerId);
        $categories = Category::all();

        View::render('seller/markups/index', [
            'markups'    => $markups,
            'categories' => $categories,
            'errors'     => Session::errors(),
        ], 'seller');
    }

    public function store(Request $request): void
    {
        $v = new Validator($request->all(), [
            'category_id'  => 'required|integer',
            'markup_type'  => 'required|in:fixed,percent',
            'markup_value' => 'required|numeric|min:0',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Response::redirectBack('/seller/markups');
        }

        CategoryMarkup::setMarkup(
            Auth::id(),
            (int) $request->post('category_id'),
            $request->post('markup_type'),
            (float) $request->post('markup_value')
        );

        Session::flash('success', 'Markup saved successfully.');
        Response::redirect('/seller/markups');
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->param('id');
        $markup = CategoryMarkup::find($id);

        if (!$markup || (int)$markup['seller_id'] !== Auth::id()) {
            Response::abort(403, 'Unauthorized.');
        }

        CategoryMarkup::delete($id);

        Session::flash('success', 'Markup removed.');
        Response::redirect('/seller/markups');
    }
}
