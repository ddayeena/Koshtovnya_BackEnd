<?php

use App\Http\Controllers\Api\Products\CategoryController;
use App\Http\Controllers\Api\Products\ProductController;
use App\Http\Controllers\Api\Users\ProfileController;
use App\Http\Controllers\Api\Products\ReviewController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\Users\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


Route::get('/products/search/{name}',[ProductController::class,'search']);
Route::get('/popular-products', [ProductController::class,'popular']);
Route::get('/new-arrivals', [ProductController::class,'newArrivals']);

Route::get('/categories', [CategoryController::class,'index']);
Route::get('/categories/{id}/products',[ProductController::class,'productsByCategory']);

Route::get('/products',[ProductController::class,'index']);
Route::get('/products/{id}',[ProductController::class,'show']);
Route::get('/products/{id}/reviews',[ReviewController::class,'getReviewsByProduct']);

Route::get('/site-settings',[SiteSettingController::class,'index']);


