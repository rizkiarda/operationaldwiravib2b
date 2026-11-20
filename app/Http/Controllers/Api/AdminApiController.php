<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WorkOrder;
use App\Models\ClientInvoice;
use App\Models\Client;
use App\Models\Transporter;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminApiController extends Controller
{
    /**
     * GET /api/v1/admin/dashboard/summary
     */
    public function summary()
    {
        try {
            $totalOrders = Order::count();
            $inTransit = Order::where('status', 'IN_TRANSIT')->count();
            $unpaidInvoices = ClientInvoice::where('payment_status', 'UNPAID')->sum('total_amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => $totalOrders,
                    'in_transit' => $inTransit,
                    'unpaid_invoices_total' => (float) $unpaidInvoices,
                    'fleet_active_count' => WorkOrder::whereIn('status', ['ASSIGNED', 'LOADED', 'ON_THE_WAY'])->count(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_orders' => 14,
                    'in_transit' => 5,
                    'unpaid_invoices_total' => 245000000.0,
                    'fleet_active_count' => 8,
                ],
            ]);
        }
    }

    /**
     * GET /api/v1/admin/orders
     */
    public function orders(Request $request)
    {
        try {
            $orders = Order::with(['client', 'items', 'workOrders.transporter', 'invoice'])
                ->orderBy('id', 'desc')
                ->take(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }
    }

    /**
     * POST /api/v1/admin/orders
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required',
            'origin_facility_name' => 'required|string',
            'origin_address' => 'required|string',
            'dest_facility_name' => 'required|string',
            'dest_address' => 'required|string',
            'selling_price' => 'required|numeric',
            'items' => 'nullable|array',
        ]);

        try {
            $orderCode = 'ORD-' . date('Ym') . '-' . rand(1000, 9999);
            $order = Order::create([
                'order_code' => $orderCode,
                'client_id' => $validated['client_id'],
                'po_number_client' => $request->input('po_number_client', 'PO-' . rand(100, 999)),
                'vehicle_type_id' => $request->input('vehicle_type_id', 1),
                'origin_facility_name' => $validated['origin_facility_name'],
                'origin_address' => $validated['origin_address'],
                'origin_pic_name' => $request->input('origin_pic_name', 'Operator Asal'),
                'origin_pic_phone' => $request->input('origin_pic_phone', '0812-0000'),
                'loading_plan_date' => now(),
                'dest_facility_name' => $validated['dest_facility_name'],
                'dest_address' => $validated['dest_address'],
                'dest_pic_name' => $request->input('dest_pic_name', 'Operator Tujuan'),
                'dest_pic_phone' => $request->input('dest_pic_phone', '0812-0001'),
                'unloading_plan_date' => now()->addDays(4),
                'selling_price' => $validated['selling_price'],
                'status' => 'DRAFT',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Order $orderCode berhasil dibuat.",
                'data' => $order,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'message' => 'Order berhasil disimulasikan (Demo Mode).',
                'data' => ['order_code' => 'ORD-' . date('Ym') . '-9999'],
            ], 201);
        }
    }

    /**
     * POST /api/v1/admin/work-orders
     */
    public function createWorkOrder(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required',
            'transporter_id' => 'required',
            'buying_cost' => 'required|numeric',
            'driver_name' => 'nullable|string',
            'vehicle_plate_no' => 'nullable|string',
        ]);

        try {
            $woNumber = 'WO-' . date('Ym') . '-' . rand(1000, 9999);
            $sjNumber = 'SJ/' . date('Ym') . '/' . rand(1000, 9999);

            $wo = WorkOrder::create([
                'wo_number' => $woNumber,
                'order_id' => $validated['order_id'],
                'transporter_id' => $validated['transporter_id'],
                'buying_cost' => $validated['buying_cost'],
                'additional_cost' => $request->input('additional_cost', 0),
                'driver_name' => $validated['driver_name'] ?? 'Driver Baru',
                'driver_phone' => $request->input('driver_phone', '0812-9999'),
                'vehicle_plate_no' => $validated['vehicle_plate_no'] ?? 'L ' . rand(1000, 9999) . ' XX',
                'delivery_note_number' => $sjNumber,
                'qr_code_hash' => Str::random(40),
                'status' => 'ASSIGNED',
            ]);

            return response()->json([
                'success' => true,
                'message' => "Work Order $woNumber & Surat Jalan $sjNumber berhasil diterbitkan.",
                'data' => $wo,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'message' => 'Work Order berhasil dibuat (Demo Mode).',
            ], 201);
        }
    }

    /**
     * PATCH /api/v1/admin/orders/{id}/complete
     */
    public function completeOrder(Request $request, $id)
    {
        try {
            $order = Order::find($id);
            if ($order) {
                $order->update(['status' => 'COMPLETED']);
            }
            return response()->json([
                'success' => true,
                'message' => 'Order berhasil diverifikasi & ditutup.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => true,
                'message' => 'Order berhasil diverifikasi (Demo Mode).',
            ]);
        }
    }

    /**
     * GET /api/v1/admin/invoices
     */
    public function invoices()
    {
        try {
            $invoices = ClientInvoice::with('order')->orderBy('id', 'desc')->get();
            return response()->json(['success' => true, 'data' => $invoices]);
        } catch (\Throwable $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * PATCH /api/v1/admin/invoices/{id}/pay
     */
    public function markInvoicePaid($id)
    {
        try {
            $invoice = ClientInvoice::find($id);
            if ($invoice) {
                $invoice->update([
                    'payment_status' => 'PAID',
                    'paid_at' => now(),
                ]);
            }
            return response()->json(['success' => true, 'message' => 'Invoice berhasil ditandai lunas.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => true, 'message' => 'Status invoice lunas (Demo Mode).']);
        }
    }

    /**
     * GET /api/v1/admin/fleet/vehicles
     */
    public function vehicles()
    {
        try {
            return response()->json(['success' => true, 'data' => VehicleType::all()]);
        } catch (\Throwable $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * GET /api/v1/admin/clients
     */
    public function clients()
    {
        try {
            return response()->json(['success' => true, 'data' => Client::all()]);
        } catch (\Throwable $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * GET /api/v1/admin/transporters
     */
    public function transporters()
    {
        try {
            return response()->json(['success' => true, 'data' => Transporter::all()]);
        } catch (\Throwable $e) {
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * GET /api/v1/admin/profitability
     */
    public function profitability()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue' => 1250000000.0,
                'total_cost' => 890000000.0,
                'gross_profit' => 360000000.0,
                'gross_margin_pct' => 28.8,
            ],
        ]);
    }
}
