<?php

namespace App\Filament\Resources\WalletTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WalletTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Transaction')
                    ->schema([
                        Select::make('user_id')->relationship('user', 'name')->required(),
                        Select::make('type')
                            ->options([
                                'top_up' => 'Top up',
                                'time_purchase' => 'Time purchase',
                                'cash_sale' => 'Cash sale',
                                'adjustment' => 'Adjustment',
                                'refund' => 'Refund',
                            ])
                            ->required(),
                        Select::make('payment_method')
                            ->options([
                                'wallet' => 'Wallet',
                                'cash' => 'Cash',
                                'transfer' => 'Transfer',
                            ])
                            ->required(),
                        TextInput::make('amount')->numeric()->required(),
                        TextInput::make('reference'),
                        Textarea::make('notes')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
