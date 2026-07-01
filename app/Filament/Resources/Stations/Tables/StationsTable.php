<?php

namespace App\Filament\Resources\Stations\Tables;

use App\Enums\RoleName;
use App\Enums\StationStatus;
use App\Models\PlaySession;
use App\Models\Station;
use App\Models\User;
use App\Services\SessionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;

class StationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('outlet.name')->label('Outlet')->searchable(),
                TextColumn::make('code')->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('status')->badge()->searchable(),
                TextColumn::make('app_mode')->label('Mode TV')->badge(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('last_heartbeat_at')->label('Heartbeat')->since()->sortable(),
            ])
            ->recordActions([
                Action::make('startSession')
                    ->label('Mulai Sesi')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Station $record): bool => $record->is_active && $record->status !== StationStatus::Active)
                    ->schema([
                        Select::make('user_id')
                            ->label('Member')
                            ->searchable()
                            ->options(fn () => User::query()
                                ->whereHas('roles', fn ($q) => $q->where('name', RoleName::Member->value))
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (array $data, Station $record): void {
                        $member = User::findOrFail($data['user_id']);
                        try {
                            app(SessionService::class)->startSession($member, $record, auth()->user());
                            Notification::make()->title('Sesi dimulai')->success()->send();
                        } catch (Throwable $e) {
                            Notification::make()->title('Gagal memulai sesi')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Action::make('endSession')
                    ->label('Akhiri Sesi')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Station $record): bool => $record->status === StationStatus::Active && $record->current_session_id !== null)
                    ->action(function (Station $record): void {
                        $session = PlaySession::find($record->current_session_id);
                        if (! $session) {
                            Notification::make()->title('Tidak ada sesi aktif')->warning()->send();

                            return;
                        }
                        try {
                            app(SessionService::class)->endSession($session, auth()->user());
                            Notification::make()->title('Sesi diakhiri')->success()->send();
                        } catch (Throwable $e) {
                            Notification::make()->title('Gagal mengakhiri sesi')->body($e->getMessage())->danger()->send();
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
