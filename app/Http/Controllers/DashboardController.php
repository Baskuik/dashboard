<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Upload;
use App\Models\UserDashboardWidget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardController extends Controller
{
    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    /**
     * Returns stats for the authenticated user.
     * Only includes records from the latest upload.
     * All values default to 0 when no records exist.
     */
    private function getStats(): array
    {
        $userId = Auth::id();

        // Get only the latest upload
        $latestUpload = Upload::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        if (!$latestUpload) {
            return [
                'total_actions' => 0,
                'total_cost' => 0,
                'avg_duration' => 0,
                'total_employees' => 0,
            ];
        }

        $records = Record::where('user_id', $userId)
            ->where('upload_id', $latestUpload->bestand_id);

        return [
            'total_actions' => (clone $records)->count(),
            'total_cost' => (clone $records)->sum('costs') ?? 0,
            'avg_duration' => (clone $records)->avg('time') ?? 0,
            'total_employees' => (clone $records)->distinct()->count('worker'),
        ];
    }

    /**
     * Returns chart data for the authenticated user.
     * Only includes records from the latest upload.
     * Arrays are empty when no records exist – Chart.js handles this gracefully.
     */
    private function getChartData(): array
    {
        $userId = Auth::id();

        // Get only the latest upload
        $latestUpload = Upload::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        $baseQuery = Record::where('user_id', $userId);
        if ($latestUpload) {
            $baseQuery->where('upload_id', $latestUpload->bestand_id);
        }

        $actionsPerMonth = (clone $baseQuery)
            ->whereNotNull('date')
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $costPerEmployee = (clone $baseQuery)
            ->whereNotNull('worker')
            ->selectRaw('worker, SUM(costs) as total')
            ->groupBy('worker')
            ->orderByDesc('total')
            ->pluck('total', 'worker')
            ->toArray();

        $actionsByType = (clone $baseQuery)
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
        $userId = Auth::id();

        // Get only the latest upload
        $latestUpload = Upload::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        $query = Record::where('user_id', $userId);

        if ($latestUpload) {
            $query->where('upload_id', $latestUpload->bestand_id);
        }

        return $query
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

    public function index(Request $request)
    {
        // Get user's selected widgets with their order
        $selectedWidgets = Auth::user()
            ->dashboardWidgets()
            ->orderBy('order')
            ->pluck('widget_key')
            ->toArray();

        // Get the most recent upload for this user
        $latestUpload = Upload::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->first();

        // Get filter parameters
        $search = strtolower($request->get('search', ''));
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $minCost = $request->get('min_cost');
        $maxCost = $request->get('max_cost');

        // Build query with filters in SQL (avoid loading all records into memory)
        $query = Record::where('user_id', Auth::id());
        if ($latestUpload) {
            $query->where('upload_id', $latestUpload->bestand_id);
        }

        // Apply filters as SQL conditions
        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        if ($minCost !== null && $minCost !== '') {
            $query->where('costs', '>=', (float) $minCost);
        }
        if ($maxCost !== null && $maxCost !== '') {
            $query->where('costs', '<=', (float) $maxCost);
        }

        // Clone query for pagination (without search, will add in PHP after)
        $paginationQuery = (clone $query)->orderByDesc('date');

        // Get all records for stats and charts (filtered by basic conditions, search will be applied in PHP)
        $allRecords = $query->get();

        // Apply search filter in PHP (since LIKE doesn't work well with nullable columns in a generic way)
        $records = $allRecords;
        if ($search) {
            $records = $records->filter(function ($record) use ($search) {
                return stripos($record->worker ?? '', $search) !== false ||
                    stripos($record->action ?? '', $search) !== false;
            });
        }

        // Calculate dynamic stats based on filtered records
        $stats = [
            'total_actions' => $records->count(),
            'total_cost' => $records->sum('costs') ?? 0,
            'avg_duration' => $records->count() > 0 ? $records->sum('time') / $records->count() : 0,
            'total_employees' => $records->pluck('worker')->unique()->count(),
        ];

        // Calculate dynamic chart data based on filtered records
        $actionsPerMonth = $records
            ->where('date', '!=', null)
            ->groupBy(function ($record) {
                return \Carbon\Carbon::parse($record->date)->format('Y-m');
            })
            ->map(fn($group) => $group->count())
            ->sortKeys()
            ->toArray();

        $costPerEmployee = $records
            ->where('worker', '!=', null)
            ->groupBy('worker')
            ->map(fn($recs) => $recs->sum('costs'))
            ->sortDesc()
            ->toArray();

        $actionsByType = $records
            ->where('action', '!=', null)
            ->groupBy('action')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->toArray();

        $chartData = compact('actionsPerMonth', 'costPerEmployee', 'actionsByType');

        $kostenPerMaand = $records
            ->where('date', '!=', null)
            ->groupBy(function ($record) {
                return \Carbon\Carbon::parse($record->date)->format('Y-m');
            })
            ->map(fn($recs) => $recs->sum('costs'))
            ->sortKeys()
            ->toArray();

        // Paginated records for display
        $sortBy = $request->get('sort_by', 'date');
        $sortDir = $request->get('sort_dir', 'desc');

        // Validate sort direction
        $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : 'desc';

        // Sort by the requested column
        if ($sortDir === 'asc') {
            $sortedRecords = $records->sortBy($sortBy)->values();
        } else {
            $sortedRecords = $records->sortByDesc($sortBy)->values();
        }

        $perPage = 25;
        $page = $request->get('page', 1);
        $paginatedRecords = new LengthAwarePaginator(
            $sortedRecords->slice(($page - 1) * $perPage, $perPage)->values(),
            $sortedRecords->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('dashboard.dashboard', compact(
            'stats',
            'chartData',
            'kostenPerMaand',
            'paginatedRecords',
            'search',
            'fromDate',
            'toDate',
            'minCost',
            'maxCost',
            'selectedWidgets'
        ));
    }

    /**
     * API endpoint for real-time dashboard data filtering
     * Returns JSON with stats, charts, and filtered records
     */
    public function getFilteredData(Request $request)
    {
        // Get filter parameters
        $search = strtolower($request->get('search', ''));
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $minCost = $request->get('min_cost');
        $maxCost = $request->get('max_cost');
        $currency = $request->get('currency', 'EUR');
        $currencyRate = (float) $request->get('currency_rate', 1.0);

        // Get the most recent upload for this user
        $latestUpload = Upload::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->first();

        // Build query with SQL filters (avoid loading all records into memory)
        $query = Record::where('user_id', Auth::id());
        if ($latestUpload) {
            $query->where('upload_id', $latestUpload->bestand_id);
        }

        // Apply date filters as SQL conditions
        if ($fromDate) {
            $query->whereDate('date', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('date', '<=', $toDate);
        }

        // Apply cost filters (normalized from display currency to EUR)
        if ($minCost !== null && $minCost !== '') {
            $minCostEur = (float) $minCost / $currencyRate;
            $query->where('costs', '>=', $minCostEur);
        }
        if ($maxCost !== null && $maxCost !== '') {
            $maxCostEur = (float) $maxCost / $currencyRate;
            $query->where('costs', '<=', $maxCostEur);
        }

        // Get filtered records (still need collection for search and aggregation)
        $allRecords = $query->get();

        // Apply search filter in PHP
        $records = $allRecords;
        if ($search) {
            $records = $records->filter(function ($record) use ($search) {
                return stripos($record->worker ?? '', $search) !== false ||
                    stripos($record->action ?? '', $search) !== false;
            });
        }

        // Calculate stats in EUR (no conversion here)
        $totalCost = ($records->sum('costs') ?? 0);
        $stats = [
            'total_actions' => $records->count(),
            'total_cost' => number_format($totalCost, 2, '.', ''),
            'avg_duration' => $records->count() > 0 ? number_format($records->sum('time') / $records->count(), 1, '.', '') : 0,
            'total_employees' => $records->pluck('worker')->unique()->count(),
        ];

        // Calculate dynamic chart data in EUR (no conversion here)
        $actionsPerMonth = $records
            ->where('date', '!=', null)
            ->groupBy(function ($record) {
                return \Carbon\Carbon::parse($record->date)->format('Y-m');
            })
            ->map(fn($group) => $group->count())
            ->sortKeys()
            ->toArray();

        $costPerEmployee = $records
            ->where('worker', '!=', null)
            ->groupBy('worker')
            ->map(fn($recs) => $recs->sum('costs'))
            ->sortDesc()
            ->toArray();

        $actionsByType = $records
            ->where('action', '!=', null)
            ->groupBy('action')
            ->map(fn($group) => $group->count())
            ->sortDesc()
            ->toArray();

        $kostenPerMaand = $records
            ->where('date', '!=', null)
            ->groupBy(function ($record) {
                return \Carbon\Carbon::parse($record->date)->format('Y-m');
            })
            ->map(fn($recs) => $recs->sum('costs'))
            ->sortKeys()
            ->toArray();

        return response()->json([
            'stats' => $stats,
            'charts' => [
                'actionsPerMonth' => $actionsPerMonth,
                'costPerEmployee' => (object) $costPerEmployee,
                'actionsByType' => (object) $actionsByType,
            ],
            'kostenPerMaand' => (object) $kostenPerMaand,
            'recordCount' => $records->count(),
            'currency' => $currency,
        ]);
    }

    // -------------------------------------------------------
    // Drilldown – per medewerker
    // -------------------------------------------------------

    public function byEmployee(Request $request)
    {
        $search = strtolower($request->get('search', ''));
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $minCost = $request->get('min_cost');
        $maxCost = $request->get('max_cost');
        $sortBy = $request->get('sort_by', 'worker');
        $sortDir = $request->get('sort_dir', 'asc');

        $records = Record::where('user_id', Auth::id())->get();

        // Filter by search term if provided
        if ($search) {
            $records = $records->filter(function ($record) use ($search) {
                return stripos($record->worker ?? '', $search) !== false ||
                    stripos($record->action ?? '', $search) !== false;
            });
        }

        // Filter by date range if provided
        if ($fromDate) {
            $records = $records->filter(function ($record) use ($fromDate) {
                return $record->date && $record->date >= $fromDate;
            });
        }
        if ($toDate) {
            $records = $records->filter(function ($record) use ($toDate) {
                return $record->date && $record->date <= $toDate;
            });
        }

        // Filter by cost range if provided
        if ($minCost !== null && $minCost !== '') {
            $records = $records->filter(function ($record) use ($minCost) {
                return $record->costs >= (float) $minCost;
            });
        }
        if ($maxCost !== null && $maxCost !== '') {
            $records = $records->filter(function ($record) use ($maxCost) {
                return $record->costs <= (float) $maxCost;
            });
        }

        // Group records FIRST, then sort within groups
        $groups = $records->groupBy('worker');

        // Sort groups based on sort_by parameter
        if ($sortBy === 'worker') {
            // Sort groups by worker name
            if ($sortDir === 'asc') {
                $groups = $groups->sortKeys();
            } else {
                $groups = $groups->sortKeys()->reverse();
            }
        } else {
            // Sort groups by aggregate of the sort field
            if ($sortDir === 'asc') {
                $groups = $groups->sortBy(function ($recs) use ($sortBy) {
                    if ($sortBy === 'date')
                        return $recs->min('date');
                    if ($sortBy === 'costs')
                        return $recs->sum('costs');
                    if ($sortBy === 'time')
                        return $recs->sum('time');
                    return $recs->first()?->{$sortBy};
                });
            } else {
                $groups = $groups->sortByDesc(function ($recs) use ($sortBy) {
                    if ($sortBy === 'date')
                        return $recs->max('date');
                    if ($sortBy === 'costs')
                        return $recs->sum('costs');
                    if ($sortBy === 'time')
                        return $recs->sum('time');
                    return $recs->last()?->{$sortBy};
                });
            }
        }

        return view('dashboard.records-grouped', [
            'groups' => $groups,
            'pageTitle' => 'Records per medewerker',
            'pageSubtitle' => 'Alle acties gegroepeerd per medewerker, gesorteerd op datum',
            'search' => $search,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'minCost' => $minCost,
            'maxCost' => $maxCost,
        ]);
    }

    // -------------------------------------------------------
    // Drilldown – per actie
    // -------------------------------------------------------

    public function byAction(Request $request)
    {
        $search = strtolower($request->get('search', ''));
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $minCost = $request->get('min_cost');
        $maxCost = $request->get('max_cost');
        $sortBy = $request->get('sort_by', 'action');
        $sortDir = $request->get('sort_dir', 'asc');

        $records = Record::where('user_id', Auth::id())->get();

        // Filter by search term if provided
        if ($search) {
            $records = $records->filter(function ($record) use ($search) {
                return stripos($record->action ?? '', $search) !== false ||
                    stripos($record->worker ?? '', $search) !== false;
            });
        }

        // Filter by date range if provided
        if ($fromDate) {
            $records = $records->filter(function ($record) use ($fromDate) {
                return $record->date && $record->date >= $fromDate;
            });
        }
        if ($toDate) {
            $records = $records->filter(function ($record) use ($toDate) {
                return $record->date && $record->date <= $toDate;
            });
        }

        // Filter by cost range if provided
        if ($minCost !== null && $minCost !== '') {
            $records = $records->filter(function ($record) use ($minCost) {
                return $record->costs >= (float) $minCost;
            });
        }
        if ($maxCost !== null && $maxCost !== '') {
            $records = $records->filter(function ($record) use ($maxCost) {
                return $record->costs <= (float) $maxCost;
            });
        }

        // Apply sorting
        if ($sortDir === 'asc') {
            $records = $records->sortBy($sortBy)->values();
        } else {
            $records = $records->sortByDesc($sortBy)->values();
        }

        $groups = $records->groupBy('action');

        // Sort groups based on sort_by parameter
        if ($sortBy === 'action') {
            // Sort groups by action name
            if ($sortDir === 'asc') {
                $groups = $groups->sortKeys();
            } else {
                $groups = $groups->sortKeys()->reverse();
            }
        } else {
            // Sort groups by aggregate of the sort field
            if ($sortDir === 'asc') {
                $groups = $groups->sortBy(function ($recs) use ($sortBy) {
                    if ($sortBy === 'date')
                        return $recs->min('date');
                    if ($sortBy === 'costs')
                        return $recs->sum('costs');
                    if ($sortBy === 'time')
                        return $recs->sum('time');
                    return $recs->first()?->{$sortBy};
                });
            } else {
                $groups = $groups->sortByDesc(function ($recs) use ($sortBy) {
                    if ($sortBy === 'date')
                        return $recs->max('date');
                    if ($sortBy === 'costs')
                        return $recs->sum('costs');
                    if ($sortBy === 'time')
                        return $recs->sum('time');
                    return $recs->last()?->{$sortBy};
                });
            }
        }

        return view('dashboard.records-grouped', [
            'groups' => $groups,
            'pageTitle' => 'Records per actie',
            'pageSubtitle' => 'Alle acties gegroepeerd op actietype, gesorteerd op datum',
            'search' => $search,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'minCost' => $minCost,
            'maxCost' => $maxCost,
        ]);
    }

    // -------------------------------------------------------
    // Drilldown – per kosten (hoog → laag)
    // -------------------------------------------------------

    public function byCost(Request $request)
    {
        $search = strtolower($request->get('search', ''));
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $minCost = $request->get('min_cost');
        $maxCost = $request->get('max_cost');
        $sortBy = $request->get('sort_by', 'costs');
        $sortDir = $request->get('sort_dir', 'desc');

        $records = Record::where('user_id', Auth::id())->get();

        // Filter by search term if provided
        if ($search) {
            $records = $records->filter(function ($record) use ($search) {
                return stripos($record->worker ?? '', $search) !== false ||
                    stripos($record->action ?? '', $search) !== false;
            });
        }

        // Filter by date range if provided
        if ($fromDate) {
            $records = $records->filter(function ($record) use ($fromDate) {
                return $record->date && $record->date >= $fromDate;
            });
        }
        if ($toDate) {
            $records = $records->filter(function ($record) use ($toDate) {
                return $record->date && $record->date <= $toDate;
            });
        }

        // Filter by cost range if provided
        if ($minCost !== null && $minCost !== '') {
            $records = $records->filter(function ($record) use ($minCost) {
                return $record->costs >= (float) $minCost;
            });
        }
        if ($maxCost !== null && $maxCost !== '') {
            $records = $records->filter(function ($record) use ($maxCost) {
                return $record->costs <= (float) $maxCost;
            });
        }

        // Group records FIRST, then sort within groups
        $groups = $records->groupBy('worker');

        // Sort groups based on sort_by parameter
        if ($sortBy === 'worker') {
            // Sort groups by worker name
            if ($sortDir === 'asc') {
                $groups = $groups->sortKeys();
            } else {
                $groups = $groups->sortKeys()->reverse();
            }
        } else {
            // Sort groups by aggregate of the sort field
            if ($sortDir === 'asc') {
                $groups = $groups->sortBy(function ($recs) use ($sortBy) {
                    if ($sortBy === 'date')
                        return $recs->min('date');
                    if ($sortBy === 'costs')
                        return $recs->sum('costs');
                    if ($sortBy === 'time')
                        return $recs->sum('time');
                    return $recs->first()?->{$sortBy};
                });
            } else {
                $groups = $groups->sortByDesc(function ($recs) use ($sortBy) {
                    if ($sortBy === 'date')
                        return $recs->max('date');
                    if ($sortBy === 'costs')
                        return $recs->sum('costs');
                    if ($sortBy === 'time')
                        return $recs->sum('time');
                    return $recs->last()?->{$sortBy};
                });
            }
        }

        return view('dashboard.records-grouped', [
            'groups' => $groups,
            'pageTitle' => 'Records per kosten',
            'pageSubtitle' => 'Medewerkers gerangschikt op totale kosten (hoogste eerst)',
            'search' => $search,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'minCost' => $minCost,
            'maxCost' => $maxCost,
        ]);
    }

    // -------------------------------------------------------
    // Drilldown – per duur (lang → kort)
    // -------------------------------------------------------

    public function byDuration(Request $request)
    {
        $search = strtolower($request->get('search', ''));
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $minCost = $request->get('min_cost');
        $maxCost = $request->get('max_cost');
        $sortBy = $request->get('sort_by', 'time');
        $sortDir = $request->get('sort_dir', 'desc');

        $records = Record::where('user_id', Auth::id())->get();

        // Filter by search term if provided
        if ($search) {
            $records = $records->filter(function ($record) use ($search) {
                return stripos($record->worker, $search) !== false ||
                    stripos($record->action, $search) !== false;
            });
        }

        // Filter by date range if provided
        if ($fromDate) {
            $records = $records->filter(function ($record) use ($fromDate) {
                return $record->date && $record->date >= $fromDate;
            });
        }
        if ($toDate) {
            $records = $records->filter(function ($record) use ($toDate) {
                return $record->date && $record->date <= $toDate;
            });
        }

        // Filter by cost range if provided
        if ($minCost !== null && $minCost !== '') {
            $records = $records->filter(function ($record) use ($minCost) {
                return $record->costs >= (float) $minCost;
            });
        }
        if ($maxCost !== null && $maxCost !== '') {
            $records = $records->filter(function ($record) use ($maxCost) {
                return $record->costs <= (float) $maxCost;
            });
        }

        // Group records FIRST, then sort within groups
        $groups = $records->groupBy('worker');

        // Sort groups based on sort_by parameter
        if ($sortBy === 'worker') {
            // Sort groups by worker name
            if ($sortDir === 'asc') {
                $groups = $groups->sortKeys();
            } else {
                $groups = $groups->sortKeys()->reverse();
            }
        } else {
            // Sort groups by aggregate of the sort field
            if ($sortDir === 'asc') {
                $groups = $groups->sortBy(function ($recs) use ($sortBy) {
                    if ($sortBy === 'date')
                        return $recs->min('date');
                    if ($sortBy === 'costs')
                        return $recs->sum('costs');
                    if ($sortBy === 'time')
                        return $recs->sum('time');
                    return $recs->first()?->{$sortBy};
                });
            } else {
                $groups = $groups->sortByDesc(function ($recs) use ($sortBy) {
                    if ($sortBy === 'date')
                        return $recs->max('date');
                    if ($sortBy === 'costs')
                        return $recs->sum('costs');
                    if ($sortBy === 'time')
                        return $recs->sum('time');
                    return $recs->last()?->{$sortBy};
                });
            }
        }

        return view('dashboard.records-grouped', [
            'groups' => $groups,
            'pageTitle' => 'Records per duur',
            'pageSubtitle' => 'Medewerkers gerangschikt op totale uren (meeste eerst)',
            'search' => $search,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'minCost' => $minCost,
            'maxCost' => $maxCost,
        ]);
    }
}