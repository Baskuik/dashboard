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
        })->map->count();

        // Cost per employee (worker)
        $costPerWorker = $records->groupBy('worker')
            ->map(function ($workerRecords) {
                return $workerRecords->sum('costs');
            });

        // Actions by type
        $actionsByType = $records->groupBy('action')->map->count();

        return [
            'actionsPerMonth' => $actionsPerMonth,
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
