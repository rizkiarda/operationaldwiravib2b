<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('wo_number')
                    ->label('Nomor WO')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('order.order_code')
                    ->label('Kode Order / PO')
                    ->formatStateUsing(fn ($record) => "{$record->order->order_code} (PO: {$record->order->po_number_client})")
                    ->searchable(),
                TextColumn::make('transporter.company_name')
                    ->label('Vendor Transportir')
                    ->searchable(),
                TextColumn::make('vehicle_plate_no')
                    ->label('Nopol Truk')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('driver_name')
                    ->label('Pengemudi (Driver)')
                    ->searchable(),
                TextColumn::make('buying_cost')
                    ->label('Biaya Sewa Vendor')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('margin_profit')
                    ->label('Margin Kotor')
                    ->state(fn ($record) => $record->gross_profit)
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->sortable(),
                TextColumn::make('status')
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
                    })
                    ->color(fn ($state) => match ($state) {
                        'ASSIGNED' => 'gray',
                        'ACCEPTED_BY_VENDOR' => 'info',
                        'AT_ORIGIN' => 'warning',
                        'LOADED' => 'primary',
                        'ON_THE_WAY' => 'primary',
                        'AT_DESTINATION' => 'warning',
                        'UNLOADED_WAITING_POD' => 'danger',
                        'POD_VERIFIED' => 'success',
                        'REJECTED' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\Action::make('track')
                    ->label('Link Tracking')
                    ->icon('heroicon-o-map-pin')
                    ->color('info')
                    ->url(fn (\App\Models\WorkOrder $record): string => route('vendor.tracking', ['hash' => $record->qr_code_hash]))
                    ->openUrlInNewTab(),
                \Filament\Actions\Action::make('print')
                    ->label('Cetak SJ')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (\App\Models\WorkOrder $record): string => route('print.surat-jalan', $record))
                    ->openUrlInNewTab(),
                ViewAction::make()->label('Lihat'),
                EditAction::make()->label('Ubah'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Pilihan'),
                ]),
            ]);
    }
}
