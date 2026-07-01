<?php

namespace App\Filament\Resources\Stations\Schemas;

use App\Enums\StationAppMode;
use App\Enums\StationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('status')
                    ->options(StationStatus::class)
                    ->default('idle')
                    ->required(),
                Select::make('app_mode')
                    ->options(StationAppMode::class)
                    ->default('qr')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('adb_identifier'),
                TextInput::make('current_session_id')
                    ->numeric(),
                DateTimePicker::make('last_heartbeat_at'),
            ]);
    }
}
