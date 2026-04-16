<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Services\FileUploadService;
use App\Services\SlugService;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
use Core\View;

class CategoryController
{
    private FileUploadService $uploader;
    private SlugService $slugService;

    public function __construct()
    {
        $this->uploader    = new FileUploadService();
        $this->slugService = new SlugService();
    }

    public function index(Request $request): void
    {
        View::render('admin/categories/index', [
            'categories' => Category::all('sort_order'),
            'errors'     => Session::errors(),
        ], 'admin');
    }

    public function create(Request $request): void
    {
        View::render('admin/categories/create', [
            'categories' => Category::all('name'),
            'errors'     => Session::errors(),
            'old'        => Session::getFlash('old', []),
        ], 'admin');
    }

    public function store(Request $request): void
    {
        $v = new Validator($request->all(), [
            'name'       => 'required|max:150',
            'sort_order' => 'integer',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Session::flashOld($request->only(['name', 'description', 'sort_order', 'parent_id']));
            Response::redirect('/admin/categories/create');
        }

        $slug  = $this->slugService->generate(trim($request->post('name')), 'categories', 'slug');
        $image = null;

        if ($request->hasFile('image')) {
            $image = $this->uploader->uploadImage($request->file('image'), 'categories');
        }

        Category::insert([
            'parent_id'   => $request->post('parent_id') ?: null,
            'name'        => trim($request->post('name')),
            'slug'        => $slug,
            'description' => trim($request->post('description', '')),
            'image'       => $image,
            'sort_order'  => (int)$request->post('sort_order', 0),
            'is_active'   => $request->post('is_active') ? 1 : 0,
        ]);

        Session::flash('success', 'Category created.');
        Response::redirect('/admin/categories');
    }

    public function edit(Request $request): void
    {
        $category = Category::findOrFail((int)$request->param('id'));

        View::render('admin/categories/edit', [
            'category'   => $category,
            'categories' => Category::all('name'),
            'errors'     => Session::errors(),
        ], 'admin');
    }

    public function update(Request $request): void
    {
        $category = Category::findOrFail((int)$request->param('id'));

        $v = new Validator($request->all(), [
            'name' => 'required|max:150',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Response::redirect('/admin/categories/' . $category['id'] . '/edit');
        }

        $image = $category['image'];

        if ($request->hasFile('image')) {
            if ($image) {
                $this->uploader->delete($image);
            }
            $image = $this->uploader->uploadImage($request->file('image'), 'categories');
        }

        Category::update($category['id'], [
            'parent_id'   => $request->post('parent_id') ?: null,
            'name'        => trim($request->post('name')),
            'description' => trim($request->post('description', '')),
            'image'       => $image,
            'sort_order'  => (int)$request->post('sort_order', 0),
            'is_active'   => $request->post('is_active') ? 1 : 0,
        ]);

        Session::flash('success', 'Category updated.');
        Response::redirect('/admin/categories');
    }

    public function destroy(Request $request): void
    {
        $category = Category::findOrFail((int)$request->param('id'));

        if ($category['image']) {
            $this->uploader->delete($category['image']);
        }

        Category::delete($category['id']);

        Session::flash('success', 'Category deleted.');
        Response::redirect('/admin/categories');
    }
}
