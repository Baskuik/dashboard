<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ChartDataWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            StatsOverviewWidget::class,
            ChartDataWidget::class,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}


