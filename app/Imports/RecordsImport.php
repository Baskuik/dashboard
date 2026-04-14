<?php

namespace App\Imports;

use App\Models\Record;
use App\Models\Upload;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RecordsImport
{
    public function __construct(
        private Upload $upload,
        private int $userId
    ) {
    }

    public function import(string $filePath): int
    {
        Log::info('RecordsImport gestart', [
            'filePath' => $filePath,
            'file_exists' => file_exists($filePath),
        ]);

        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            Log::warning('RecordsImport: bestand is leeg');
            return 0;
        }

        // Eerste rij = headers
        $headers = array_map(fn($h) => strtolower(trim((string) $h)), $rows[0]);
        Log::info('RecordsImport headers', ['headers' => $headers]);

        $count = 0;
        $skipped = 0;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Lege rijen overslaan
            if (empty(array_filter($row, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $rowData = array_combine($headers, $row);

            $action = trim((string) ($rowData['actie'] ?? $rowData['action'] ?? ''));
            $date = $rowData['datum'] ?? $rowData['date'] ?? null;

            // Rijen zonder actie én datum overslaan
            if (empty($action) && empty($date)) {
                continue;
            }

            // Totaalrij overslaan
            if (strtolower($action) === 'totaal') {
                continue;
            }

            // Voorbereiding van record data
            $recordData = [
                'upload_id' => $this->upload->bestand_id,
                'user_id' => $this->userId,
                'date' => $this->parseDate($date),
                'action' => $action,
                'description' => trim((string) (
                    $rowData['omschrijving']
                    ?? $rowData['beschrijving']
                    ?? $rowData['description']
                    ?? $rowData['toelichting']
                    ?? $rowData['info']
                    ?? $rowData['opmerking']
                    ?? $rowData['details']
                    ?? $rowData['note']
                    ?? $rowData['notes']
                    ?? ''
                )),
                'worker' => trim((string) ($rowData['medewerker'] ?? $rowData['worker'] ?? '')),
                'time' => $this->parseNumeric($rowData['uren'] ?? $rowData['time'] ?? null),
                'costs' => $this->parseNumeric(
                    $rowData['kosten (€)']
                    ?? $rowData['kosten (â‚¬)']   // encoding fallback
                    ?? $rowData['kosten']
                    ?? $rowData['costs']
                    ?? null
                ),
            ];

            // Valideer record data
            try {
                $validated = Record::validateRecord($recordData);
                Record::create($validated);
                $count++;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $skipped++;
                Log::warning('RecordsImport: rij skipped vanwege validatie fouten', [
                    'row_number' => $i + 1,
                    'errors' => $e->errors(),
                    'data' => $recordData,
                ]);
            } catch (\Exception $e) {
                $skipped++;
                Log::error('RecordsImport: rij skipped vanwege error', [
                    'row_number' => $i + 1,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('RecordsImport klaar', [
            'count' => $count,
            'skipped' => $skipped,
            'total_processed' => $count + $skipped,
        ]);

        return $count;
    }

    private function parseDate(mixed $value): ?string
    {
        if (empty($value))
            return null;

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Exception) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    private function parseNumeric(mixed $value): ?float
    {
        if ($value === null || $value === '')
            return null;
        if (is_numeric($value))
            return (float) $value;

        $value = trim((string) $value);

        // Controleer voor tijd-formaten (H:MM of HH:MM:SS of H;MM:SS of H;MM)
        // Ondersteunt: "1:30", "15:00", "29;15:00", "1;30" etc.
        if (preg_match('/^(\d+)[;:](\d+)(?::(\d+))?$/', $value, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = isset($matches[3]) ? (int) $matches[3] : 0;

            // Zet om naar decimale uren
            // Bijv: 1 uur + 30 minuten = 1 + (30/60) = 1.5 uur
            return $hours + ($minutes / 60) + ($seconds / 3600);
        }

        // Originele numerieke parsing logica
        // Verwijder valutasymbolen
        $value = preg_replace('/[€$£\s]/', '', $value);

        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
