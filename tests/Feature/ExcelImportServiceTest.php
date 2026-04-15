<?php

namespace Tests\Feature;

use App\Models\Record;
use App\Models\Upload;
use App\Models\User;
use App\Services\ExcelImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelReader;
use Tests\TestCase;

class ExcelImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_detect_columns_matches_normalized_header_variants(): void
    {
        $service = new ExcelImportService();
        $headers = [' Medewerker ', 'ACTIE', 'Omschrijving', 'Datum', 'Kosten (€)', 'Uren'];

        $method = new \ReflectionMethod($service, 'detectColumns');
        $method->setAccessible(true);
        $columns = $method->invoke($service, $headers);

        $this->assertSame('medewerker', $columns['worker']);
        $this->assertSame('actie', $columns['action']);
        $this->assertSame('omschrijving', $columns['description']);
        $this->assertSame('datum', $columns['date']);
        $this->assertSame('kosten', $columns['costs']);
        $this->assertSame('uren', $columns['time']);
    }

    public function test_import_extracts_values_from_dutch_headers_and_currency_values(): void
    {
        $user = User::factory()->create();
        $upload = Upload::create([
            'user_id' => $user->id,
            'filename' => 'import-test.xlsx',
            'status' => 'pending',
        ]);

        $uploadDir = storage_path('app/private/uploads');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        file_put_contents($uploadDir . '/import-test.xlsx', 'dummy');

        $excelMock = \Mockery::mock(ExcelReader::class);
        $excelMock->shouldReceive('toArray')
            ->once()
            ->andReturn([[
                ['Medewerker', 'Actie', 'Omschrijving', 'Datum', 'Kosten (€)', 'Uren'],
                ['Sanne', 'Reparatie', 'Defect onderdeel gerepareerd', '2024-10-07', '€ 69,35', '7,3'],
            ]]);
        $this->app->instance('excel', $excelMock);

        $service = new ExcelImportService();
        $result = $service->import($upload);

        $this->assertTrue($result);
        $this->assertDatabaseCount('records', 1);

        $record = Record::query()->firstOrFail();
        $this->assertSame('Sanne', $record->worker);
        $this->assertSame('Reparatie', $record->action);
        $this->assertSame('Defect onderdeel gerepareerd', $record->description);
        $this->assertSame('2024-10-07', $record->date?->format('Y-m-d'));
        $this->assertEquals(7.3, (float) $record->time);
        $this->assertEquals(69.35, (float) $record->costs);
    }
}
