<?php

use App\Http\Controllers\Api\Orders\Delivery\NovaPoshtaController;
use App\Http\Controllers\Api\Orders\OrderController;
use App\Http\Controllers\Api\Products\CategoryController;
use App\Http\Controllers\Api\Products\NotificationController;
use App\Http\Controllers\Api\Products\ProductController;
use App\Http\Controllers\Api\Users\ProfileController;
use App\Http\Controllers\Api\Products\ReviewController;
use App\Http\Controllers\Api\Products\ReviewReplyController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\Users\AuthController;
use App\Http\Controllers\Api\Users\CartController;
use App\Http\Controllers\Api\Users\UserController;
use App\Http\Controllers\Api\Users\WishlistController;
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


// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'index']);

    Route::patch('/change-password',[UserController::class,'changePassword']);
    
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
        Route::patch('/{id}',[CartController::class,'update']); // Update product's quantity
        Route::get('/cart-count',[CartController::class, 'getCartCount']); //Get item's count of user's cart
    });

    Route::post('/products/{id}/reviews', [ReviewController::class, 'store']); // Add reviews for a product
    Route::post('/reviews/{id}/reply', [ReviewReplyController::class, 'store']); // Add reply foreview

    Route::post('/notification',[NotificationController::class, 'store']); //Add notification for user

    Route::prefix('orders')->group(function(){
        Route::get('/', [OrderController::class, 'index']); // Get user's orders
        Route::get('/{id}', [OrderController::class, 'show']); // Get order`s products
    });
});
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Products Routes
Route::get('/products', [ProductController::class, 'index']); // Get all products
Route::get('/filter',[ProductController::class,'filter']);  //Get filter
Route::get('/products/{id}', [ProductController::class, 'show']); // Get a specific product
Route::get('/products/{id}/reviews', [ReviewController::class, 'getReviewsByProduct']); // Get reviews for a product
Route::get('/products/search/{name}', [ProductController::class, 'search']); // Search products
Route::get('/popular-products', [ProductController::class, 'popular']); // Get popular products
Route::get('/new-arrivals', [ProductController::class, 'newArrivals']); // Get new arrivals

// Categories Routes
Route::get('/categories', [CategoryController::class, 'index']); // Get all categories
Route::get('/categories/{id}/products', [ProductController::class, 'productsByCategory']); // Get products by category

// Site Settings Routes
Route::get('/site-settings', [SiteSettingController::class, 'index']); // Get site settings

// Nova Poshta Routes
Route::get('/nova-poshta/cities', [NovaPoshtaController::class, 'getCities']); //Get cities
Route::get('/nova-poshta/ware-houses', [NovaPoshtaController::class, 'getWarehousesForCity']); //Get warehouses
Route::get('/nova-poshta/delivery/cost',[NovaPoshtaController::class,'calculateDeliveryCost']); //calculate delivery cost
