<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Konversi nomor HP Indonesia ke format internasional (misal: 0812xxx -> 62812xxx)
     */
    public static function formatPhoneNumber(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Hapus spasi, strip, tanda kurung, dan tanda plus
        $clean = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali 08, ubah menjadi 628
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        }

        return $clean;
    }

    /**
     * Susun teks pesan penugasan resmi untuk Driver / Vendor
     */
    public static function buildDriverMessage(WorkOrder $wo): string
    {
        $order = $wo->order;
        $trackingUrl = url("/track/{$wo->qr_code_hash}");

        return "🚚 *PENUGASAN SURAT JALAN & WORK ORDER*\n"
            . "🏢 *PT Dwi Ravi Ladang Energi (Natuna)*\n"
            . "──────────────────────\n"
            . "Halo *{$wo->driver_name}*,\n"
            . "Anda telah ditugaskan untuk melakukan pengiriman kargo berikut:\n\n"
            . "📄 *No. Surat Jalan:* {$wo->delivery_note_number}\n"
            . "📋 *No. Work Order:* {$wo->wo_number}\n"
            . "🚛 *Nopol Truk:* {$wo->vehicle_plate_no}\n"
            . "🏢 *Vendor Transporter:* " . ($wo->transporter->company_name ?? '-') . "\n\n"
            . "📍 *Titik Muat (Origin):*\n"
            . "{$order->origin_facility_name}\n"
            . "{$order->origin_address}\n"
            . "PIC Muat: {$order->origin_pic_name} ({$order->origin_pic_phone})\n\n"
            . "🏁 *Titik Bongkar (Destination):*\n"
            . "{$order->dest_facility_name}\n"
            . "{$order->dest_address}\n"
            . "PIC Bongkar: {$order->dest_pic_name} ({$order->dest_pic_phone})\n\n"
            . "──────────────────────\n"
            . "📲 *PORTAL DRIVER (Update Status & Upload Bukti e-POD):*\n"
            . "{$trackingUrl}\n\n"
            . "_Harap buka link di atas saat tiba di lokasi muat, berangkat, tiba di tujuan, dan upload foto Surat Jalan setelah selesai bongkar. Utamakan keselamatan di jalan!_";
    }

    /**
     * Susun teks pesan pelacakan kargo resmi untuk Klien (Shipper)
     */
    public static function buildClientMessage(Order $order): string
    {
        $trackingUrl = route('client.tracking', ['hash' => $order->tracking_hash]);
        $clientName = $order->client->company_name ?? 'Pelanggan Setia';
        $loadingDate = $order->loading_plan_date ? date('d M Y, H:i', strtotime($order->loading_plan_date)) : '-';

        return "📦 *STATUS PENGIRIMAN KARGO (LIVE TRACKING)*\n"
            . "🏢 *PT Dwi Ravi Ladang Energi (Natuna)*\n"
            . "──────────────────────\n"
            . "Yth. *{$clientName}*,\n"
            . "Pesanan logistik Anda telah diproses oleh tim operasional kami:\n\n"
            . "🔖 *Kode Order:* {$order->order_code}\n"
            . "📑 *No. PO Klien:* {$order->po_number_client}\n"
            . "🗓️ *Rencana Muat:* {$loadingDate} WIB\n"
            . "📍 *Asal:* {$order->origin_facility_name}\n"
            . "🏁 *Tujuan:* {$order->dest_facility_name}\n\n"
            . "──────────────────────\n"
            . "🔍 *PANTAU PERJALANAN KARGO REAL-TIME (100% Bebas Akses):*\n"
            . "{$trackingUrl}\n\n"
            . "_Melalui link di atas, Anda dapat memantau posisi armada, waktu tiba, dan melihat salinan digital Surat Jalan / bukti serah terima (e-POD)._\n\n"
            . "Layanan Pelanggan: Jl. Ali Murtopo – Cemaka, Ranai Kota, Kab. Natuna.";
    }

    /**
     * Buat URL link WhatsApp Web / Mobile (One-Click Share)
     */
    public static function getWhatsAppShareUrl(string $phone, string $message): string
    {
        $formattedPhone = self::formatPhoneNumber($phone);
        $encodedMessage = rawurlencode($message);

        return "https://api.whatsapp.com/send?phone={$formattedPhone}&text={$encodedMessage}";
    }

    /**
     * Kirim pesan otomatis melalui API Gateway Fonnte (Jika token tersedia di .env)
     */
    public static function sendViaFonnte(string $phone, string $message): array
    {
        $token = env('FONNTE_API_TOKEN');
        $formattedPhone = self::formatPhoneNumber($phone);

        if (empty($token) || empty($formattedPhone)) {
            return [
                'success' => false,
                'message' => 'Token FONNTE_API_TOKEN belum diatur di .env atau nomor telepon kosong.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $formattedPhone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'body' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Fonnte WhatsApp API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
