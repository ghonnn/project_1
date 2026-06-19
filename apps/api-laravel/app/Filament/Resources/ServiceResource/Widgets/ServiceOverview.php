<?php

namespace App\Filament\Resources\ServiceResource\Widgets;

use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServiceOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total', number_format(Service::query()->count(), 0, ',', '.'))
                ->icon('heroicon-o-user-group')
                ->color('info'),
            Stat::make('PSB Bulan Ini', number_format(Service::query()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(), 0, ',', '.'))
                ->icon('heroicon-o-user-plus')
                ->color('success'),
            Stat::make('Non Aktif', number_format(Service::query()->whereIn('status', ['requested', 'inactive'])->count(), 0, ',', '.'))
                ->icon('heroicon-o-user-minus')
                ->color('warning'),
            Stat::make('Terisolir', number_format(Service::query()->where('status', 'suspended')->count(), 0, ',', '.'))
                ->icon('heroicon-o-user-minus')
                ->color('danger'),
        ];
    }
}
