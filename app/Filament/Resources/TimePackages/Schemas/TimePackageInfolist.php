<?php

namespace App\Filament\Resources\TimePackages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TimePackageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Package details')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('slug'),
                        TextEntry::make('minutes')->suffix(' minutes'),
                        TextEntry::make('price')->money('IDR'),
                        IconEntry::make('is_active')->boolean(),
                        TextEntry::make('description')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
