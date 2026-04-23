<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class RecordsSummaryExport
{
    protected $records;
    protected $groupBy;
    protected $currency;

    public function __construct(Collection $records, $groupBy = 'medewerker', $currency = 'EUR')
    {
        $this->records = $records;
        $this->groupBy = $groupBy;
        $this->currency = $currency;
    }

    public function toSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary');

        // Get summary data
        if ($this->groupBy === 'medewerker') {
            $summary = $this->getGroupedByEmployee();
            $headings = ['Medewerker', 'Aantal', 'Totale Kosten', 'Totale Uren', 'Gem. Kosten'];
        } else {
            $summary = $this->getGroupedByAction();
            $headings = ['Actie', 'Aantal', 'Totale Kosten', 'Totale Uren', 'Gem. Kosten'];
        }

        // Add headings
        foreach ($headings as $index => $heading) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $heading);
        }

        // Style headings
        $headerStyle = $sheet->getStyle('A1:E1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType('solid')->getStartColor()->setARGB('FFD3D3D3');

        // Add data rows
        $row = 2;
        foreach ($summary as $item) {
            if ($this->groupBy === 'medewerker') {
                $sheet->setCellValueByColumnAndRow(1, $row, $item['medewerker']);
            } else {
                $sheet->setCellValueByColumnAndRow(1, $row, $item['actie']);
            }

            $sheet->setCellValueByColumnAndRow(2, $row, $item['count']);
            $sheet->setCellValueByColumnAndRow(
                3,
                $row,
                number_format($item['total_cost'], 2, ',', '.') . ' ' . $this->currency
            );
            $sheet->setCellValueByColumnAndRow(
                4,
                $row,
                number_format($item['total_hours'], 2, ',', '.')
            );
            $sheet->setCellValueByColumnAndRow(
                5,
                $row,
                number_format($item['avg_cost'], 2, ',', '.') . ' ' . $this->currency
            );
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    protected function getGroupedByEmployee()
    {
        $grouped = $this->records->groupBy('medewerker');
        $summary = [];

        foreach ($grouped as $medewerker => $items) {
            $summary[] = [
                'medewerker' => $medewerker,
                'count' => $items->count(),
                'total_cost' => $items->sum('kosten'),
                'total_hours' => $items->sum('uren'),
                'avg_cost' => $items->avg('kosten'),
            ];
        }

        return $summary;
    }

    protected function getGroupedByAction()
    {
        $grouped = $this->records->groupBy('actie');
        $summary = [];

        foreach ($grouped as $actie => $items) {
            $summary[] = [
                'actie' => $actie,
                'count' => $items->count(),
                'total_cost' => $items->sum('kosten'),
                'total_hours' => $items->sum('uren'),
                'avg_cost' => $items->avg('kosten'),
            ];
        }

        return $summary;
    }
}
