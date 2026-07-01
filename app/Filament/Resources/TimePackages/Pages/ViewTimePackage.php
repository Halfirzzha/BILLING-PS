<?php

namespace App\Filament\Resources\TimePackages\Pages;

use App\Filament\Resources\TimePackages\TimePackageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTimePackage extends ViewRecord
{
    protected static string $resource = TimePackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
