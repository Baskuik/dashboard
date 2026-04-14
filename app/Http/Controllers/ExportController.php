<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    /**
     * Export records to Excel file
     */
    public function exportRecords(Request $request)
    {
        $userId = Auth::id();

        // Get filter parameters
        $search = strtolower($request->get('search', ''));
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $minCost = $request->get('min_cost');
        $maxCost = $request->get('max_cost');

        // Get the most recent upload for this user
        $latestUpload = Upload::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        // Build query
        $query = Record::where('user_id', $userId);
        if ($latestUpload) {
            $query->where('upload_id', $latestUpload->bestand_id);
        }

        // Apply filters
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

        // Get all records
        $records = $query->orderByDesc('date')->get();

        // Apply search filter in memory
        if ($search) {
            $records = $records->filter(function ($record) use ($search) {
                return stripos($record->worker ?? '', $search) !== false ||
                    stripos($record->action ?? '', $search) !== false;
            })->values();
        }

        // Generate CSV content
        $csv = "Datum,Actie,Omschrijving,Medewerker,Uren,Kosten (€)\n";

        foreach ($records as $record) {
            $date = $record->date ? $record->date->format('d-m-Y') : '';
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s"' . "\n",
                $date,
                str_replace('"', '""', $record->action ?? ''),
                str_replace('"', '""', $record->description ?? ''),
                str_replace('"', '""', $record->worker ?? ''),
                number_format($record->time ?? 0, 2, ',', '.'),
                number_format($record->costs ?? 0, 2, ',', '.')
            );
        }

        // Return CSV file download
        $filename = 'export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export records summary statistics
     */
    public function exportSummary(Request $request)
    {
        $userId = Auth::id();

        // Get filter parameters
        $search = strtolower($request->get('search', ''));
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $minCost = $request->get('min_cost');
        $maxCost = $request->get('max_cost');

        // Get the most recent upload for this user
        $latestUpload = Upload::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        // Build query
        $query = Record::where('user_id', $userId);
        if ($latestUpload) {
            $query->where('upload_id', $latestUpload->bestand_id);
        }

        // Apply filters
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

        // Get records
        $records = $query->get();

        // Apply search filter in memory
        if ($search) {
            $records = $records->filter(function ($record) use ($search) {
                return stripos($record->worker ?? '', $search) !== false ||
                    stripos($record->action ?? '', $search) !== false;
            });
        }

        // Calculate stats
        $totalActions = $records->count();
        $totalCost = $records->sum('costs') ?? 0;
        $avgDuration = $records->count() > 0 ? $records->sum('time') / $records->count() : 0;
        $totalEmployees = $records->pluck('worker')->unique()->count();

        // Costs per employee
        $costPerEmployee = $records
            ->where('worker', '!=', null)
            ->groupBy('worker')
            ->map(fn($recs) => $recs->sum('costs'))
            ->sortDesc();

        // Actions by type
        $actionsByType = $records
            ->where('action', '!=', null)
            ->groupBy('action')
            ->map(fn($recs) => $recs->count())
            ->sortDesc();

        // Generate summary CSV
        $csv = "SAMENVATTING RAPPORT\n";
        $csv .= "Gegenereerd: " . now()->format('d-m-Y H:i:s') . "\n";
        $csv .= "Periode: " . ($fromDate ?? 'Start') . " tot " . ($toDate ?? 'Nu') . "\n\n";

        $csv .= "TOTALEN\n";
        $csv .= "Totaal acties,Totaal kosten (€),Gem. duur (uren),Aantal medewerkers\n";
        $csv .= sprintf(
            '"%d","%s","%s","%d"' . "\n",
            $totalActions,
            number_format($totalCost, 2, ',', '.'),
            number_format($avgDuration, 1, ',', '.'),
            $totalEmployees
        );

        $csv .= "\nKOSTEN PER MEDEWERKER\n";
        $csv .= "Medewerker,Kosten (€)\n";
        foreach ($costPerEmployee as $worker => $cost) {
            $csv .= sprintf(
                '"%s","%s"' . "\n",
                str_replace('"', '""', $worker),
                number_format($cost, 2, ',', '.')
            );
        }

        $csv .= "\nACTIES PER TYPE\n";
        $csv .= "Actie,Aantal\n";
        foreach ($actionsByType as $action => $count) {
            $csv .= sprintf(
                '"%s","%d"' . "\n",
                str_replace('"', '""', $action),
                $count
            );
        }

        $filename = 'samenvatting_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
