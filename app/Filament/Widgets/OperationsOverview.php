<?php

namespace App\Filament\Widgets;

use App\Enums\PlaySessionStatus;
use App\Enums\RoleName;
use App\Enums\StationStatus;
use App\Models\PlaySession;
use App\Models\Station;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $outletId = ($user && ! $user->isAdmin() && $user->hasRole(RoleName::Operator->value))
            ? $user->outlet_id
            : null;

        $stations = fn () => Station::query()->when($outletId, fn ($q) => $q->where('outlet_id', $outletId));
        $sessions = PlaySession::query()->when($outletId, fn ($q) => $q->where('outlet_id', $outletId));

        $activeSessions = $sessions->where('status', PlaySessionStatus::Active->value)->count();
        $idle = $stations()->where('status', StationStatus::Idle->value)->count();
        $maintenance = $stations()->where('status', StationStatus::Maintenance->value)->count();
        $members = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', RoleName::Member->value))
            ->count();

        return [
            Stat::make('Sesi Aktif', (string) $activeSessions)
                ->description('Sedang bermain')
                ->color('success'),
            Stat::make('Station Idle', (string) $idle)
                ->description('Siap dipakai')
                ->color('gray'),
            Stat::make('Station Maintenance', (string) $maintenance)
                ->color($maintenance > 0 ? 'warning' : 'gray'),
            Stat::make('Total Member', (string) $members),
        ];
    }
}
