<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Webhook\WebhookController;

// Payment gateway webhooks (signature-verified, no Sanctum)
Route::post('/momo', [WebhookController::class, 'momo']);
Route::post('/zalopay', [WebhookController::class, 'zalopay']);
Route::post('/vnpay', [WebhookController::class, 'vnpay']);
Route::post('/sepay', [WebhookController::class, 'sepay']);
