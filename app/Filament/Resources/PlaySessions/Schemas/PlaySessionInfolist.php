<?php

namespace App\Filament\Resources\PlaySessions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlaySessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Session details')
                    ->schema([
                        TextEntry::make('user.name'),
                        TextEntry::make('station.name'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('started_with_minutes'),
                        TextEntry::make('consumed_minutes'),
                        TextEntry::make('overage_minutes'),
                        TextEntry::make('started_at')->dateTime('d M Y H:i'),
                        TextEntry::make('ended_at')->dateTime('d M Y H:i'),
                        TextEntry::make('notes')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
