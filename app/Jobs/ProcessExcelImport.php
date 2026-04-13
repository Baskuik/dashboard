<?php

namespace App\Jobs;

use App\Models\Upload;
use App\Services\ExcelImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessExcelImport implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Upload $upload
    ) {
    }

    public function handle(ExcelImportService $importService): void
    {
        try {
            Log::info('Starting Excel import for upload: ' . $this->upload->filename);
            Log::debug('Upload status: ' . $this->upload->status);

            $importService->import($this->upload);

            Log::info('Successfully processed upload: ' . $this->upload->filename);
        } catch (\Exception $e) {
            Log::error('Excel import failed for upload: ' . $this->upload->filename . ' - ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
