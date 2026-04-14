<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class ChartDataWidget extends Widget
{
    protected string $view = 'filament.widgets.chart-data';

    public function getChartData(): array
    {
        $records = Record::all();

        // Actions per month
        $actionsPerMonth = $records->groupBy(function ($record) {
            $date = $record->date ?? now()->format('Y-m-d');
            return \Carbon\Carbon::parse($date)->format('Y-m');
        })->map->count()->toArray();

        // Cost per month
        $costPerMonth = $records->groupBy(function ($record) {
            $date = $record->date ?? now()->format('Y-m-d');
            return \Carbon\Carbon::parse($date)->format('Y-m');
        })->map(function ($monthRecords) {
            return $monthRecords->sum('costs');
        })->toArray();

        // Cost per employee (worker)
        $costPerWorker = $records->groupBy('worker')
            ->map(function ($workerRecords) {
                return $workerRecords->sum('costs');
            })->toArray();

        // Actions by type
        $actionsByType = $records->groupBy('action')->map->count()->toArray();

        return [
            'actionsPerMonth' => $actionsPerMonth,
            'costPerMonth' => $costPerMonth,
            'costPerEmployee' => $costPerWorker,
            'actionsByType' => $actionsByType,
        ];
    }

    public function render(): View
    {
        return view($this->view, [
            'chartData' => $this->getChartData(),
        ]);
    }
}
