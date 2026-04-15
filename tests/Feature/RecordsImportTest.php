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

    public function test_import_works_with_actual_excel_column_order_and_multiple_rows(): void
    {
        $user = User::factory()->create();
        $upload = Upload::create([
            'user_id' => $user->id,
            'filename' => 'records-import-actual-format.xlsx',
            'status' => 'pending',
        ]);

        $filePath = $this->createSpreadsheet([
            ['ID', 'Medewerker', 'Actie', 'Kosten', 'Uren', 'Notities', 'Upload ID', 'Datum'],
            ['1', 'Sanne', 'Reparatie', '69,35', '7,3', 'Defect onderdeel gerepareerd', '10', '2024-10-07'],
            ['2', 'Piet', 'Controle', '458,02', '4,2', 'Algemene controle uitgevoerd', '10', '2024-04-13'],
        ]);

        $importer = new RecordsImport($upload, $user->id);
        $count = $importer->import($filePath);

        $this->assertSame(2, $count);

        $records = Record::query()->orderBy('date')->get();
        $this->assertCount(2, $records);

        $this->assertSame('2024-04-13', $records[0]->date?->format('Y-m-d'));
        $this->assertSame('Controle', $records[0]->action);
        $this->assertSame('Piet', $records[0]->worker);
        $this->assertSame('Algemene controle uitgevoerd', $records[0]->description);
        $this->assertEquals(4.2, (float) $records[0]->time);
        $this->assertEquals(458.02, (float) $records[0]->costs);

        $this->assertSame('2024-10-07', $records[1]->date?->format('Y-m-d'));
        $this->assertSame('Reparatie', $records[1]->action);
        $this->assertSame('Sanne', $records[1]->worker);
        $this->assertSame('Defect onderdeel gerepareerd', $records[1]->description);
        $this->assertEquals(7.3, (float) $records[1]->time);
        $this->assertEquals(69.35, (float) $records[1]->costs);
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

    public function test_import_supports_header_variations_with_shuffled_column_order(): void
    {
        $user = User::factory()->create();
        $upload = Upload::create([
            'user_id' => $user->id,
            'filename' => 'records-import-header-variations.xlsx',
            'status' => 'pending',
        ]);

        $filePath = $this->createSpreadsheet([
            ['Date', 'Kosten (€)', 'Type', 'Medeweker', 'Details', 'Hours'],
            ['2024-10-07', '69,35', 'Reparatie', 'Sanne', 'Defect onderdeel gerepareerd', '7,3'],
        ]);

        $importer = new RecordsImport($upload, $user->id);
        $count = $importer->import($filePath);

        $this->assertSame(1, $count);
        $record = Record::query()->firstOrFail();

        $this->assertSame('Sanne', $record->worker);
        $this->assertSame('Reparatie', $record->action);
        $this->assertSame('Defect onderdeel gerepareerd', $record->description);
        $this->assertSame('2024-10-07', $record->date?->format('Y-m-d'));
        $this->assertEquals(69.35, (float) $record->costs);
        $this->assertEquals(7.3, (float) $record->time);
    }

    private function createSpreadsheet(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            $sheet->fromArray($row, null, 'A'.($rowIndex + 1));
        }

        $filePath = tempnam(sys_get_temp_dir(), 'records-import-test-');
        $xlsxPath = $filePath.'.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($xlsxPath);
        @unlink($filePath);

        return $xlsxPath;
    }
}
