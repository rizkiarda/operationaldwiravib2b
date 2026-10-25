<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ClientTrackingController extends Controller
{
    /**
     * Halaman Live Tracking khusus Klien (Read-Only 100%)
     * Klien bisa memantau posisi kargo & seluruh armada tanpa bisa mengubah status apa pun.
     */
    public function show(string $hash)
    {
        $order = Order::with([
            'client',
            'items',
            'vehicleType',
            'workOrders.transporter',
            'workOrders.deliveryProof',
            'workOrders.trackingLogs' => function ($q) {
                $q->latest('logged_at');
            }
        ])->where('tracking_hash', $hash)->firstOrFail();

        return view('client.tracking', [
            'order' => $order,
        ]);
    }
}
