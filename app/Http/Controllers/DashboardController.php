<?php

namespace App\Http\Controllers;

use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    /**
     * Returns stats for the authenticated user.
     * All values default to 0 when no records exist.
     */
    private function getStats(): array
    {
        $userId = Auth::id();

        $records = Record::where('user_id', $userId);

        return [
            'total_actions'    => (clone $records)->count(),
            'total_cost'       => (clone $records)->sum('costs') ?? 0,
            'avg_duration'     => (clone $records)->avg('time') ?? 0,
            'total_employees'  => (clone $records)->distinct('worker')->count('worker'),
        ];
    }

    /**
     * Returns chart data for the authenticated user.
     * Arrays are empty when no records exist – Chart.js handles this gracefully.
     */
    private function getChartData(): array
    {
        $userId = Auth::id();

        $actionsPerMonth = Record::where('user_id', $userId)
            ->whereNotNull('date')
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $costPerEmployee = Record::where('user_id', $userId)
            ->whereNotNull('worker')
            ->selectRaw('worker, SUM(costs) as total')
            ->groupBy('worker')
            ->orderByDesc('total')
            ->pluck('total', 'worker')
            ->toArray();

        $actionsByType = Record::where('user_id', $userId)
            ->whereNotNull('action')
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderByDesc('count')
            ->pluck('count', 'action')
            ->toArray();

        return compact('actionsPerMonth', 'costPerEmployee', 'actionsByType');
    }

    private function getKostenPerMaand(): array
    {
        return Record::where('user_id', Auth::id())
            ->whereNotNull('date')
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, SUM(costs) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();
    }

    // -------------------------------------------------------
    // Main dashboard
    // -------------------------------------------------------

    public function index()
    {
        $stats           = $this->getStats();
        $chartData       = $this->getChartData();
        $kostenPerMaand  = $this->getKostenPerMaand();

        $paginatedRecords = Record::where('user_id', Auth::id())
            ->orderByDesc('date')
            ->paginate(25);

        return view('dashboard.dashboard', compact(
            'stats',
            'chartData',
            'kostenPerMaand',
            'paginatedRecords'
        ));
    }

    // -------------------------------------------------------
    // Drilldown – per medewerker
    // -------------------------------------------------------

    public function byEmployee()
    {
        $groups = Record::where('user_id', Auth::id())
            ->orderBy('worker')
            ->orderByDesc('date')
            ->get()
            ->groupBy('worker');

        return view('dashboard.records-grouped', [
            'groups'       => $groups,
            'pageTitle'    => 'Records per medewerker',
            'pageSubtitle' => 'Alle acties gegroepeerd per medewerker, gesorteerd op datum',
        ]);
    }

    // -------------------------------------------------------
    // Drilldown – per actie
    // -------------------------------------------------------

    public function byAction()
    {
        $groups = Record::where('user_id', Auth::id())
            ->orderBy('action')
            ->orderByDesc('date')
            ->get()
            ->groupBy('action');

        return view('dashboard.records-grouped', [
            'groups'       => $groups,
            'pageTitle'    => 'Records per actie',
            'pageSubtitle' => 'Alle acties gegroepeerd op actietype, gesorteerd op datum',
        ]);
    }

    // -------------------------------------------------------
    // Drilldown – per kosten (hoog → laag)
    // -------------------------------------------------------

    public function byCost()
    {
        // Group by worker so you see who costs the most, rows sorted cost desc
        $groups = Record::where('user_id', Auth::id())
            ->orderByDesc('costs')
            ->get()
            ->groupBy('worker');

        // Sort groups by their total costs descending
        $groups = $groups->sortByDesc(fn ($recs) => $recs->sum('costs'));

        return view('dashboard.records-grouped', [
            'groups'       => $groups,
            'pageTitle'    => 'Records per kosten',
            'pageSubtitle' => 'Medewerkers gerangschikt op totale kosten (hoogste eerst)',
        ]);
    }

    // -------------------------------------------------------
    // Drilldown – per duur (lang → kort)
    // -------------------------------------------------------

    public function byDuration()
    {
        $groups = Record::where('user_id', Auth::id())
            ->orderByDesc('time')
            ->get()
            ->groupBy('worker');

        $groups = $groups->sortByDesc(fn ($recs) => $recs->sum('time'));

        return view('dashboard.records-grouped', [
            'groups'       => $groups,
            'pageTitle'    => 'Records per duur',
            'pageSubtitle' => 'Medewerkers gerangschikt op totale uren (meeste eerst)',
        ]);
    }
}