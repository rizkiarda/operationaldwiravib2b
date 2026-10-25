<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\WorkOrder;
use App\Models\Transporter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_code')
                    ->label('Kode Order')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('client.company_name')
                    ->label('Klien B2B (Shipper)')
                    ->searchable(),
                TextColumn::make('po_number_client')
                    ->label('No. PO Klien')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items')
                    ->suffix(' Jenis Barang')
                    ->badge()
                    ->color('info'),
                TextColumn::make('cargo_category')
                    ->label('Kategori Muatan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'GENERAL_CARGO' => 'General Cargo',
                        'FMCG_DRY' => 'FMCG / Dry',
                        'FRAGILE_ELECTRONICS' => 'Fragile / Elektronik',
                        'COLD_CHAIN' => 'Cold Chain',
                        'HEAVY_INDUSTRIAL' => 'Heavy Industrial',
                        'HAZMAT_DANGEROUS' => 'Hazmat',
                        default => $state,
                    }),
                TextColumn::make('origin_facility_name')
                    ->label('Asal (Origin)')
                    ->searchable(),
                TextColumn::make('dest_facility_name')
                    ->label('Tujuan (Destination)')
                    ->searchable(),
                TextColumn::make('loading_plan_date')
                    ->label('Rencana Muat')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('selling_price')
                    ->label('Tarif Jual Klien')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'DRAFT' => 'Draft',
                        'CONFIRMED' => 'Terkonfirmasi',
                        'WO_DISPATCHED' => 'WO Diterbitkan',
                        'IN_TRANSIT' => 'Dalam Perjalanan',
                        'DELIVERED' => 'Terkirim',
                        'COMPLETED' => 'Selesai',
                        'CANCELLED' => 'Dibatalkan',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'DRAFT' => 'gray',
                        'CONFIRMED' => 'warning',
                        'WO_DISPATCHED' => 'info',
                        'IN_TRANSIT' => 'primary',
                        'DELIVERED' => 'success',
                        'COMPLETED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                // TOMBOL TERBITKAN WO LANGSUNG DARI BARIS ORDER
                Action::make('create_wo')
                    ->label('Terbitkan WO')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->modalHeading(fn ($record) => "Terbitkan Work Order untuk: {$record->order_code}")
                    ->modalDescription(fn ($record) => "Klien: {$record->client->company_name} | Rute: {$record->origin_facility_name} -> {$record->dest_facility_name}")
                    ->form(fn ($record) => [
                        TextInput::make('wo_number')
                            ->label('Nomor Work Order (WO)')
                            ->default(fn () => 'WO-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -5)))
                            ->required(),
                        TextInput::make('delivery_note_number')
                            ->label('Nomor Surat Jalan Driver')
                            ->default(fn () => 'SJ/' . date('Ym') . '/' . strtoupper(substr(uniqid(), -4)))
                            ->required(),
                        Select::make('transporter_id')
                            ->label('Pilih Vendor Transportir')
                            ->relationship('transporter', 'company_name')
                            ->options(Transporter::where('is_active', true)->pluck('company_name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('vehicle_type_id')
                            ->label('Tipe Kendaraan Trip Ini')
                            ->relationship('vehicleType', 'name')
                            ->default($record->vehicle_type_id)
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('vehicle_plate_no')
                            ->label('Nomor Polisi (Nopol Truk)')
                            ->placeholder('Contoh: BP 9123 NA')
                            ->required(),
                        TextInput::make('driver_name')
                            ->label('Nama Pengemudi (Driver)')
                            ->required(),
                        TextInput::make('driver_phone')
                            ->label('No. WhatsApp Driver')
                            ->tel()
                            ->placeholder('08xxxxxxxxxx')
                            ->required(),
                        TextInput::make('buying_cost')
                            ->label('Biaya Sewa Vendor (Buying Cost)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('additional_cost')
                            ->label('Biaya Tambahan (Inap / Kuli)')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),
                    ])
                    ->action(function ($record, array $data) {
                        $wo = WorkOrder::create([
                            'wo_number' => $data['wo_number'],
                            'order_id' => $record->id,
                            'transporter_id' => $data['transporter_id'],
                            'vehicle_type_id' => $data['vehicle_type_id'],
                            'vehicle_plate_no' => $data['vehicle_plate_no'],
                            'driver_name' => $data['driver_name'],
                            'driver_phone' => $data['driver_phone'],
                            'buying_cost' => $data['buying_cost'],
                            'additional_cost' => $data['additional_cost'] ?? 0,
                            'delivery_note_number' => $data['delivery_note_number'],
                            'qr_code_hash' => Str::random(32),
                            'status' => 'ASSIGNED',
                        ]);

                        // Update status order menjadi WO_DISPATCHED
                        $record->update(['status' => 'WO_DISPATCHED']);

                        Notification::make()
                            ->title('Work Order Berhasil Diterbitkan!')
                            ->body("Surat Jalan {$wo->delivery_note_number} siap dicetak.")
                            ->success()
                            ->send();
                    }),

                // TOMBOL LINK TRACKING KHUSUS KLIEN (READ-ONLY)
                Action::make('client_tracking')
                    ->label('Link Klien')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => route('client.tracking', ['hash' => $record->tracking_hash]))
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
