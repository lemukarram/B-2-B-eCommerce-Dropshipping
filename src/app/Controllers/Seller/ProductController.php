<?php

declare(strict_types=1);

namespace App\Controllers\Seller;

use App\Models\Category;
use App\Models\Product;
use Core\Request;
use Core\View;

class ProductController
{
    public function index(Request $request): void
    {
        $page       = max(1, (int)$request->get('page', 1));
        $categoryId = $request->get('category_id') ? (int)$request->get('category_id') : null;

        $result     = Product::sellerList($page, 20, $categoryId);
        $categories = Category::allActive();

        View::render('seller/products/index', [
            'products'   => $result['data'],
            'pagination' => $result,
            'categories' => $categories,
        ], 'seller');
    }

    public function show(Request $request): void
    {
        $product = Product::findBySlug($request->param('slug'));

        if (!$product) {
            \Core\Response::abort(404, 'Product not found.');
        }

        $images = Product::images($product['id']);

        View::render('seller/products/show', [
            'product' => $product,
            'images'  => $images,
        ], 'seller');
    }

    public function exportShopify(Request $request): void
    {
        $products = Product::allForExport();
        $filename = 'shopify_products_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // Shopify CSV Headers
        fputcsv($output, [
            'Handle', 'Title', 'Body (HTML)', 'Vendor', 'Standardized Product Type', 'Custom Product Type', 'Tags', 'Published',
            'Option1 Name', 'Option1 Value', 'Option2 Name', 'Option2 Value', 'Option3 Name', 'Option3 Value',
            'Variant SKU', 'Variant Grams', 'Variant Inventory Tracker', 'Variant Inventory Qty', 'Variant Inventory Policy',
            'Variant Fulfillment Service', 'Variant Price', 'Variant Compare At Price', 'Variant Requires Shipping', 'Variant Taxable',
            'Variant Barcode', 'Image Src', 'Image Position', 'Image Alt Text', 'Gift Card', 'SEO Title', 'SEO Description',
            'Google Shopping / Google Product Category', 'Google Shopping / Gender', 'Google Shopping / Age Group',
            'Google Shopping / MPN', 'Google Shopping / AdWords Grouping', 'Google Shopping / AdWords Labels',
            'Google Shopping / Condition', 'Google Shopping / Custom Product', 'Google Shopping / Custom Label 0',
            'Google Shopping / Custom Label 1', 'Google Shopping / Custom Label 2', 'Google Shopping / Custom Label 3',
            'Google Shopping / Custom Label 4', 'Variant Image', 'Variant Weight Unit', 'Variant Tax Code', 'Cost per item',
            'Included / Pakistan', 'Status'
        ]);

        $appUrl = \App\Models\Setting::get('app_url', 'https://emag.pk');

        foreach ($products as $p) {
            fputcsv($output, [
                $p['slug'],                               // Handle
                $p['title'],                              // Title
                $p['description'],                        // Body (HTML)
                'EMAG.PK',                                // Vendor
                '',                                       // Standardized Product Type
                $p['category_name'],                      // Custom Product Type
                '',                                       // Tags
                'TRUE',                                   // Published
                'Title',                                  // Option1 Name
                'Default Title',                          // Option1 Value
                '', '', '', '',                           // Option2/3
                $p['sku'],                                // Variant SKU
                '0',                                      // Variant Grams
                'shopify',                                // Variant Inventory Tracker
                $p['stock_quantity'],                     // Variant Inventory Qty
                'deny',                                   // Variant Inventory Policy
                'manual',                                 // Variant Fulfillment Service
                $p['base_price'],                         // Variant Price
                '',                                       // Variant Compare At Price
                'TRUE',                                   // Variant Requires Shipping
                'TRUE',                                   // Variant Taxable
                '',                                       // Variant Barcode
                $p['image_path'] ? $appUrl . $p['image_path'] : '', // Image Src
                '1',                                      // Image Position
                $p['title'],                              // Image Alt Text
                'FALSE',                                  // Gift Card
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', // SEO and Google
                '',                                       // Variant Image
                'kg',                                     // Variant Weight Unit
                '',                                       // Variant Tax Code
                '',                                       // Cost per item
                'TRUE',                                   // Included / Pakistan
                'active'                                  // Status
            ]);
        }

        fclose($output);
        exit;
    }

    public function exportWordPress(Request $request): void
    {
        $products = Product::allForExport();
        $filename = 'wordpress_products_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');

        // WooCommerce CSV Headers
        fputcsv($output, [
            'ID', 'Type', 'SKU', 'Name', 'Published', 'Is featured?', 'Visibility in catalog', 'Short description',
            'Description', 'Date sale price starts', 'Date sale price ends', 'Tax status', 'Tax class', 'In stock?',
            'Stock', 'Low stock amount', 'Backorders allowed?', 'Sold individually?', 'Weight (kg)', 'Length (cm)',
            'Width (cm)', 'Height (cm)', 'Allow customer reviews?', 'Purchase note', 'Sale price', 'Regular price',
            'Categories', 'Tags', 'Shipping class', 'Images', 'Download limit', 'Download expiry days', 'Parent',
            'Grouped products', 'Upsells', 'Cross-sells', 'External URL', 'Button text', 'Position'
        ]);

        $appUrl = \App\Models\Setting::get('app_url', 'https://emag.pk');

        foreach ($products as $p) {
            fputcsv($output, [
                '',                                       // ID
                'simple',                                 // Type
                $p['sku'],                                // SKU
                $p['title'],                              // Name
                '1',                                      // Published
                '0',                                      // Is featured?
                'visible',                                // Visibility in catalog
                '',                                       // Short description
                $p['description'],                        // Description
                '', '',                                   // Date sale price
                'taxable',                                // Tax status
                '',                                       // Tax class
                '1',                                      // In stock?
                $p['stock_quantity'],                     // Stock
                '',                                       // Low stock amount
                '0',                                      // Backorders allowed?
                '0',                                      // Sold individually?
                '', '', '', '',                           // Dimensions
                '1',                                      // Allow reviews
                '',                                       // Purchase note
                '',                                       // Sale price
                $p['base_price'],                         // Regular price
                $p['category_name'],                      // Categories
                '',                                       // Tags
                '',                                       // Shipping class
                $p['image_path'] ? $appUrl . $p['image_path'] : '', // Images
                '', '', '', '', '', '', '', '', ''        // Misc
            ]);
        }

        fclose($output);
        exit;
    }
}
