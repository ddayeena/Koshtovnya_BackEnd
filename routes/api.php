<?php

use App\Http\Controllers\Api\Products\CategoryController;
use App\Http\Controllers\Api\Products\ProductController;
use App\Http\Controllers\Api\SiteSettingController;
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

Route::get('/site-settings',[SiteSettingController::class,'index']);
Route::get('/categories', [CategoryController::class,'index']);
Route::get('/popular-products', [ProductController::class,'popular']);

Route::get('/categories/{id}/products',[ProductController::class,'productByCategory']);
Route::get('/products',[ProductController::class,'index']);

Route::get('/products/search/{name}',[ProductController::class,'search']);