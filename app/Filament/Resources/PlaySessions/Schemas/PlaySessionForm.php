<?php

namespace App\Filament\Resources\PlaySessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlaySessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Session')
                    ->schema([
                        Select::make('user_id')->relationship('user', 'name')->required(),
                        Select::make('station_id')->relationship('station', 'name')->required(),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        TextInput::make('started_with_minutes')->numeric()->required(),
                        TextInput::make('consumed_minutes')->numeric(),
                        TextInput::make('overage_minutes')->numeric(),
                        DateTimePicker::make('started_at')->required(),
                        DateTimePicker::make('ended_at'),
                        Textarea::make('notes')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
