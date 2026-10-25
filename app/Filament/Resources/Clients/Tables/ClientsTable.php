<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')
                    ->label('Nama Perusahaan Klien')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('pic_name')
                    ->label('PIC Operasional')
                    ->searchable(),
                TextColumn::make('pic_phone')
                    ->label('No. WhatsApp / HP')
                    ->searchable(),
                TextColumn::make('default_top_days')
                    ->label('TOP')
                    ->suffix(' Hari')
                    ->sortable(),
                TextColumn::make('npwp')
                    ->label('NPWP')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Tanggal Terdaftar')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
