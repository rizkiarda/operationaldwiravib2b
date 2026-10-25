<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentPrintController extends Controller
{
    /**
     * Cetak Surat Jalan Resmi 4 Rangkap berformat PDF
     */
    public function printSuratJalan(WorkOrder $workOrder)
    {
        $workOrder->load([
            'order.client',
            'order.items',
            'order.vehicleType',
            'transporter'
        ]);

        // Generate QR code data URL (berisi link verifikasi e-POD)
        $verifyUrl = url("/driver/pod/{$workOrder->qr_code_hash}");
        $qrCodeSvg = base64_encode(QrCode::format('svg')->size(100)->generate($verifyUrl));

        $pdf = Pdf::loadView('pdf.surat_jalan', [
            'wo' => $workOrder,
            'qrCode' => $qrCodeSvg,
        ])->setPaper('a4', 'portrait');

        // Bersihkan karakter '/' atau '\' dari nomor surat jalan untuk nama file download yang valid
        $safeFileName = str_replace(['/', '\\'], '-', $workOrder->delivery_note_number);

        return $pdf->stream("Surat-Jalan-{$safeFileName}.pdf");
    }
}
