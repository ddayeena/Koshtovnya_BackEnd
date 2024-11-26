<?php

use App\Http\Controllers\Api\Products\CategoryController;
use App\Http\Controllers\Api\Products\ProductController;
use App\Http\Controllers\Api\Users\ProfileController;
use App\Http\Controllers\Api\Products\ReviewController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\Users\AuthController;
use App\Http\Controllers\Api\Users\CartController;
use App\Http\Controllers\Api\Users\WishlistController;
use Illuminate\Http\Request;
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
    });
});
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Products Routes
Route::get('/products', [ProductController::class, 'index']); // Get all products
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
