<?php

namespace App\Filament\Widgets;

use App\Models\Record;
use App\Models\Upload;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class StatsOverviewWidget extends Widget
{
    protected string $view = 'filament.widgets.stats-overview';

    public function getStats(): array
    {
        // Check if any uploads exist
        $uploadsExist = Upload::exists();

        // If no uploads exist, return zeros
        if (!$uploadsExist) {
            return [
                'total_records' => 0,
                'total_cost' => 0,
                'avg_hours' => 0,
                'total_employees' => 0,
            ];
        }

        // Otherwise, fetch and calculate stats from records
        $records = Record::all();

        // Calculate stats directly from typed columns
        $totalTime = $records->sum('time');
        $totalCosts = $records->sum('costs');
        $uniqueWorkers = $records->pluck('worker')->unique()->count();
        $avgTime = $records->count() > 0 ? $totalTime / $records->count() : 0;

        return [
            'total_records' => $records->count(),
            'total_cost' => $totalCosts,
            'avg_hours' => round($avgTime, 2),
            'total_employees' => $uniqueWorkers,
        ];
    }

    public function render(): View
    {
        return view($this->view, [
            'stats' => $this->getStats(),
        ]);
    }
}
