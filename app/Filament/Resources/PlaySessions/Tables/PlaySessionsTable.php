<?php

namespace App\Filament\Resources\PlaySessions\Tables;

use App\Services\BillingService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PlaySessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('station.name')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('started_at')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('consumed_minutes')->suffix(' min'),
                TextColumn::make('overage_minutes')->suffix(' min'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->recordActions([
                Action::make('endSession')
                    ->label('End')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->status === 'active')
                    ->action(function ($record): void {
                        app(BillingService::class)->endSession($record, auth()->user());
                        Notification::make()->title('Session ended successfully.')->success()->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
