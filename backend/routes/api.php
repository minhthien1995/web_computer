<?php

use Illuminate\Support\Facades\Route;

// Health check
Route::get('/health', fn() => response()->json(['status' => 'ok', 'timestamp' => now()]));

// API v1 routes
Route::prefix('v1')->group(function () {
    // Auth routes (public)
    Route::prefix('auth')->group(base_path('routes/api/v1/auth.php'));

    // Public product routes
    Route::prefix('')->group(base_path('routes/api/v1/public.php'));

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(base_path('routes/api/v1/protected.php'));

    // Admin routes
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(base_path('routes/api/v1/admin.php'));

    // Payment webhooks (no auth, signature-verified per gateway)
    Route::prefix('webhooks')->group(base_path('routes/api/v1/webhooks.php'));
});
