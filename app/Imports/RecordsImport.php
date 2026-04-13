<?php

namespace App\Imports;

use App\Models\Upload;
use App\Services\ExcelImportService;

class RecordsImport
{
    private Upload $upload;
    private int $userId;
    private ExcelImportService $importService;

    public function __construct(Upload $upload, int $userId)
    {
        $this->upload = $upload;
        $this->userId = $userId;
        $this->importService = new ExcelImportService();
    }

    /**
     * Import Excel file from the given path
     */
    public function import(string $filePath): int
    {
        try {
            // Use the Excel import service to process the file
            $this->importService->import($this->upload);

            // Return the number of processed rows
            return $this->upload->processed_rows ?? 0;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
