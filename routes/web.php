<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Inertia\Inertia;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FavoriteController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas y verificadas
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Cuenta
    Route::get('/account', fn() => Inertia::render('Account'))->name('account');

    // Admin Panel
    Route::get('/admin', fn () => Inertia::render('Admin/AdminPanel', [
        'activeRoute' => request()->route()->getName(),
    ]))->middleware('permission:can-access-admin-panel')->name('admin.panel');

    // Vista general de dirección
    Route::get('/address', fn () => Inertia::render('Address/AddressForm', [
        'activeRoute' => request()->route()->getName(),
    ]));

    // Categorías
    Route::get('categories', [CategoryController::class, 'index'])->name('category.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('category.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('category.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('category.edit');
    Route::post('/categories/{category}', [CategoryController::class, 'update'])->name('category.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('category.destroy');
    Route::get('/categories/catalog', [CategoryController::class, 'catalog'])->name('category.catalog');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('category.show');
    Route::get('/categories/{category}/products', [CategoryController::class, 'products'])->name('categories.products');

    // Marcas
    Route::get('brands', [BrandController::class, 'index'])->name('brand.index');
    Route::get('brands/create', [BrandController::class, 'create'])->name('brand.create');
    Route::post('/brands', [BrandController::class, 'store'])->name('brand.store');
    Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brand.edit');
    Route::post('/brands/{brand}', [BrandController::class, 'update'])->name('brand.update');
    Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->name('brand.destroy');
    Route::get('/brands/catalog', [BrandController::class, 'catalog'])->name('brand.catalog');
    Route::get('/brands/{brand}', [BrandController::class, 'show'])->name('brand.show');
    Route::get('/brands/{brand}/products', [BrandController::class, 'products'])->name('brands.products');

    // Productos
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::get('/products', [ProductController::class, 'view'])->name('products.view');
    Route::get('/products/index', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::post('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::get('/products/{product}/confirm-delete', [ProductController::class, 'confirmDelete'])->name('products.confirmDelete');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Favoritos
    Route::resource('favorites', FavoriteController::class)->only(['index', 'store', 'destroy']);
    Route::get('favorites/contains/{product}', [FavoriteController::class, 'contains'])->name('favorites.contains');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // CRUD Direcciones
    Route::get('/address', [LocationController::class, 'index'])->name('address.index');
    Route::get('/address/create', [LocationController::class, 'create'])->name('address.create');
    Route::get('/address/{address}/edit', [LocationController::class, 'edit'])->name('address.edit');
    Route::post('/address', [LocationController::class, 'store'])->name('address.store');
    Route::put('/address/{address}', [LocationController::class, 'update'])->name('address.update');
    Route::delete('/address/{address}', [LocationController::class, 'destroy'])->name('address.destroy');

    // Carrito
    Route::resource('cart', CartController::class)->only(['index', 'update', 'destroy']);
    Route::get('cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::get('cart/contains/{product}', [CartController::class, 'contains'])->name('cart.contains');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/cod', [CheckoutController::class, 'processCod'])->name('checkout.cod');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
    Route::post('/checkout/paypal/order', [CheckoutController::class, 'createPaypalOrder'])->name('checkout.paypal.create');
    Route::post('/checkout/paypal/capture', [CheckoutController::class, 'capturePaypalOrder'])->name('checkout.paypal.capture');
    Route::get('/checkout/buy-now/{product}', [CheckoutController::class, 'buyNow'])->name('checkout.buy-now');

    // Pedidos
    Route::get('/purchases', [OrderController::class, 'index'])->name('purchases.index');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Recursos Admin
    Route::resource('admin/users', UserController::class);
    Route::resource('admin/roles', RoleController::class);
    Route::resource('admin/permissions', PermissionController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/api/cart/status', [CartController::class, 'getCartStatus'])->name('api.cart.status');
});

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
// Ruta para vista home
Route::get('/', [HomeController::class, 'welcome'])->name('welcome');

require __DIR__.'/auth.php';

Route::get('/devoluciones', function () {
    return Inertia::render('ReturnForm');
})->name('returns.form');