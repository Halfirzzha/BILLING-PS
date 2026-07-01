<?php

namespace App\Filament\Resources\Stations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Station profile')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('code')->required()->unique(ignoreRecord: true),
                        Select::make('status')
                            ->options([
                                'idle' => 'Idle',
                                'active' => 'Active',
                                'maintenance' => 'Maintenance',
                            ])
                            ->required(),
                        Select::make('device_status')
                            ->options([
                                'offline' => 'Offline',
                                'online' => 'Online',
                                'busy' => 'Busy',
                                'error' => 'Error',
                            ])
                            ->required(),
                        TextInput::make('qr_token')->required()->unique(ignoreRecord: true),
                        TextInput::make('device_token')->required()->unique(ignoreRecord: true),
                        TextInput::make('location'),
                        TextInput::make('default_hourly_rate')->numeric()->prefix('Rp'),
                        Toggle::make('is_active')->default(true),
                    ])
                    ->columns(2),
                Section::make('Device labels')
                    ->schema([
                        TextInput::make('tv_label'),
                        TextInput::make('ps_label'),
                        TextInput::make('adb_identifier'),
                        TextInput::make('current_screen'),
                        TextInput::make('device_version'),
                        Textarea::make('notes')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
