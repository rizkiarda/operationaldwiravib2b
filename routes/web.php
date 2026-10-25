<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentPrintController;
use App\Http\Controllers\VendorTrackingController;
use App\Http\Controllers\ClientTrackingController;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/print/surat-jalan/{workOrder}', [DocumentPrintController::class, 'printSuratJalan'])
    ->name('print.surat-jalan');

// 1. Route Live Tracking & Driver Field Action untuk Transporter/Driver
Route::get('/track/{hash}', [VendorTrackingController::class, 'show'])
    ->name('vendor.tracking');

Route::post('/track/{hash}/status', [VendorTrackingController::class, 'updateStatus'])
    ->name('vendor.tracking.update-status');

Route::post('/track/{hash}/upload-pod', [VendorTrackingController::class, 'uploadPod'])
    ->name('vendor.tracking.upload-pod');

// 2. Route Live Tracking Khusus Klien (Read-Only 100%, Aman & Tanpa Bisa Ubah Status)
Route::get('/client/track/{hash}', [ClientTrackingController::class, 'show'])
    ->name('client.tracking');
