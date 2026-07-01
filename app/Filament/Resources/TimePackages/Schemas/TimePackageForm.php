<?php

namespace App\Filament\Resources\TimePackages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TimePackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Package')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true),
                        TextInput::make('minutes')->required()->numeric()->minValue(1),
                        TextInput::make('price')->required()->numeric()->minValue(0)->prefix('Rp'),
                        Toggle::make('is_active')->default(true),
                        Textarea::make('description')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
