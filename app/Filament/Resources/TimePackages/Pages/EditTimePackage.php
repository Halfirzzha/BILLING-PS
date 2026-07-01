<?php

namespace App\Filament\Resources\TimePackages\Pages;

use App\Filament\Resources\TimePackages\TimePackageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTimePackage extends EditRecord
{
    protected static string $resource = TimePackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
