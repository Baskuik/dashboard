<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RecordsExport
{
    protected $records;
    protected $currency;

    public function __construct(Collection $records, $currency = 'EUR')
    {
        $this->records = $records;
        $this->currency = $currency;
    }

    public function toSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headings
        $headings = [
            'ID',
            'Medewerker',
            'Actie',
            'Kosten',
            'Uren',
            'Notities',
            'Upload ID',
            'Datum'
        ];

        foreach ($headings as $index => $heading) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $heading);
        }

        // Style headings
        $headerStyle = $sheet->getStyle('A1:H1');
        $headerStyle->getFont()->setBold(true);
        $headerStyle->getFill()->setFillType('solid')->getStartColor()->setARGB('FFD3D3D3');

        // Add data rows
        $row = 2;
        foreach ($this->records as $record) {
            $sheet->setCellValueByColumnAndRow(1, $row, $record->id);
            $sheet->setCellValueByColumnAndRow(2, $row, $record->medewerker ?? 'N/A');
            $sheet->setCellValueByColumnAndRow(3, $row, $record->actie ?? 'N/A');
            $sheet->setCellValueByColumnAndRow(
                4,
                $row,
                number_format($record->kosten ?? 0, 2, ',', '.') . ' ' . $this->currency
            );
            $sheet->setCellValueByColumnAndRow(
                5,
                $row,
                number_format($record->uren ?? 0, 2, ',', '.')
            );
            $sheet->setCellValueByColumnAndRow(6, $row, $record->notities ?? '');
            $sheet->setCellValueByColumnAndRow(7, $row, $record->upload_id ?? '');
            $sheet->setCellValueByColumnAndRow(
                8,
                $row,
                $record->created_at?->format('d-m-Y H:i') ?? ''
            );
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
