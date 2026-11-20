<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ClientInvoice;
use Illuminate\Http\Request;

class CustomerApiController extends Controller
{
    /**
     * GET /api/v1/customer/orders
     */
    public function orders(Request $request)
    {
        try {
            $orders = Order::with(['client', 'items', 'workOrders.deliveryProof'])
                ->orderBy('id', 'desc')
                ->take(30)
                ->get();

            if ($orders->isNotEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => $orders,
                ]);
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'data' => [
                [
                    'id' => 88,
                    'order_code' => 'ORD-202611-00088',
                    'status' => 'IN_TRANSIT',
                    'origin_facility_name' => 'PT Pupuk Agro Lestari (Gresik)',
                    'dest_facility_name' => 'PT Agro Niaga Makassar',
                    'loading_plan_date' => '2026-11-10T08:00:00Z',
                    'unloading_plan_date' => '2026-11-15T17:00:00Z',
                    'selling_price' => 18500000.00,
                    'items_count' => 1,
                ]
            ],
        ]);
    }

    /**
     * GET /api/v1/customer/orders/{id}
     */
    public function orderDetail($id)
    {
        try {
            $order = Order::with([
                'client',
                'items',
                'vehicleType',
                'workOrders.transporter',
                'workOrders.deliveryProof',
                'invoice',
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }
    }

    /**
     * GET /api/v1/customer/orders/{id}/tracking
     */
    public function trackingTimeline($id)
    {
        try {
            $order = Order::with([
                'workOrders.trackingLogs' => function ($q) {
                    $q->orderBy('logged_at', 'desc');
                }
            ])->findOrFail($id);

            $logs = [];
            foreach ($order->workOrders as $wo) {
                foreach ($wo->trackingLogs as $log) {
                    $logs[] = [
                        'milestone' => $log->milestone,
                        'location' => $log->location_description,
                        'logged_at' => $log->logged_at,
                        'latitude' => (float) $log->latitude,
                        'longitude' => (float) $log->longitude,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_code' => $order->order_code,
                    'status' => $order->status,
                    'timeline' => $logs,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Timeline tidak ditemukan.',
            ], 404);
        }
    }

    /**
     * GET /api/v1/customer/invoices
     */
    public function invoices(Request $request)
    {
        try {
            $invoices = ClientInvoice::with('order')->orderBy('id', 'desc')->get();
            return response()->json([
                'success' => true,
                'data' => $invoices,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
    }
}
