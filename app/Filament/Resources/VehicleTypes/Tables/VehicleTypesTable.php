<?php

namespace App\Filament\Resources\VehicleTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Jenis Armada')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('max_weight_kg')
                    ->label('Maks Beban')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', '.') . ' Kg')
                    ->sortable(),
                TextColumn::make('max_volume_cbm')
                    ->label('Maks Volume')
                    ->formatStateUsing(fn ($state) => number_format($state, 1, ',', '.') . ' CBM')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Dimensi / Spesifikasi')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->label('Ubah'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Hapus Pilihan'),
                ]),
            ]);
    }
}
