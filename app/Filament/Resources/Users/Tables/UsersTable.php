<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\PaymentMethod;
use App\Enums\TimeLedgerType;
use App\Models\TimePackage;
use App\Models\TimeLedgerEntry;
use App\Services\BillingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('member_code')->searchable()->copyable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('roles.name')->badge(),
                TextColumn::make('wallet_balance')->money('IDR')->label('Wallet'),
                TextColumn::make('remaining_minutes')->suffix(' min')->label('Time'),
                TextColumn::make('playSessions_count')->counts('playSessions')->label('Sessions'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name'),
            ])
            ->recordActions([
                Action::make('topUp')
                    ->label('Top Up')
                    ->icon('heroicon-m-wallet')
                    ->form([
                        TextInput::make('amount')->numeric()->required()->minValue(1000),
                        Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'transfer' => 'Transfer',
                            ])
                            ->default('cash')
                            ->required(),
                        Textarea::make('notes'),
                    ])
                    ->action(function ($record, array $data): void {
                        app(BillingService::class)->topUpWallet(
                            user: $record,
                            amount: (int) $data['amount'],
                            method: PaymentMethod::from($data['payment_method']),
                            operator: auth()->user(),
                            notes: $data['notes'] ?? null,
                        );

                        Notification::make()->title('Wallet topped up successfully.')->success()->send();
                    }),
                Action::make('sellPackage')
                    ->label('Sell Package')
                    ->icon('heroicon-m-clock')
                    ->form([
                        Select::make('time_package_id')
                            ->label('Time package')
                            ->options(fn (): array => TimePackage::query()->where('is_active', true)->pluck('name', 'id')->all())
                            ->required(),
                        Select::make('payment_method')
                            ->options([
                                'wallet' => 'Wallet',
                                'cash' => 'Cash',
                                'transfer' => 'Transfer',
                            ])
                            ->default('cash')
                            ->required(),
                        Textarea::make('notes'),
                    ])
                    ->action(function ($record, array $data): void {
                        $package = TimePackage::findOrFail($data['time_package_id']);

                        app(BillingService::class)->purchaseTimePackage(
                            user: $record,
                            package: $package,
                            method: PaymentMethod::from($data['payment_method']),
                            operator: auth()->user(),
                            notes: $data['notes'] ?? null,
                        );

                        Notification::make()->title('Time package assigned successfully.')->success()->send();
                    }),
                Action::make('addTime')
                    ->label('Add Time')
                    ->icon('heroicon-m-plus-circle')
                    ->form([
                        TextInput::make('minutes')->numeric()->required()->minValue(1),
                        Textarea::make('notes'),
                    ])
                    ->action(function ($record, array $data): void {
                        TimeLedgerEntry::create([
                            'user_id' => $record->id,
                            'operator_id' => auth()->id(),
                            'type' => TimeLedgerType::Adjustment->value,
                            'minutes' => (int) $data['minutes'],
                            'notes' => $data['notes'] ?? 'Manual time adjustment',
                        ]);

                        Notification::make()->title('Time added successfully.')->success()->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
