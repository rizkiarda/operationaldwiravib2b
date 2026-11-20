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

// 3. Halaman QR Hub Akses Mobile (Scan via HP)
Route::get('/qr', function () {
    $ip = '192.168.1.4';

    $links = [
        [
            'title' => 'Portal Driver Lapangan (Mobile Desk)',
            'desc' => 'Update milestone linier & upload e-POD bukti serah terima foto kargo',
            'url' => "http://{$ip}:8000/track/AzsDFMXavSoMwupbE6qJyYnjQlQhq8Dl",
            'badge' => 'Driver Desk',
        ],
        [
            'title' => 'Portal Live Tracking Klien',
            'desc' => 'Pantau status perjalanan kargo real-time (100% Read-Only)',
            'url' => "http://{$ip}:8000/client/track/2e9ce333bafec8842a181546514b0e2f",
            'badge' => 'Client Portal',
        ],
        [
            'title' => 'Panel Admin Filament',
            'desc' => 'Dashboard operasional logistik, manajemen order & cetak Surat Jalan',
            'url' => "http://{$ip}:8000/admin",
            'badge' => 'Back Office',
        ],
        [
            'title' => 'REST API Driver Tasks (JSON)',
            'desc' => 'Endpoint data JSON tugas aktif untuk aplikasi Flutter / Android',
            'url' => "http://{$ip}:8000/api/v1/driver/tasks",
            'badge' => 'Mobile API',
        ],
    ];

    return view('qr', compact('links', 'ip'));
})->name('qr.hub');
