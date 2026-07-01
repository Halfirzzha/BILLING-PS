<?php

namespace App\Filament\Resources\PlaySessions\Schemas;

use App\Enums\PlaySessionStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PlaySessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('outlet_id')
                    ->relationship('outlet', 'name')
                    ->required(),
                Select::make('station_id')
                    ->relationship('station', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('status')
                    ->options(PlaySessionStatus::class)
                    ->default('active')
                    ->required(),
                TextInput::make('payment_method')
                    ->required()
                    ->default('time_balance'),
                DateTimePicker::make('started_at')
                    ->required(),
                DateTimePicker::make('planned_end_at'),
                DateTimePicker::make('ended_at'),
                TextInput::make('started_with_minutes')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('consumed_minutes')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('minutes_debited')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('ended_by')
                    ->numeric(),
                TextInput::make('notes'),
            ]);
    }
}
