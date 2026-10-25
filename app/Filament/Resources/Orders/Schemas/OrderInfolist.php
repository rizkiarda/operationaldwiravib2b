<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order_code'),
                TextEntry::make('client.id')
                    ->label('Client'),
                TextEntry::make('po_number_client'),
                TextEntry::make('vehicleType.name')
                    ->label('Vehicle type'),
                TextEntry::make('origin_facility_name'),
                TextEntry::make('origin_address')
                    ->columnSpanFull(),
                TextEntry::make('origin_pic_name'),
                TextEntry::make('origin_pic_phone'),
                TextEntry::make('loading_plan_date')
                    ->dateTime(),
                TextEntry::make('dest_facility_name'),
                TextEntry::make('dest_address')
                    ->columnSpanFull(),
                TextEntry::make('dest_pic_name'),
                TextEntry::make('dest_pic_phone'),
                TextEntry::make('unloading_plan_date')
                    ->dateTime(),
                TextEntry::make('selling_price')
                    ->money(),
                TextEntry::make('tax_percent')
                    ->numeric(),
                TextEntry::make('special_instructions')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('insurance_type')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
