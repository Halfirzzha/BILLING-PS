<?php

namespace App\Filament\Resources\WalletTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WalletTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('outlet_id')
                    ->relationship('outlet', 'name'),
                Select::make('operator_id')
                    ->relationship('operator', 'name'),
                TextInput::make('type')
                    ->required(),
                TextInput::make('payment_method')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Toggle::make('affects_balance')
                    ->required(),
                TextInput::make('reference')
                    ->required(),
                TextInput::make('gateway_ref'),
                TextInput::make('notes'),
                TextInput::make('meta'),
            ]);
    }
}
