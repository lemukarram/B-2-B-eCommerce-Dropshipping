<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Services\FileUploadService;
use App\Services\SlugService;
use Core\Auth;
use Core\Database;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\Validator;
use Core\View;

class ProductController
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
        $page       = max(1, (int)$request->get('page', 1));
        $categoryId = $request->get('category_id') ? (int)$request->get('category_id') : null;

        $result     = Product::adminList($page, 20, $categoryId);
        $categories = Category::allActive();

        View::render('admin/products/index', [
            'products'   => $result['data'],
            'pagination' => $result,
            'categories' => $categories,
            'errors'     => Session::errors(),
        ], 'admin');
    }

    public function create(Request $request): void
    {
        View::render('admin/products/create', [
            'categories' => Category::allActive(),
            'errors'     => Session::errors(),
            'old'        => Session::getFlash('old', []),
        ], 'admin');
    }

    public function store(Request $request): void
    {
        $v = new Validator($request->all(), [
            'sku'            => 'required|max:100',
            'title'          => 'required|max:255',
            'base_price'     => 'required|decimal|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id'    => 'integer',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Session::flashOld($request->only(['sku', 'title', 'base_price', 'stock_quantity', 'category_id', 'description']));
            Response::redirect('/admin/products/create');
        }

        $slug = $this->slugService->generate(trim($request->post('title')));

        $productId = Product::insert([
            'sku'            => trim($request->post('sku')),
            'title'          => trim($request->post('title')),
            'slug'           => $slug,
            'category_id'    => $request->post('category_id') ?: null,
            'base_price'     => number_format((float)$request->post('base_price'), 2, '.', ''),
            'stock_quantity' => (int)$request->post('stock_quantity'),
            'description'    => trim($request->post('description', '')),
            'is_active'      => $request->post('is_active') ? 1 : 0,
        ]);

        // Handle image uploads
        $this->handleImageUploads($productId, $request);

        Session::flash('success', 'Product created successfully.');
        Response::redirect('/admin/products');
    }

    public function edit(Request $request): void
    {
        $product = Product::findOrFail((int)$request->param('id'));

        View::render('admin/products/edit', [
            'product'    => $product,
            'images'     => Product::images($product['id']),
            'categories' => Category::allActive(),
            'errors'     => Session::errors(),
        ], 'admin');
    }

    public function update(Request $request): void
    {
        $product = Product::findOrFail((int)$request->param('id'));

        $v = new Validator($request->all(), [
            'title'          => 'required|max:255',
            'base_price'     => 'required|decimal|min:0',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        if ($v->fails()) {
            Session::flashErrors($v->errors());
            Response::redirect('/admin/products/' . $product['id'] . '/edit');
        }

        Product::update($product['id'], [
            'title'          => trim($request->post('title')),
            'category_id'    => $request->post('category_id') ?: null,
            'base_price'     => number_format((float)$request->post('base_price'), 2, '.', ''),
            'stock_quantity' => (int)$request->post('stock_quantity'),
            'description'    => trim($request->post('description', '')),
            'is_active'      => $request->post('is_active') ? 1 : 0,
        ]);

        $this->handleImageUploads($product['id'], $request);

        Session::flash('success', 'Product updated.');
        Response::redirect('/admin/products/' . $product['id'] . '/edit');
    }

    public function destroy(Request $request): void
    {
        $product = Product::findOrFail((int)$request->param('id'));

        // Delete associated images from disk
        foreach (Product::images($product['id']) as $img) {
            $this->uploader->delete($img['image_path']);
        }

        Product::delete($product['id']);

        Session::flash('success', 'Product deleted.');
        Response::redirect('/admin/products');
    }

    public function deleteImage(Request $request): void
    {
        $pdo  = Database::getInstance();
        $imgId = (int)$request->param('imgId');

        $img = $pdo->prepare('SELECT * FROM product_images WHERE id = ? LIMIT 1');
        $img->execute([$imgId]);
        $image = $img->fetch();

        if ($image) {
            $this->uploader->delete($image['image_path']);
            $pdo->prepare('DELETE FROM product_images WHERE id = ?')->execute([$imgId]);
        }

        Response::redirect('/admin/products/' . $request->param('id') . '/edit');
    }

    private function handleImageUploads(int $productId, Request $request): void
    {
        if (!isset($_FILES['images'])) {
            return;
        }

        $pdo = Database::getInstance();

        // Check if this product already has a primary image
        $hasPrimary = (bool) $pdo->prepare(
            'SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_primary = 1'
        )->execute([$productId]);

        $files = $_FILES['images'];
        $count = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $file = is_array($files['name'])
                ? [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ]
                : $files;

            if ($file['error'] !== UPLOAD_ERR_OK) {
                continue;
            }

            try {
                $path      = $this->uploader->uploadImage($file, 'products');
                $isPrimary = (!$hasPrimary && $i === 0) ? 1 : 0;

                $pdo->prepare(
                    'INSERT INTO product_images (product_id, image_path, is_primary, sort_order)
                     VALUES (?, ?, ?, ?)'
                )->execute([$productId, $path, $isPrimary, $i]);

                if ($isPrimary) {
                    $hasPrimary = true;
                }
            } catch (\Throwable $e) {
                error_log('Image upload failed: ' . $e->getMessage());
            }
        }
    }
}
