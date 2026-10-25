<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Order & Legalitas Kontrak')
                    ->description('Nomor pesanan dan relasi perjanjian kerja sama (MoU)')
                    ->schema([
                        TextInput::make('order_code')
                            ->label('Kode Order')
                            ->default(fn () => 'ORD-' . date('Ym') . '-' . strtoupper(substr(uniqid(), -5)))
                            ->required(),

                        TextInput::make('dispatch_note_number')
                            ->label('Nomor Surat Pengantar Pengiriman (SPP)')
                            ->default(fn () => 'SPP/' . date('Ym') . '/' . strtoupper(substr(uniqid(), -4)))
                            ->helperText('Diterbitkan oleh PT Dwi Ravi Ladang Energi')
                            ->required(),

                        Select::make('client_id')
                            ->label('Pihak Utama Pemegang MoU (Client Utama)')
                            ->relationship('client', 'company_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('company_name')->label('Nama Perusahaan Klien')->required(),
                                TextInput::make('pic_name')->label('Nama PIC')->required(),
                                TextInput::make('pic_phone')->label('No. WA PIC')->tel()->required(),
                                Textarea::make('address')->label('Alamat Kantor/Pabrik')->required(),
                                TextInput::make('default_top_days')->label('TOP (Hari)')->numeric()->default(30)->required(),
                            ]),

                        Select::make('client_contract_role')
                            ->label('Struktur Perjanjian Kontrak')
                            ->options([
                                'DIRECT_MOU' => 'Pihak Pertama (Direct MoU Utama)',
                                'SECOND_PARTY_SUB' => 'Pihak Kedua (Vendor Client / Sub-Contractor)',
                                'BROKERAGE_PARTNER' => 'Konsorsium Mitra / Multi-Vendor',
                            ])
                            ->default('DIRECT_MOU')
                            ->reactive()
                            ->required(),

                        TextInput::make('vendor_client_name')
                            ->label('Nama Vendor Client Pihak Kedua')
                            ->placeholder('Contoh: PT. Mitra Sejahtera Logistik')
                            ->visible(fn ($get) => $get('client_contract_role') === 'SECOND_PARTY_SUB'),

                        TextInput::make('po_number_client')
                            ->label('No. PO / Kontrak Klien')
                            ->placeholder('Contoh: PO-ABC-2026-001')
                            ->required(),

                        Select::make('cargo_category')
                            ->label('Kategori & Jenis Order Barang')
                            ->options([
                                'GENERAL_CARGO' => 'General Cargo (Barang Pabrik / Komoditas Umum)',
                                'FMCG_DRY' => 'FMCG / Dry Food (Konsumsi & Ritel Kering)',
                                'FRAGILE_ELECTRONICS' => 'Barang Pecah Belah / Elektronik Presisi (Fragile)',
                                'COLD_CHAIN' => 'Cold Chain / Frozen Food (Suhu Terkontrol)',
                                'HEAVY_INDUSTRIAL' => 'Heavy Industrial (Besi, Baja, Mesin Berat)',
                                'HAZMAT_DANGEROUS' => 'Bahan Berbahaya / Gas / Kimia (Dangerous Goods/Hazmat)',
                            ])
                            ->default('GENERAL_CARGO')
                            ->required(),

                        Select::make('vehicle_type_id')
                            ->label('Tipe Unit Armada Standar')
                            ->relationship('vehicleType', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('code')->label('Kode Armada')->required(),
                                TextInput::make('name')->label('Nama Jenis Armada')->required(),
                                TextInput::make('max_weight_kg')->label('Maks Beban (Kg)')->numeric()->required(),
                                TextInput::make('max_volume_cbm')->label('Maks Volume (CBM)')->numeric()->required(),
                            ]),
                    ])->columns(2),

                Section::make('Rute Pemuatan & Pembongkaran')
                    ->description('Waktu muat dan estimasi tiba wajib valid')
                    ->schema([
                        TextInput::make('origin_facility_name')->label('Gudang Asal (Origin)')->required(),
                        Textarea::make('origin_address')->label('Alamat Muat Lengkap')->required()->columnSpanFull(),
                        TextInput::make('origin_pic_name')->label('Nama PIC Muat')->required(),
                        TextInput::make('origin_pic_phone')->label('No. HP PIC Muat')->tel()->required(),
                        DateTimePicker::make('loading_plan_date')
                            ->label('Rencana Waktu Muat')
                            ->live()
                            ->required(),

                        TextInput::make('dest_facility_name')->label('Gudang Tujuan (Destination)')->required(),
                        Textarea::make('dest_address')->label('Alamat Bongkar Lengkap')->required()->columnSpanFull(),
                        TextInput::make('dest_pic_name')->label('Nama PIC Bongkar')->required(),
                        TextInput::make('dest_pic_phone')->label('No. HP PIC Bongkar')->tel()->required(),
                        DateTimePicker::make('unloading_plan_date')
                            ->label('Estimasi Waktu Tiba (ETA)')
                            ->afterOrEqual('loading_plan_date')
                            ->validationMessages([
                                'after_or_equal' => 'Estimasi Waktu Tiba (ETA) tidak boleh lebih awal dari Rencana Waktu Muat!',
                            ])
                            ->required(),
                    ])->columns(2),

                Section::make('Daftar Rincian Item Muatan Barang')
                    ->description('Input banyak jenis barang sekaligus dalam 1 order (misal: Tabung Gas Isi & Tabung Kosong)')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                TextInput::make('item_name')
                                    ->label('Nama / Deskripsi Barang')
                                    ->placeholder('Contoh: Tabung Gas LPG 12kg (Isi) / Karton Minyak')
                                    ->required()
                                    ->columnSpan(4),

                                TextInput::make('quantity')
                                    ->label('Jumlah (Qty)')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->columnSpan(2),

                                Select::make('unit')
                                    ->label('Satuan')
                                    ->options([
                                        'Tabung' => 'Tabung',
                                        'Koli' => 'Koli',
                                        'Palet' => 'Palet',
                                        'Karton' => 'Karton',
                                        'Drum' => 'Drum',
                                        'Batang' => 'Batang',
                                        'Zak' => 'Zak',
                                        'Ton' => 'Ton',
                                        'Kg' => 'Kg',
                                    ])
                                    ->default('Tabung')
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('weight_kg')
                                    ->label('Berat Total (Kg)')
                                    ->numeric()
                                    ->default(0)
                                    ->columnSpan(2),

                                TextInput::make('remarks')
                                    ->label('No. Segel / Catatan')
                                    ->placeholder('Contoh: Segel QC OK / Tabung Kosong')
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah Item Barang Lagi')
                            ->reorderable()
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),

                Section::make('Ketentuan Finansial & Instruksi Khusus')
                    ->schema([
                        TextInput::make('selling_price')
                            ->label('Tarif Tagihan Klien (Selling Rate)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        TextInput::make('tax_percent')
                            ->label('Pajak PPN (%)')
                            ->required()
                            ->numeric()
                            ->default(1.10),

                        Select::make('insurance_type')
                            ->label('Jenis Asuransi Kargo')
                            ->options([
                                'OWNER_RISK' => 'Owner Risk (Ditanggung Klien)',
                                'ALL_RISK' => 'All Risk (Ditanggung Ekspedisi)',
                            ])
                            ->default('OWNER_RISK')
                            ->required(),

                        Select::make('status')
                            ->label('Status Pesanan')
                            ->options([
                                'DRAFT' => 'Draft',
                                'CONFIRMED' => 'Terkonfirmasi (Confirmed)',
                                'WO_DISPATCHED' => 'WO Diterbitkan (Dispatched)',
                                'IN_TRANSIT' => 'Dalam Perjalanan (In-Transit)',
                                'DELIVERED' => 'Terkirim (Delivered)',
                                'COMPLETED' => 'Selesai (Completed)',
                                'CANCELLED' => 'Dibatalkan',
                            ])
                            ->default('DRAFT')
                            ->required(),

                        Textarea::make('special_instructions')
                            ->label('Instruksi Khusus (Safety/Packing/Suhu)')
                            ->placeholder('Contoh: Suhu chiller wajib -18°C, terpal lapis 2, jangan ditumpuk lebih 3 koli')
                            ->default(null)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
