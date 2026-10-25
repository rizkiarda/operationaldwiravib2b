<?php

namespace App\Filament\Resources\VehicleTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VehicleTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Tipe Armada')
                    ->placeholder('Contoh: TRONTON_WINGBOX')
                    ->required(),

                TextInput::make('name')
                    ->label('Nama Jenis Armada')
                    ->placeholder('Contoh: Tronton Wingbox 30 Ton')
                    ->required(),

                TextInput::make('max_weight_kg')
                    ->label('Kapasitas Beban Maksimal (Kg)')
                    ->suffix('Kg')
                    ->required()
                    ->numeric(),

                TextInput::make('max_volume_cbm')
                    ->label('Kapasitas Volume Kubikasi (CBM)')
                    ->suffix('CBM')
                    ->required()
                    ->numeric(),

                TextInput::make('description')
                    ->label('Deskripsi / Spesifikasi Dimensi Box')
                    ->placeholder('Contoh: P: 9.5m, L: 2.4m, T: 2.4m')
                    ->default(null),
            ]);
    }
}
