<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\AdminProductController;
use App\Http\Controllers\Api\V1\Admin\AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\AdminBrandController;
use App\Http\Controllers\Api\V1\Admin\AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\AdminRepairOrderController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Admin\AdminAnalyticsController;
use App\Http\Controllers\Api\V1\Admin\AdminSettingsController;

Route::apiResource('products', AdminProductController::class);
Route::post('products/{id}/images', [AdminProductController::class, 'uploadImage']);
Route::delete('products/{id}/images/{imageId}', [AdminProductController::class, 'deleteImage']);

Route::apiResource('categories', AdminCategoryController::class);
Route::apiResource('brands', AdminBrandController::class);

Route::get('orders', [AdminOrderController::class, 'index']);
Route::get('orders/{id}', [AdminOrderController::class, 'show']);
Route::put('orders/{id}', [AdminOrderController::class, 'update']);

Route::get('repair-orders', [AdminRepairOrderController::class, 'index']);
Route::get('repair-orders/{id}', [AdminRepairOrderController::class, 'show']);
Route::put('repair-orders/{id}', [AdminRepairOrderController::class, 'update']);
Route::post('repair-orders/{id}/status', [AdminRepairOrderController::class, 'updateStatus']);

Route::get('users', [AdminUserController::class, 'index']);
Route::get('users/{id}', [AdminUserController::class, 'show']);
Route::put('users/{id}', [AdminUserController::class, 'update']);

Route::get('analytics/dashboard', [AdminAnalyticsController::class, 'dashboard']);
Route::get('analytics/sales', [AdminAnalyticsController::class, 'sales']);
Route::get('analytics/repairs', [AdminAnalyticsController::class, 'repairs']);

Route::get('settings', [AdminSettingsController::class, 'index']);
Route::put('settings', [AdminSettingsController::class, 'update']);
