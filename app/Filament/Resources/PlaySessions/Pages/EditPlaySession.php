<?php

namespace App\Filament\Resources\PlaySessions\Pages;

use App\Filament\Resources\PlaySessions\PlaySessionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPlaySession extends EditRecord
{
    protected static string $resource = PlaySessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
