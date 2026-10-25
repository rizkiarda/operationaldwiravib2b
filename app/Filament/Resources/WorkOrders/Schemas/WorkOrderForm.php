<?php

namespace App\Filament\Resources\WorkOrders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\Client;

class WorkOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('wo_number')
                    ->label('Nomor Work Order (WO)')
                    ->default(fn () => 'WO-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -5)))
                    ->required(),

                TextInput::make('delivery_note_number')
                    ->label('Nomor Surat Jalan Driver')
                    ->default(fn () => 'SJ/' . date('Ym') . '/' . strtoupper(substr(uniqid(), -4)))
                    ->required(),

                // 1. MEMILIH ORDER DAHULU ATAU INPUT ORDER BARU LANGSUNG (MODAL POPUP)
                Select::make('order_id')
                    ->label('1. Order / PO Muatan Klien')
                    ->relationship('order', 'order_code')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->order_code} | {$record->client->company_name} (PO: {$record->po_number_client}) [{$record->origin_facility_name} -> {$record->dest_facility_name}]")
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $order = Order::with('vehicleType')->find($state);
                            if ($order && $order->vehicle_type_id) {
                                $set('vehicle_type_id', $order->vehicle_type_id);
                            }
                        }
                    })
                    ->required()
                    ->columnSpanFull()
                    ->createOptionModalHeading('Input Order / PO Baru Langsung di Tempat')
                    ->createOptionForm([
                        Select::make('client_id')
                            ->label('Client B2B (Pemegang MoU)')
                            ->relationship('client', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionModalHeading('Tambah Client Baru Langsung')
                            ->createOptionForm([
                                TextInput::make('company_name')->label('Nama Perusahaan Client')->required(),
                                TextInput::make('pic_name')->label('Nama PIC')->required(),
                                TextInput::make('pic_phone')->label('No. WA PIC')->tel()->required(),
                                Textarea::make('address')->label('Alamat Pabrik/Kantor')->required(),
                                TextInput::make('default_top_days')->label('TOP (Hari)')->numeric()->default(30)->required(),
                            ]),

                        TextInput::make('order_code')
                            ->label('Kode Order')
                            ->default(fn () => 'ORD-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -5)))
                            ->required(),

                        TextInput::make('po_number_client')
                            ->label('No. PO Klien')
                            ->placeholder('Contoh: PO-ABC-2026-001')
                            ->required(),

                        Select::make('cargo_category')
                            ->label('Kategori Barang')
                            ->options([
                                'GENERAL_CARGO' => 'General Cargo',
                                'FMCG_DRY' => 'FMCG / Dry Food',
                                'FRAGILE_ELECTRONICS' => 'Barang Pecah Belah / Elektronik',
                                'COLD_CHAIN' => 'Cold Chain (Suhu Terkontrol)',
                                'HEAVY_INDUSTRIAL' => 'Heavy Industrial',
                                'HAZMAT_DANGEROUS' => 'Dangerous Goods',
                            ])
                            ->default('GENERAL_CARGO')
                            ->required(),

                        TextInput::make('origin_facility_name')->label('Gudang Asal (Origin)')->required(),
                        Textarea::make('origin_address')->label('Alamat Muat Lengkap')->required(),
                        TextInput::make('origin_pic_name')->label('PIC Muat')->required(),
                        TextInput::make('origin_pic_phone')->label('HP PIC Muat')->tel()->required(),
                        DateTimePicker::make('loading_plan_date')->label('Waktu Rencana Muat')->required(),

                        TextInput::make('dest_facility_name')->label('Gudang Tujuan (Bongkar)')->required(),
                        Textarea::make('dest_address')->label('Alamat Bongkar Lengkap')->required(),
                        TextInput::make('dest_pic_name')->label('PIC Bongkar')->required(),
                        TextInput::make('dest_pic_phone')->label('HP PIC Bongkar')->tel()->required(),
                        DateTimePicker::make('unloading_plan_date')->label('Waktu Rencana Tiba')->required(),

                        TextInput::make('selling_price')->label('Harga Jual ke Klien (Rp)')->numeric()->required(),
                    ]),

                // 2. MEMILIH TRANSPORTER (BISA INPUT VENDOR BARU LANGSUNG DI MODAL POPUP)
                Select::make('transporter_id')
                    ->label('2. Vendor Transportir (Transporter)')
                    ->relationship('transporter', 'company_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Jika vendor belum ada, klik ikon "+" di samping untuk tambah baru')
                    ->createOptionModalHeading('Daftarkan Vendor Transportir Baru')
                    ->createOptionForm([
                        TextInput::make('company_name')
                            ->label('Nama PT / CV Transporter')
                            ->placeholder('Contoh: CV. Berkah Angkutan Jaya')
                            ->required(),
                        TextInput::make('pic_name')
                            ->label('Nama PIC Vendor')
                            ->required(),
                        TextInput::make('pic_phone')
                            ->label('No. WhatsApp PIC')
                            ->tel()
                            ->required(),
                        Textarea::make('address')
                            ->label('Alamat Pool / Kantor Transporter')
                            ->required(),
                        TextInput::make('bank_name')
                            ->label('Nama Bank (Untuk Pembayaran)')
                            ->placeholder('BCA / Mandiri / BNI / BRI')
                            ->required(),
                        TextInput::make('bank_account_no')
                            ->label('Nomor Rekening')
                            ->required(),
                        TextInput::make('bank_account_holder')
                            ->label('Atas Nama Rekening')
                            ->required(),
                        TextInput::make('default_top_days')
                            ->label('Term of Payment (Hari)')
                            ->numeric()
                            ->default(14)
                            ->required(),
                    ]),

                // 3. TIPE KENDARAAN (BISA INPUT JENIS ARMADA BARU LANGSUNG DI TEMPAT)
                Select::make('vehicle_type_id')
                    ->label('3. Tipe Kendaraan / Jenis Armada')
                    ->relationship('vehicleType', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionModalHeading('Tambah Tipe Armada Baru')
                    ->createOptionForm([
                        TextInput::make('code')
                            ->label('Kode Armada (misal: TRONTON_BOX)')
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Jenis Armada (misal: Tronton Box 30 Ton)')
                            ->required(),
                        TextInput::make('max_weight_kg')
                            ->label('Kapasitas Tonase Maksimal (Kg)')
                            ->numeric()
                            ->required(),
                        TextInput::make('max_volume_cbm')
                            ->label('Kapasitas Kubikasi Maksimal (CBM)')
                            ->numeric()
                            ->required(),
                    ]),

                // 4. DATA ARMADA & DRIVER KHUSUS WO INI
                TextInput::make('vehicle_plate_no')
                    ->label('Nomor Polisi (Nopol Truk)')
                    ->placeholder('Contoh: B 9123 UXT')
                    ->required(),

                TextInput::make('driver_name')
                    ->label('Nama Pengemudi (Driver)')
                    ->placeholder('Nama lengkap pengemudi')
                    ->required(),

                TextInput::make('driver_phone')
                    ->label('No. WhatsApp Driver')
                    ->tel()
                    ->placeholder('08xxxxxxxxxx')
                    ->required(),

                TextInput::make('driver_id_card_no')
                    ->label('No. KTP Driver (NIK)')
                    ->placeholder('16 digit NIK driver')
                    ->default(null),

                // 5. BIAYA SEWA DAN OPERASIONAL WO INI
                TextInput::make('buying_cost')
                    ->label('Biaya Sewa Truk Vendor (Buying Cost)')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->helperText('Tarif sewa armada yang dibayarkan ke vendor untuk trip ini'),

                TextInput::make('additional_cost')
                    ->label('Biaya Tambahan (Uang Inap / Kuli Bongkar)')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('Rp'),

                Select::make('status')
                    ->label('Status Pengiriman')
                    ->options([
                        'ASSIGNED' => 'Armada Ditugaskan (Assigned)',
                        'ACCEPTED_BY_VENDOR' => 'Disetujui Vendor',
                        'AT_ORIGIN' => 'Tiba di Lokasi Muat (At Origin)',
                        'LOADED' => 'Muat Selesai (Loaded)',
                        'ON_THE_WAY' => 'Dalam Perjalanan (On The Way)',
                        'AT_DESTINATION' => 'Tiba di Lokasi Bongkar',
                        'UNLOADED_WAITING_POD' => 'Bongkar Selesai (Menunggu e-POD)',
                        'POD_VERIFIED' => 'POD Terverifikasi Lengkap',
                        'REJECTED' => 'Ditolak / Dibatalkan',
                    ])
                    ->default('ASSIGNED')
                    ->required(),

                TextInput::make('qr_code_hash')
                    ->label('Hash Validasi QR')
                    ->default(fn () => Str::random(32))
                    ->readOnly()
                    ->required(),
            ]);
    }
}
