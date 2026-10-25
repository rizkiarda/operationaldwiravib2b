<?php

namespace App\Filament\Resources\Transporters\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TransporterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('company_name'),
                TextEntry::make('pic_name'),
                TextEntry::make('pic_phone'),
                TextEntry::make('pic_email')
                    ->placeholder('-'),
                TextEntry::make('npwp')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->columnSpanFull(),
                TextEntry::make('bank_name'),
                TextEntry::make('bank_account_no'),
                TextEntry::make('bank_account_holder'),
                TextEntry::make('default_top_days')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
