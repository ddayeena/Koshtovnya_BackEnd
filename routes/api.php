<?php

use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Orders\Delivery\DeliveryTypeController;
use App\Http\Controllers\Api\Orders\Delivery\NovaPoshtaController;
use App\Http\Controllers\Api\Orders\OrderController;
use App\Http\Controllers\Api\Orders\Payment\PaymentController;
use App\Http\Controllers\Api\Products\CategoryController;
use App\Http\Controllers\Api\Products\NotificationController;
use App\Http\Controllers\Api\Products\ProductController;
use App\Http\Controllers\Api\Products\ReviewController;
use App\Http\Controllers\Api\Products\ReviewReplyController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\Users\AuthController;
use App\Http\Controllers\Api\Users\CartController;
use App\Http\Controllers\Api\Users\UserAddressController;
use App\Http\Controllers\Api\Users\UserController;
use App\Http\Controllers\Api\Users\WishlistController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|---------------------------------------------------------------------------
| API Routes
|---------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and assigned to
| the "api" middleware group.
|
*/


Route::post('/login', [AuthController::class, 'login']); //Login user
Route::post('/register', [AuthController::class, 'register']); //Register user
Route::post('/verify-code', [UserController::class, 'verify']); //Verify  code for email
Route::post('/resend-code', [AuthController::class, 'sendCode']); //Resend code for email

Route::post('/send-code', [AuthController::class, 'sendCode']); // Send code for reset password
Route::post('/verify-reset-code', [AuthController::class, 'verifyResetCode']); //Verify code for reset password
Route::patch('/reset-password', [AuthController::class, 'resetPassword']); //Change password


// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'show']);
    Route::patch('/change-password', [UserController::class, 'changePassword']); //Change password
    Route::patch('/user/{id}', [UserController::class, 'update']); //Update user data

    // User Address Routes
    Route::get('/user-address', [UserAddressController::class, 'show']); // Get users delivery address
    Route::post('/user-address', [UserAddressController::class, 'store']); //Add address to user
    Route::delete('/user-address/{id}', [UserAddressController::class, 'destroy']); //Delete address
    Route::patch('/user-address/{id}', [UserAddressController::class, 'update']); //Update address
    Route::get('/user/phone-number', [UserAddressController::class, 'getUserPhoneNumber']); //Get user`s phone number

    // Wishlist Routes
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'show']); // Get user's wishlist
        Route::post('/', [WishlistController::class, 'store']); // Add product to wishlist
        Route::delete('{id}', [WishlistController::class, 'destroy']); // Remove product from wishlist
    });

    // Cart Routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'show']); // Get user's cart
        Route::post('/', [CartController::class, 'store']); // Add product to cart
        Route::delete('{id}', [CartController::class, 'destroy']); // Remove product from cart
        Route::patch('/{id}', [CartController::class, 'update']); // Update product's quantity
        Route::get('/cart-count', [CartController::class, 'getCartCount']); //Get item's count of user's cart
    });

    Route::post('/products/{id}/reviews', [ReviewController::class, 'store']); // Add reviews for a product
    Route::post('/reviews/{id}/reply', [ReviewReplyController::class, 'store']); // Add reply foreview
    Route::post('/notification', [NotificationController::class, 'store']); //Add notification for user

    //Order Routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'userOrders']); // Get user's orders
        Route::get('/{id}', [OrderController::class, 'show']); // Get order`s products
        Route::post('/', [OrderController::class, 'store']); // Add order
        Route::post('/{id}/cancel', [OrderController::class, 'cancel']); // Cancel order
    });

    Route::get('/delivery-types', [DeliveryTypeController::class, 'index']); // Get delivery options

    // Nova Poshta Routes
    Route::prefix('nova-poshta')->group(function () {
        Route::get('/cities', [NovaPoshtaController::class, 'getCities']); //Get cities
        Route::get('/ware-houses', [NovaPoshtaController::class, 'getWarehousesForCity']); //Get warehouses
        Route::get('/streets', [NovaPoshtaController::class, 'getStreetsForCity']); //Get streets
        Route::get('/delivery/cost', [NovaPoshtaController::class, 'calculateDeliveryCost']); // Calculate delivery cost    
    });

    Route::post('/payment', [PaymentController::class, 'createPayment']); // Create payment
});
Route::post('/liqpay-callback', [PaymentController::class, 'callback'])->name('liqpay.callback');

// Products Routes
Route::get('/products', [ProductController::class, 'index'])->defaults('isAdminPanel', false); // Get all products
Route::get('/product-filter', [ProductController::class, 'filter']);  //Get  product filter
Route::get('/products/{id}', [ProductController::class, 'show'])->defaults('isAdminPanel', false);; // Get a specific product
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']); // Get reviews for a product
Route::get('/reviews/top-latest', [ReviewController::class, 'topLatest']); // Get last reviews
Route::get('/products/search/{name}', [ProductController::class, 'search']); // Search products
Route::get('/popular-products', [ProductController::class, 'popular']); // Get popular products
Route::get('/new-arrivals', [ProductController::class, 'newArrivals']); // Get new arrivals

// Categories Routes
Route::get('/categories', [CategoryController::class, 'index']); // Get all categories
Route::get('/categories/{id}/products', [ProductController::class, 'productsByCategory']); // Get products by category

// Site Settings Routes
Route::get('/site-settings', [SiteSettingController::class, 'index']); // Get site settings


Route::middleware(['auth:sanctum', 'role:admin,superadmin,manager'])->group(function () {
    Route::get('/admin/profile', [UserController::class, 'show']);
    Route::get('/admin/users', [UserController::class, 'index']); //Get all users
    Route::get('/admin/users/search/{name}', [UserController::class, 'search']); // Search users
    Route::patch('/admin/user/{id}', [UserController::class, 'update']); //Update user data

    Route::get('admin/products/search/{name}', [ProductController::class, 'search']); // Search products
    Route::post('/admin/products', [ProductController::class, 'store']); // Store product
    Route::get('/admin/products/form-data', [ProductController::class, 'formData']); // form data for storing product
    Route::delete('/admin/products/{id}', [ProductController::class, 'destroy']); // Soft Delete Product
    Route::patch('/admin/products/{id}', [ProductController::class, 'update']); //Update Product
    Route::post('/admin/products/{id}/restore', [ProductController::class, 'restore']); // Restore deleted product
    Route::get('/admin/products', [ProductController::class, 'index'])->defaults('isAdminPanel', true); // Get all products
    Route::get('/admin/products/{id}', [ProductController::class, 'show'])->defaults('isAdminPanel', true);; // Get a specific product
    Route::get('/admin/product-filter', [ProductController::class, 'filter']);

    Route::get('/admin/users/{id}/orders', [OrderController::class, 'adminOrders']); //Get user`s orders
    Route::get('/admin/orders/{id}', [OrderController::class, 'show'])->defaults('isAdminPanel', true); //Get user`s orders details
    Route::get('/admin/orders', [OrderController::class, 'index']); //Get all orders
    Route::patch('/admin/orders/{id}', [OrderController::class, 'update']); //Change status of order

    Route::get('/admin/stats/summary', [StatsController::class, 'getSummary']); //Get summary
    Route::get('/admin/stats/order-dynamics', [StatsController::class, 'orderDynamics']); //Get order dynamics
    Route::get('/admin/stats/latest-orders', [StatsController::class, 'latestOrders']); //Get latest orders
    Route::get('/admin/stats/popular-products', [StatsController::class, 'popularProducts']); //Get popular products
    Route::get('/admin/stats/income', [StatsController::class, 'income']); //Get income

});

Route::middleware(['auth:sanctum', 'role:admin,superadmin'])->group(function () {
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy']); //Delete user
    Route::post('/admin/user', [UserController::class, 'store']);   // Add user
});

Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    Route::patch('/admin/site-settings', [SiteSettingController::class, 'update']); // Update site settings
    Route::post('/admin/categories', [CategoryController::class, 'store']); // Add new category
    Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy']); // Delete category
    Route::patch('/admin/categories/{id}', [CategoryController::class, 'update']); // Update category
});

Route::get('/run-migrations', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return '✅ Міграції успішно виконані!';
    } catch (\Exception $e) {
        return '❌ Помилка: ' . $e->getMessage();
    }
});

Route::get('/run-seeders', function () {
    try {
        Artisan::call('db:seed', ['--force' => true]);
        return '✅ Сідери успішно виконані!';
    } catch (\Exception $e) {
        return '❌ Помилка: ' . $e->getMessage();
    }
});

Route::get('/migrate-fresh', function () {
    try {
        Artisan::call('migrate:fresh', ['--force' => true]);
        return '✅ База даних очищена та міграції виконані заново!';
    } catch (\Exception $e) {
        return '❌ Помилка: ' . $e->getMessage();
    }
});
