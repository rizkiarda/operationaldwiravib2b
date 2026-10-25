<?php

namespace App\Filament\Resources\Transporters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TransporterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->label('Nama PT / CV Transporter (Vendor Truk)')
                    ->placeholder('Contoh: CV. Berkah Logistik Express')
                    ->required(),

                TextInput::make('pic_name')
                    ->label('Nama PIC Vendor')
                    ->placeholder('Nama penanggung jawab armada')
                    ->required(),

                TextInput::make('pic_phone')
                    ->label('No. WhatsApp / HP PIC Vendor')
                    ->tel()
                    ->placeholder('08xxxxxxxxxx')
                    ->required(),

                TextInput::make('pic_email')
                    ->label('Email Vendor')
                    ->email()
                    ->default(null),

                TextInput::make('npwp')
                    ->label('NPWP Vendor')
                    ->default(null),

                Textarea::make('address')
                    ->label('Alamat Pool / Kantor Vendor')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('bank_name')
                    ->label('Nama Bank (Tujuan Pembayaran Ongkos)')
                    ->placeholder('BCA / Mandiri / BNI / BRI')
                    ->required(),

                TextInput::make('bank_account_no')
                    ->label('Nomor Rekening Bank')
                    ->placeholder('Nomor rekening penampung')
                    ->required(),

                TextInput::make('bank_account_holder')
                    ->label('Nama Pemilik Rekening (Sesuai Buku Tabungan)')
                    ->required(),

                TextInput::make('default_top_days')
                    ->label('Jatuh Tempo Pembayaran Vendor (TOP)')
                    ->helperText('Term of Payment ke vendor ekspedisi')
                    ->suffix('Hari')
                    ->required()
                    ->numeric()
                    ->default(14),

                Toggle::make('is_active')
                    ->label('Status Mitra Transporter Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
