<?php

namespace App\Filament\Resources\Transporters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransportersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label('Nama Transporter')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('pic_name')
                    ->label('PIC Vendor')
                    ->searchable(),
                TextColumn::make('pic_phone')
                    ->label('WhatsApp / HP')
                    ->searchable(),
                TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable(),
                TextColumn::make('bank_account_no')
                    ->label('No. Rekening')
                    ->searchable(),
                TextColumn::make('bank_account_holder')
                    ->label('Atas Nama')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('default_top_days')
                    ->label('TOP')
                    ->suffix(' Hari')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
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
