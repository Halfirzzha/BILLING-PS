<?php

namespace App\Filament\Resources\Stations\Pages;

use App\Filament\Resources\Stations\StationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStation extends ViewRecord
{
    protected static string $resource = StationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
