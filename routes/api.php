<?php

use App\Http\Controllers\Api\Orders\Delivery\DeliveryTypeController;
use App\Http\Controllers\Api\Orders\Delivery\NovaPoshtaController;
use App\Http\Controllers\Api\Orders\OrderController;
use App\Http\Controllers\Api\Orders\Payment\PaymentController;
use App\Http\Controllers\Api\Products\CategoryController;
use App\Http\Controllers\Api\Products\NotificationController;
use App\Http\Controllers\Api\Products\ProductController;
use App\Http\Controllers\Api\Products\ReviewController;
use App\Http\Controllers\Api\Products\ReviewReplyController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\Users\AuthController;
use App\Http\Controllers\Api\Users\CartController;
use App\Http\Controllers\Api\Users\UserAddressController;
use App\Http\Controllers\Api\Users\UserController;
use App\Http\Controllers\Api\Users\WishlistController;
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


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-code', [UserController::class, 'verify']);
Route::post('/resend-code', [AuthController::class, 'resendCode']);


// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'index']);
    Route::patch('/change-password', [UserController::class, 'changePassword']); //Change password
    Route::patch('/user', [UserController::class, 'update']);//Change user data

    // User Address Routes
    Route::get('/user-address', [UserAddressController::class, 'show']); // Get users delivery address
    Route::post('/user-address', [UserAddressController::class, 'store']); //Add address to user
    Route::delete('/user-address/{id}', [UserAddressController::class, 'destroy']); //Delete address
    Route::patch('/user-address/{id}', [UserAddressController::class, 'update']); //Update address
    Route::get('/user/phone-number', [UserAddressController::class, 'getUserPhoneNumber']); //Get user`s phone number

    // Wishlist Routes
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']); // Get user's wishlist
        Route::post('/', [WishlistController::class, 'store']); // Add product to wishlist
        Route::delete('{id}', [WishlistController::class, 'destroy']); // Remove product from wishlist
    });

    // Cart Routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']); // Get user's cart
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
        Route::get('/', [OrderController::class, 'index']); // Get user's orders
        Route::get('/{id}', [OrderController::class, 'show']); // Get order`s products
        Route::post('/', [OrderController::class, 'store']); // Add order
    });

    Route::get('/delivery-types', [DeliveryTypeController::class, 'index']); // Get delivery options

    // Nova Poshta Routes
    Route::prefix('nova-poshta')->group(function () {
        Route::get('/cities', [NovaPoshtaController::class, 'getCities']); //Get cities
        Route::get('/ware-houses', [NovaPoshtaController::class, 'getWarehousesForCity']); //Get warehouses
        Route::get('/streets', [NovaPoshtaController::class, 'getStreetsForCity']); //Get streets
        Route::get('/delivery/cost', [NovaPoshtaController::class, 'calculateDeliveryCost']); // Calculate delivery cost    
    });

    Route::post('/payment', [PaymentController::class, 'createPayment']);
});
Route::post('/liqpay-callback', [PaymentController::class, 'callback'])->name('liqpay.callback');


Route::middleware(['auth:sanctum', 'role:admin, superadmin, manager'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']); // Store product
    Route::get('/products/form-data', [ProductController::class, 'formData']); // form data for storing product
});


// Products Routes
Route::get('/products', [ProductController::class, 'index']); // Get all products
Route::get('/filter', [ProductController::class, 'filter']);  //Get filter
Route::get('/products/{id}', [ProductController::class, 'show']); // Get a specific product
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']); // Get reviews for a product
Route::get('/products/search/{name}', [ProductController::class, 'search']); // Search products
Route::get('/popular-products', [ProductController::class, 'popular']); // Get popular products
Route::get('/new-arrivals', [ProductController::class, 'newArrivals']); // Get new arrivals

// Categories Routes
Route::get('/categories', [CategoryController::class, 'index']); // Get all categories
Route::get('/categories/{id}/products', [ProductController::class, 'productsByCategory']); // Get products by category

// Site Settings Routes
Route::get('/site-settings', [SiteSettingController::class, 'index']); // Get site settings

