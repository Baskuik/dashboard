<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class CostOverviewWidget extends ChartWidget
{
    protected ?string $heading = 'Kostenoverzicht per periode';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $userId = Auth::id();

        // Get kosten per maand
        $costPerMonth = Record::query()
            ->where('user_id', $userId)
            ->whereNotNull('date')
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(costs) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Format months for display (e.g., "Januari 2026")
        $labels = array_map(function ($month) {
            $date = \Carbon\Carbon::parse($month . '-01');
            return $date->translatedFormat('F Y'); // "Januari 2026"
        }, array_keys($costPerMonth));

        return [
            'datasets' => [
                [
                    'label' => 'Kosten per maand (€)',
                    'data' => array_values($costPerMonth),
                    'borderColor' => '#06b6d4',
                    'backgroundColor' => 'rgba(6, 182, 212, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 5,
                    'pointBackgroundColor' => '#06b6d4',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
