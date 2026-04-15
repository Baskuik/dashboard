<?php

namespace Tests\Feature;

use App\Imports\RecordsImport;
use App\Models\Record;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class RecordsImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_handles_row_length_mismatch_and_maps_columns_by_index(): void
    {
        $user = User::factory()->create();
        $upload = Upload::create([
            'user_id' => $user->id,
            'filename' => 'records-import-mismatch.xlsx',
            'status' => 'pending',
        ]);

        $filePath = $this->createSpreadsheet([
            ['ID', 'Medewerker', 'Actie', 'Kosten', 'Uren', 'Notities', 'Upload ID', 'Datum', ''],
            ['123', 'Sanne', 'Reparatie', '€ 69,35', '7,3', 'Onderdeel vervangen', '69', '2026-04-15'],
        ]);

        $importer = new RecordsImport($upload, $user->id);
        $count = $importer->import($filePath);

        $this->assertSame(1, $count);
        $this->assertDatabaseCount('records', 1);

        $record = Record::query()->firstOrFail();
        $this->assertSame('Sanne', $record->worker);
        $this->assertSame('Reparatie', $record->action);
        $this->assertSame('Onderdeel vervangen', $record->description);
        $this->assertSame('2026-04-15', $record->date?->format('Y-m-d'));
        $this->assertEquals(7.3, (float) $record->time);
        $this->assertEquals(69.35, (float) $record->costs);
    }

    public function test_import_fails_with_clear_error_when_required_columns_are_missing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Verplichte kolommen ontbreken in Excel');

        $user = User::factory()->create();
        $upload = Upload::create([
            'user_id' => $user->id,
            'filename' => 'records-import-missing-columns.xlsx',
            'status' => 'pending',
        ]);

        $filePath = $this->createSpreadsheet([
            ['Medewerker', 'Omschrijving', 'Kosten'],
            ['Sanne', 'Geen actie kolom', '69,35'],
        ]);

        $importer = new RecordsImport($upload, $user->id);
        $importer->import($filePath);
    }

    private function createSpreadsheet(array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            $sheet->fromArray($row, null, 'A' . ($rowIndex + 1));
        }

        $filePath = tempnam(sys_get_temp_dir(), 'records-import-test-');
        $xlsxPath = $filePath . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxPath);
        @unlink($filePath);

        return $xlsxPath;
    }
}
