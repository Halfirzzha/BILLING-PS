<?php

namespace App\Filament\Widgets;

use App\Models\PlaySession;
use App\Models\Station;
use App\Models\User;
use App\Models\WalletTransaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Operational Snapshot';

    protected function getStats(): array
    {
        return [
            Stat::make('Active sessions', PlaySession::query()->where('status', 'active')->count())
                ->description('Station currently in use'),
            Stat::make('Active stations', Station::query()->where('is_active', true)->count())
                ->description('Ready for booking'),
            Stat::make('Members', User::role('member')->count())
                ->description('Registered player accounts'),
            Stat::make('Wallet top-ups', 'Rp ' . number_format((int) WalletTransaction::query()->where('type', 'top_up')->sum('amount'), 0, ',', '.'))
                ->description('Accumulated member deposits'),
        ];
    }
}
