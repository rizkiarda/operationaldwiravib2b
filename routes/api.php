<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverApiController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\PublicTrackingApiController;

/*
|--------------------------------------------------------------------------
| REST API Routes — LogiB2B (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // === 1. Endpoint Publik (Tanpa Autentikasi) ===
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::prefix('public')->group(function () {
        Route::get('track/{code}', [PublicTrackingApiController::class, 'track']);
        Route::get('track/{code}/document', [PublicTrackingApiController::class, 'document']);
    });

    // === 2. Endpoint Driver / Vendor Lapangan ===
    Route::prefix('driver')->group(function () {
        Route::get('tasks', [DriverApiController::class, 'index']);
        Route::get('tasks/{id}', [DriverApiController::class, 'show']);
        Route::patch('tasks/{id}/status', [DriverApiController::class, 'updateStatus']);
        Route::post('tasks/{id}/pod', [DriverApiController::class, 'uploadPod']);
    });

    // === 3. Endpoint Customer / Klien (Shipper & Consignee) ===
    Route::prefix('customer')->group(function () {
        Route::get('orders', [CustomerApiController::class, 'orders']);
        Route::get('orders/{id}', [CustomerApiController::class, 'orderDetail']);
        Route::get('orders/{id}/tracking', [CustomerApiController::class, 'trackingTimeline']);
        Route::get('invoices', [CustomerApiController::class, 'invoices']);
    });

    // === 4. Endpoint Admin / Dispatcher (Control Tower) ===
    Route::prefix('admin')->group(function () {
        Route::get('dashboard/summary', [AdminApiController::class, 'summary']);
        Route::get('orders', [AdminApiController::class, 'orders']);
        Route::post('orders', [AdminApiController::class, 'createOrder']);
        Route::post('work-orders', [AdminApiController::class, 'createWorkOrder']);
        Route::patch('orders/{id}/complete', [AdminApiController::class, 'completeOrder']);
        Route::get('invoices', [AdminApiController::class, 'invoices']);
        Route::patch('invoices/{id}/pay', [AdminApiController::class, 'markInvoicePaid']);
        Route::get('fleet/vehicles', [AdminApiController::class, 'vehicles']);
        Route::get('clients', [AdminApiController::class, 'clients']);
        Route::get('transporters', [AdminApiController::class, 'transporters']);
        Route::get('profitability', [AdminApiController::class, 'profitability']);
    });
});
