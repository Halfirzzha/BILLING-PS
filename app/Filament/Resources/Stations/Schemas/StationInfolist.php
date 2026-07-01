<?php

namespace App\Filament\Resources\Stations\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Station details')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('code'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('device_status')->badge(),
                        TextEntry::make('app_mode')->badge(),
                        IconEntry::make('is_active')->boolean(),
                        TextEntry::make('tv_label'),
                        TextEntry::make('ps_label'),
                        TextEntry::make('adb_identifier'),
                        TextEntry::make('current_screen'),
                        TextEntry::make('device_version'),
                        TextEntry::make('last_heartbeat_at')->dateTime('d M Y H:i:s'),
                        TextEntry::make('location'),
                        TextEntry::make('qr_token'),
                        TextEntry::make('device_token'),
                        TextEntry::make('notes')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
