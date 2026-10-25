<?php

namespace App\Filament\Resources\Transporters;

use App\Filament\Resources\Transporters\Pages\CreateTransporter;
use App\Filament\Resources\Transporters\Pages\EditTransporter;
use App\Filament\Resources\Transporters\Pages\ListTransporters;
use App\Filament\Resources\Transporters\Pages\ViewTransporter;
use App\Filament\Resources\Transporters\Schemas\TransporterForm;
use App\Filament\Resources\Transporters\Schemas\TransporterInfolist;
use App\Filament\Resources\Transporters\Tables\TransportersTable;
use App\Models\Transporter;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TransporterResource extends Resource
{
    protected static ?string $model = Transporter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Master Mitra & Armada';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Transporters (Vendor Truk)';

    protected static ?string $recordTitleAttribute = 'company_name';

    public static function form(Schema $schema): Schema
    {
        return TransporterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TransporterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransportersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransporters::route('/'),
            'create' => CreateTransporter::route('/create'),
            'view' => ViewTransporter::route('/{record}'),
            'edit' => EditTransporter::route('/{record}/edit'),
        ];
    }
}
