<?php

namespace App\Filament\Resources\WalletTransactions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WalletTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('payment_method'),
                TextColumn::make('amount')->money('IDR')->sortable(),
                IconColumn::make('affects_balance')->boolean(),
                TextColumn::make('reference')->searchable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'top_up' => 'Top up',
                        'time_purchase' => 'Time purchase',
                        'cash_sale' => 'Cash sale',
                        'adjustment' => 'Adjustment',
                        'refund' => 'Refund',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
