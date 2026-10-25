<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\DeliveryProof;
use App\Models\ShipmentTrackingLog;
use Illuminate\Http\Request;

class VendorTrackingController extends Controller
{
    // Urutan linier milestone pengiriman (Progress hanya bisa maju ke depan)
    protected array $milestoneRanks = [
        'ASSIGNED' => 1,
        'ACCEPTED_BY_VENDOR' => 2,
        'AT_ORIGIN' => 3,
        'LOADED' => 4,
        'ON_THE_WAY' => 5,
        'AT_DESTINATION' => 6,
        'UNLOADED_WAITING_POD' => 7,
        'POD_VERIFIED' => 8,
    ];

    /**
     * Menampilkan halaman live tracking publik untuk vendor/transporter & driver
     */
    public function show(string $hash)
    {
        $workOrder = WorkOrder::with([
            'order.items',
            'order.vehicleType',
            'transporter',
            'deliveryProof',
            'trackingLogs' => function ($q) {
                $q->orderBy('logged_at', 'desc');
            }
        ])->where('qr_code_hash', $hash)->firstOrFail();

        // Metadata tampilan tiap tahap
        $milestones = [
            'ASSIGNED' => ['label' => 'Armada Ditugaskan', 'step' => 1, 'desc' => 'Work order telah diterbitkan'],
            'ACCEPTED_BY_VENDOR' => ['label' => 'Dikonfirmasi Vendor', 'step' => 2, 'desc' => 'Vendor telah mengunci unit armada'],
            'AT_ORIGIN' => ['label' => 'Tiba di Lokasi Muat', 'step' => 3, 'desc' => 'Truk check-in di pabrik/gudang asal'],
            'LOADED' => ['label' => 'Selesai Pemuatan', 'step' => 4, 'desc' => 'Kargo telah dimuat & Surat Jalan aktif'],
            'ON_THE_WAY' => ['label' => 'Dalam Perjalanan', 'step' => 5, 'desc' => 'Armada bergerak menuju titik bongkar'],
            'AT_DESTINATION' => ['label' => 'Tiba di Tujuan', 'step' => 6, 'desc' => 'Truk antre di gudang pembongkaran'],
            'UNLOADED_WAITING_POD' => ['label' => 'Selesai Bongkar', 'step' => 7, 'desc' => 'Kargo telah dibongkar, menunggu upload e-POD'],
            'POD_VERIFIED' => ['label' => 'POD Lengkap & Terverifikasi', 'step' => 8, 'desc' => 'Surat serah terima telah diverifikasi lengkap'],
        ];

        $currentRank = $this->milestoneRanks[$workOrder->status] ?? 1;

        // FILTER HANYA PROGRESS YANG LEBIH TINGGI (TIDAK BISA MEMILIH YANG SUDAH LEWAT)
        $driverSelectableOptions = [
            'AT_ORIGIN' => '📍 Tiba di Lokasi Muat (Check-in Gudang Asal)',
            'LOADED' => '📦 Selesai Pemuatan (Kargo Sudah Naik Truk)',
            'ON_THE_WAY' => '🚛 Dalam Perjalanan Menuju Titik Bongkar',
            'AT_DESTINATION' => '🏁 Tiba di Lokasi Bongkar (Tujuan)',
            'UNLOADED_WAITING_POD' => '⏳ Pembongkaran Selesai (Menunggu e-POD)',
        ];

        $availableNextOptions = [];
        foreach ($driverSelectableOptions as $statusCode => $label) {
            $rank = $this->milestoneRanks[$statusCode] ?? 0;
            if ($rank > $currentRank) {
                $availableNextOptions[$statusCode] = $label;
            }
        }

        return view('vendor.tracking', [
            'wo' => $workOrder,
            'milestones' => $milestones,
            'currentStep' => $currentRank,
            'availableNextOptions' => $availableNextOptions,
        ]);
    }

    /**
     * Aksi Driver memperbarui status perjalanan (Validasi Linier: Tidak bisa mundur)
     */
    public function updateStatus(Request $request, string $hash)
    {
        $workOrder = WorkOrder::where('qr_code_hash', $hash)->firstOrFail();

        $request->validate([
            'status' => 'required|in:AT_ORIGIN,LOADED,ON_THE_WAY,AT_DESTINATION,UNLOADED_WAITING_POD',
            'notes' => 'nullable|string|max:255',
        ]);

        $newStatus = $request->input('status');
        $currentRank = $this->milestoneRanks[$workOrder->status] ?? 1;
        $newRank = $this->milestoneRanks[$newStatus] ?? 0;

        // VALIDASI KEAMANAN: TOLAK JIKA MEMILIH STATUS YANG SAMA ATAU STATUS SEBELUMNYA
        if ($newRank <= $currentRank) {
            return back()->withErrors([
                'status' => 'Status perjalanan hanya dapat bergerak maju. Anda tidak dapat memilih tahap yang sudah selesai!'
            ]);
        }

        // Simpan status baru pada Work Order
        $workOrder->update(['status' => $newStatus]);

        // Catat ke riwayat log tracking lengkap dengan timestamp
        ShipmentTrackingLog::create([
            'work_order_id' => $workOrder->id,
            'milestone' => $newStatus,
            'location_description' => $request->input('notes') ?? 'Pembaruan posisi oleh Driver Lapangan',
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Status pengiriman berhasil diperbarui ke tahap berikutnya!');
    }

    /**
     * Aksi Driver mengunggah bukti serah terima (e-POD) & foto kargo
     */
    public function uploadPod(Request $request, string $hash)
    {
        $workOrder = WorkOrder::where('qr_code_hash', $hash)->firstOrFail();

        $request->validate([
            'recipient_name' => 'required|string|max:100',
            'epod_image' => 'required|image|max:5120',
            'cargo_photo' => 'nullable|image|max:5120',
            'has_discrepancy' => 'nullable|boolean',
            'discrepancy_notes' => 'nullable|string',
        ]);

        $epodPath = $request->file('epod_image')->store('epod_proofs', 'public');
        $cargoPhotoPath = $request->hasFile('cargo_photo') 
            ? $request->file('cargo_photo')->store('cargo_proofs', 'public') 
            : null;

        DeliveryProof::updateOrCreate(
            ['work_order_id' => $workOrder->id],
            [
                'recipient_name' => $request->input('recipient_name'),
                'received_at' => now(),
                'epod_image_url' => 'storage/' . $epodPath,
                'cargo_unloaded_photo_url' => $cargoPhotoPath ? 'storage/' . $cargoPhotoPath : null,
                'has_discrepancy' => $request->boolean('has_discrepancy'),
                'discrepancy_notes' => $request->input('discrepancy_notes'),
                'verification_status' => 'PENDING',
            ]
        );

        // Hanya perbarui status internal Work Order armada ini
        $workOrder->update(['status' => 'UNLOADED_WAITING_POD']);

        // Catat ke log tracking serah terima
        ShipmentTrackingLog::create([
            'work_order_id' => $workOrder->id,
            'milestone' => 'UNLOADED_WAITING_POD',
            'location_description' => 'Bukti serah terima (e-POD) telah diunggah oleh driver',
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Bukti Serah Terima (e-POD) berhasil diunggah!');
    }
}
