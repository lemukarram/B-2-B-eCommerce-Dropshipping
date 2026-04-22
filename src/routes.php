<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\Admin\DashboardController    as AdminDashboard;
use App\Controllers\Admin\ProductController      as AdminProduct;
use App\Controllers\Admin\CategoryController     as AdminCategory;
use App\Controllers\Admin\OrderController        as AdminOrder;
use App\Controllers\Admin\SellerController       as AdminSeller;
use App\Controllers\Admin\PaymentController      as AdminPayment;
use App\Controllers\Admin\ReportController       as AdminReport;
use App\Controllers\Admin\SettingsController     as AdminSettings;
use App\Controllers\Admin\BulkUploadController   as AdminBulkUpload;
use App\Controllers\Seller\DashboardController   as SellerDashboard;
use App\Controllers\Seller\ProductController     as SellerProduct;
use App\Controllers\Seller\OrderController       as SellerOrder;
use App\Controllers\Seller\WalletController      as SellerWallet;
use App\Controllers\Seller\ProfileController     as SellerProfile;
use App\Controllers\Seller\MarkupController      as SellerMarkup;
use App\Controllers\Seller\StoreController       as SellerStore;
use App\Controllers\Store\DashboardController    as StoreDashboard;
use App\Controllers\Store\ProductController      as StoreProduct;
use App\Controllers\Store\OrderController        as StoreOrder;
use App\Controllers\Guest\HomeController;
use App\Controllers\Guest\CategoryController     as GuestCategory;
use App\Controllers\Guest\ProductController      as GuestProduct;

// ── Auth ───────────────────────────────────────────────────────────────────
$router->get( '/login',            [AuthController::class, 'showLogin'],    ['guest']);
$router->post('/login',            [AuthController::class, 'login'],        ['guest', 'csrf']);
$router->get( '/register',         [AuthController::class, 'showRegister'], ['guest']);
$router->post('/register',         [AuthController::class, 'register'],     ['guest', 'csrf']);
$router->get( '/register/store',   [AuthController::class, 'showStoreRegister'], ['guest']);
$router->post('/register/store',  [AuthController::class, 'registerStore'],     ['guest', 'csrf']);
$router->get( '/logout',           [AuthController::class, 'logout'],       ['auth']);

// ── Guest / Public ─────────────────────────────────────────────────────────
$router->get('/',                          [HomeController::class,    'index'],   []);
$router->get('/categories',                [GuestCategory::class,     'index'],   []);
$router->get('/categories/:slug',          [GuestCategory::class,     'show'],    []);
$router->get('/products',                  [GuestProduct::class,      'index'],   []);
$router->get('/products/:slug',            [GuestProduct::class,      'show'],    []);

// ── Admin ──────────────────────────────────────────────────────────────────
$router->get( '/admin',                          [AdminDashboard::class, 'index'],   ['admin']);

// Categories
$router->get( '/admin/categories',               [AdminCategory::class,  'index'],   ['admin']);
$router->get( '/admin/categories/create',        [AdminCategory::class,  'create'],  ['admin']);
$router->post('/admin/categories',               [AdminCategory::class,  'store'],   ['admin', 'csrf']);
$router->get( '/admin/categories/:id/edit',      [AdminCategory::class,  'edit'],    ['admin']);
$router->post('/admin/categories/:id',           [AdminCategory::class,  'update'],  ['admin', 'csrf']);
$router->post('/admin/categories/:id/delete',    [AdminCategory::class,  'destroy'], ['admin', 'csrf']);

// Products
$router->get( '/admin/products',                 [AdminProduct::class,   'index'],   ['admin']);
$router->post('/admin/products/quick-update',     [AdminProduct::class,   'quickUpdate'],['admin', 'csrf']);

// Bulk upload (must be above /admin/products/:id routes)
$router->get( '/admin/products/bulk-upload',     [AdminBulkUpload::class, 'show'],   ['admin']);
$router->post('/admin/products/bulk-upload',     [AdminBulkUpload::class, 'process'],['admin', 'csrf']);

// CZ Import
$router->get( '/admin/products/cz-import',       [App\Controllers\Admin\CzBulkUploadController::class, 'show'],   ['admin']);
$router->post('/admin/products/cz-import',      [App\Controllers\Admin\CzBulkUploadController::class, 'process'],['admin', 'csrf']);

$router->get( '/admin/products/create',          [AdminProduct::class,   'create'],  ['admin']);
$router->post('/admin/products',                 [AdminProduct::class,   'store'],   ['admin', 'csrf']);
$router->get( '/admin/products/:id/edit',        [AdminProduct::class,   'edit'],    ['admin']);
$router->post('/admin/products/:id',             [AdminProduct::class,   'update'],  ['admin', 'csrf']);
$router->post('/admin/products/:id/delete',      [AdminProduct::class,   'destroy'], ['admin', 'csrf']);
$router->post('/admin/products/:id/images/:imgId/delete', [AdminProduct::class, 'deleteImage'], ['admin', 'csrf']);

// Orders (admin)
$router->get( '/admin/orders',                   [AdminOrder::class,     'index'],   ['admin']);
$router->get( '/admin/orders/:id',               [AdminOrder::class,     'show'],    ['admin']);
$router->post('/admin/orders/:id/status',        [AdminOrder::class,     'updateStatus'], ['admin', 'csrf']);

// Sellers
$router->get( '/admin/sellers',                  [AdminSeller::class,    'index'],   ['admin']);
$router->get( '/admin/sellers/:id',              [AdminSeller::class,    'show'],    ['admin']);
$router->post('/admin/sellers/:id/approve',      [AdminSeller::class,    'approve'], ['admin', 'csrf']);
$router->post('/admin/sellers/:id/suspend',      [AdminSeller::class,    'suspend'], ['admin', 'csrf']);

// Payments
$router->get( '/admin/payments',                 [AdminPayment::class,   'index'],   ['admin']);
$router->post('/admin/payments/:id/process',     [AdminPayment::class,   'process'], ['admin', 'csrf']);

// Reports
$router->get( '/admin/reports',                  [AdminReport::class,    'index'],   ['admin']);
$router->get( '/admin/reports/orders',           [AdminReport::class,    'orders'],  ['admin']);
$router->get( '/admin/reports/sellers',          [AdminReport::class,    'sellers'], ['admin']);

// Settings
$router->get( '/admin/settings',                 [AdminSettings::class,  'index'],   ['admin']);
$router->post('/admin/settings',                 [AdminSettings::class,  'update'],  ['admin', 'csrf']);

// ── Seller ─────────────────────────────────────────────────────────────────
$router->get( '/seller',                         [SellerDashboard::class, 'index'],  ['seller']);

// Stores management
$router->get( '/seller/stores',                  [SellerStore::class,    'index'],   ['seller']);
$router->get( '/seller/stores/:id',              [SellerStore::class,    'show'],    ['seller']);

// Markups
$router->get( '/seller/markups',                 [SellerMarkup::class,   'index'],   ['seller']);
$router->post('/seller/markups',                 [SellerMarkup::class,   'store'],   ['seller', 'csrf']);
$router->post('/seller/markups/:id/delete',      [SellerMarkup::class,   'destroy'], ['seller', 'csrf']);

// Products (browse with base price)
$router->get( '/seller/products',                [SellerProduct::class,  'index'],   ['seller']);
$router->get( '/seller/products/export/shopify',  [SellerProduct::class,  'exportShopify'], ['seller']);
$router->get( '/seller/products/export/wordpress',[SellerProduct::class,  'exportWordPress'],['seller']);
$router->get( '/seller/products/:slug',          [SellerProduct::class,  'show'],    ['seller']);

// Orders
$router->get( '/seller/orders',                  [SellerOrder::class,    'index'],   ['seller']);
$router->get( '/seller/orders/create',           [SellerOrder::class,    'create'],  ['seller']);
$router->post('/seller/orders',                  [SellerOrder::class,    'store'],   ['seller', 'csrf']);
$router->get( '/seller/orders/:id',              [SellerOrder::class,    'show'],    ['seller']);

// Wallet
$router->get( '/seller/wallet',                  [SellerWallet::class,   'index'],   ['seller']);
$router->post('/seller/wallet/withdraw',         [SellerWallet::class,   'withdraw'],['seller', 'csrf']);

// Profile
$router->get( '/seller/profile',                 [SellerProfile::class,  'index'],   ['seller']);
$router->post('/seller/profile',                 [SellerProfile::class,  'update'],  ['seller', 'csrf']);
$router->post('/seller/profile/payment-methods', [SellerProfile::class,  'storePaymentMethod'],  ['seller', 'csrf']);
$router->post('/seller/profile/payment-methods/:id/delete', [SellerProfile::class, 'deletePaymentMethod'], ['seller', 'csrf']);
$router->post('/seller/profile/payment-methods/:id/primary', [SellerProfile::class, 'setPrimaryPaymentMethod'], ['seller', 'csrf']);

// ── Store (Referral) ───────────────────────────────────────────────────────
$router->get( '/store',                          [StoreDashboard::class, 'index'],   ['store']);

// Products (browse with markup price)
$router->get( '/store/products',                 [StoreProduct::class,   'index'],   ['store']);
$router->get( '/store/products/:slug',           [StoreProduct::class,   'show'],    ['store']);

// Orders (dropshipping workflow)
$router->get( '/store/orders',                   [StoreOrder::class,     'index'],   ['store']);
$router->get( '/store/orders/create',            [StoreOrder::class,     'create'],  ['store']);
$router->post('/store/orders',                   [StoreOrder::class,     'store'],   ['store', 'csrf']);
$router->get( '/store/orders/:id',               [StoreOrder::class,     'show'],    ['store']);

// Wallet
$router->get( '/store/wallet',                  [StoreDashboard::class,  'wallet'],  ['store']);
