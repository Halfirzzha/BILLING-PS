<?php

namespace App\Filament\Resources\Stations\Tables;

use App\Enums\StationCommandType;
use App\Services\BillingService;
use App\Services\StationDeviceService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('code')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('device_status')->badge()->label('Device'),
                TextColumn::make('app_mode')->badge()->toggleable(),
                TextColumn::make('location')->toggleable(),
                TextColumn::make('tv_label')->label('TV'),
                TextColumn::make('ps_label')->label('PS'),
                TextColumn::make('last_heartbeat_at')->since()->label('Heartbeat'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'idle' => 'Idle',
                        'active' => 'Active',
                        'maintenance' => 'Maintenance',
                    ]),
            ])
            ->recordActions([
                Action::make('display')
                    ->label('Open QR')
                    ->icon('heroicon-m-qr-code')
                    ->url(fn ($record): string => route('stations.display', $record))
                    ->openUrlInNewTab(),
                Action::make('syncScreen')
                    ->label('Sync Screen')
                    ->icon('heroicon-m-arrow-path')
                    ->action(function ($record): void {
                        app(StationDeviceService::class)->syncStationPresentation($record, auth()->user());

                        Notification::make()
                            ->title('Station screen sync queued.')
                            ->success()
                            ->send();
                    }),
                Action::make('wakeDevice')
                    ->label('Wake')
                    ->icon('heroicon-m-bolt')
                    ->visible(fn ($record): bool => filled($record->adb_identifier))
                    ->action(function ($record): void {
                        app(StationDeviceService::class)->queueCommand($record, StationCommandType::WakeDevice, requestedBy: auth()->user());

                        Notification::make()->title('Wake command queued.')->success()->send();
                    }),
                Action::make('restartBrowser')
                    ->label('Restart Browser')
                    ->icon('heroicon-m-arrow-path-rounded-square')
                    ->visible(fn ($record): bool => filled($record->adb_identifier))
                    ->action(function ($record): void {
                        app(StationDeviceService::class)->queueCommand($record, StationCommandType::RestartBrowser, requestedBy: auth()->user());

                        Notification::make()->title('Browser restart queued.')->success()->send();
                    }),
                Action::make('stopSession')
                    ->label('Force Stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->playSessions()->where('status', 'active')->exists())
                    ->action(function ($record): void {
                        $session = $record->playSessions()->where('status', 'active')->latest('started_at')->first();

                        if (! $session) {
                            return;
                        }

                        app(BillingService::class)->endSession($session, auth()->user());

                        Notification::make()
                            ->title('Session stopped successfully.')
                            ->success()
                            ->send();
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
