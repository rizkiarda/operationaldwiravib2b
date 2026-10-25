<?php

namespace App\Filament\Resources\WorkOrders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WorkOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('wo_number')->label('Nomor Work Order'),
                TextEntry::make('order.order_code')->label('Kode Order / PO'),
                TextEntry::make('transporter.company_name')->label('Vendor Transportir'),
                TextEntry::make('buying_cost')
                    ->label('Biaya Sewa Vendor')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                TextEntry::make('additional_cost')
                    ->label('Biaya Tambahan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                TextEntry::make('margin_profit')
                    ->label('Margin Kotor')
                    ->state(fn ($record) => $record->gross_profit)
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                TextEntry::make('driver_name')->label('Nama Pengemudi'),
                TextEntry::make('driver_phone')->label('No. WhatsApp Driver'),
                TextEntry::make('driver_id_card_no')->label('No. KTP Driver'),
                TextEntry::make('vehicle_plate_no')->label('Nomor Polisi Truk'),
                TextEntry::make('delivery_note_number')->label('Nomor Surat Jalan'),
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'ASSIGNED' => 'Ditugaskan',
                        'ACCEPTED_BY_VENDOR' => 'Disetujui Vendor',
                        'AT_ORIGIN' => 'Tiba di Asal',
                        'LOADED' => 'Muat Selesai',
                        'ON_THE_WAY' => 'Dalam Perjalanan',
                        'AT_DESTINATION' => 'Tiba di Tujuan',
                        'UNLOADED_WAITING_POD' => 'Bongkar (Menunggu e-POD)',
                        'POD_VERIFIED' => 'POD Lengkap',
                        'REJECTED' => 'Ditolak',
                        default => $state,
                    }),
                TextEntry::make('created_at')->label('Dibuat Pada')->dateTime('d M Y, H:i'),
            ]);
    }
}
