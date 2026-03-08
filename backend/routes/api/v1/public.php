<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Public\ProductController;
use App\Http\Controllers\Api\V1\Public\CategoryController;
use App\Http\Controllers\Api\V1\Public\BrandController;
use App\Http\Controllers\Api\V1\Public\RepairServiceController;
use App\Http\Controllers\Api\V1\Public\RepairTrackingController;

// Product catalog
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/featured-products', [ProductController::class, 'featured']);
Route::get('/search', [ProductController::class, 'search']);

// Categories & Brands
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/brands', [BrandController::class, 'index']);

// Repair services (public)
Route::get('/repair-services', [RepairServiceController::class, 'index']);
Route::get('/repair-services/{slug}', [RepairServiceController::class, 'show']);

// Repair tracking (no auth, verified by order_number + phone)
Route::get('/repair-bookings/{orderNumber}', [RepairTrackingController::class, 'track']);
