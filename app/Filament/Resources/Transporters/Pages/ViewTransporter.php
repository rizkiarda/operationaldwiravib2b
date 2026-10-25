<?php

namespace App\Filament\Resources\Transporters\Pages;

use App\Filament\Resources\Transporters\TransporterResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTransporter extends ViewRecord
{
    protected static string $resource = TransporterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
