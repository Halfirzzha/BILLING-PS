<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User summary')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('member_code'),
                        TextEntry::make('email'),
                        TextEntry::make('phone'),
                        TextEntry::make('roles.name')->badge(),
                        TextEntry::make('wallet_balance')->label('Wallet balance')->money('IDR'),
                        TextEntry::make('remaining_minutes')->suffix(' minutes'),
                        IconEntry::make('is_active')->boolean(),
                        TextEntry::make('last_seen_at')->dateTime('d M Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }
}
