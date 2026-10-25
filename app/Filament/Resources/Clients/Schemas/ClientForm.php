<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label('Nama Perusahaan Klien (Shipper)')
                    ->placeholder('Contoh: PT. Sumber Pangan Makmur')
                    ->required(),

                TextInput::make('business_license_no')
                    ->label('Nomor Induk Berusaha (NIB) / MoU')
                    ->placeholder('Nomor izin legalitas usaha atau kontrak')
                    ->default(null),

                TextInput::make('npwp')
                    ->label('NPWP Perusahaan')
                    ->placeholder('Contoh: 01.234.567.8-012.000')
                    ->default(null),

                Textarea::make('address')
                    ->label('Alamat Pabrik / Gudang Utama')
                    ->placeholder('Alamat lengkap operasional klien')
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('billing_address')
                    ->label('Alamat Penagihan / Faktur Pajak')
                    ->placeholder('Kosongkan jika sama dengan alamat utama')
                    ->default(null)
                    ->columnSpanFull(),

                TextInput::make('pic_name')
                    ->label('Nama PIC Operasional')
                    ->placeholder('Nama penanggung jawab logistik')
                    ->required(),

                TextInput::make('pic_phone')
                    ->label('No. WhatsApp / HP PIC')
                    ->tel()
                    ->placeholder('08xxxxxxxxxx')
                    ->required(),

                TextInput::make('pic_email')
                    ->label('Email PIC / Finance Klien')
                    ->email()
                    ->placeholder('finance@klien.com')
                    ->default(null),

                TextInput::make('default_top_days')
                    ->label('Jatuh Tempo Pembayaran (Term of Payment)')
                    ->helperText('Jumlah hari sebelum invoice jatuh tempo')
                    ->suffix('Hari')
                    ->required()
                    ->numeric()
                    ->default(30),

                Toggle::make('is_active')
                    ->label('Status Akun Klien Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
