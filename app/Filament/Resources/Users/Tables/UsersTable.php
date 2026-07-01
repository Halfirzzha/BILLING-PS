<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\PaymentMethod;
use App\Models\TimePackage;
use App\Models\User;
use App\Services\BillingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('member_code')->label('Kode Member')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->label('HP')->searchable(),
                TextColumn::make('wallet_balance')->label('Saldo')->money('IDR')->alignEnd(),
                TextColumn::make('remaining_minutes')->label('Sisa Waktu')->suffix(' mnt')->alignEnd(),
                TextColumn::make('outlet.name')->label('Outlet')->searchable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->recordActions([
                Action::make('topUp')
                    ->label('Top Up')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (User $record): bool => ! $record->isAdmin())
                    ->schema([
                        TextInput::make('amount')->label('Jumlah (Rp)')->numeric()->minValue(1000)->required(),
                    ])
                    ->action(function (array $data, User $record): void {
                        app(BillingService::class)->topUpWallet(
                            $record,
                            (int) $data['amount'],
                            PaymentMethod::Cash,
                            auth()->user(),
                        );
                        Notification::make()->title('Top up berhasil')->success()->send();
                    }),
                Action::make('sellPackage')
                    ->label('Jual Paket')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->visible(fn (User $record): bool => ! $record->isAdmin())
                    ->schema([
                        Select::make('time_package_id')
                            ->label('Paket')
                            ->options(fn () => TimePackage::query()
                                ->where('is_active', true)
                                ->when(
                                    auth()->user() && ! auth()->user()->isAdmin() && auth()->user()->outlet_id,
                                    fn ($q) => $q->where('outlet_id', auth()->user()->outlet_id),
                                )
                                ->pluck('name', 'id'))
                            ->required(),
                        Select::make('method')
                            ->label('Metode Bayar')
                            ->options(['cash' => 'Cash', 'wallet' => 'Wallet'])
                            ->default('cash')
                            ->required(),
                    ])
                    ->action(function (array $data, User $record): void {
                        $package = TimePackage::findOrFail($data['time_package_id']);
                        try {
                            app(BillingService::class)->purchaseTimePackage(
                                $record,
                                $package,
                                PaymentMethod::from($data['method']),
                                auth()->user(),
                            );
                            Notification::make()->title('Paket terjual')->success()->send();
                        } catch (Throwable $e) {
                            Notification::make()->title('Gagal menjual paket')->body($e->getMessage())->danger()->send();
                        }
                    }),
                EditAction::make()->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
                ]),
            ]);
    }
}
