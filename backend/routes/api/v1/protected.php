<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Customer\CartController;
use App\Http\Controllers\Api\V1\Customer\OrderController;
use App\Http\Controllers\Api\V1\Customer\RepairBookingController;
use App\Http\Controllers\Api\V1\Customer\ProfileController;
use App\Http\Controllers\Api\V1\Customer\AddressController;

// Cart
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/items', [CartController::class, 'addItem']);
    Route::put('/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/items/{id}', [CartController::class, 'removeItem']);
    Route::delete('/', [CartController::class, 'clear']);
    Route::post('/merge', [CartController::class, 'merge']);
});

// Orders
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{orderNumber}', [OrderController::class, 'show']);
    Route::post('/{orderNumber}/cancel', [OrderController::class, 'cancel']);
});

// Repair bookings (authenticated)
Route::post('/repair-bookings', [RepairBookingController::class, 'store']);
Route::get('/my-repairs', [RepairBookingController::class, 'index']);
Route::get('/my-repairs/{orderNumber}', [RepairBookingController::class, 'show']);

// Profile & Addresses
Route::prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::put('/', [ProfileController::class, 'update']);
    Route::put('/password', [ProfileController::class, 'updatePassword']);
});

Route::apiResource('addresses', AddressController::class);
