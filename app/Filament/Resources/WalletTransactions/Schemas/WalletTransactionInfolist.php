<?php

namespace App\Filament\Resources\WalletTransactions\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WalletTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Wallet transaction')
                    ->schema([
                        TextEntry::make('user.name'),
                        TextEntry::make('type')->badge(),
                        TextEntry::make('payment_method'),
                        TextEntry::make('amount')->money('IDR'),
                        IconEntry::make('affects_balance')->boolean(),
                        TextEntry::make('reference'),
                        TextEntry::make('notes')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
