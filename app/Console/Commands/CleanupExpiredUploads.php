<?php

namespace App\Console\Commands;

use App\Models\Upload;
use App\Models\Record;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired uploads and their associated records from the database and storage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredUploads = Upload::where('expires_at', '<', now())->get();

        if ($expiredUploads->isEmpty()) {
            $this->info('✓ Geen verlopen uploads gevonden.');
            return Command::SUCCESS;
        }

        $count = 0;

        foreach ($expiredUploads as $upload) {
            // Delete all records associated with this upload
            $recordCount = Record::where('upload_id', $upload->bestand_id)->delete();

            // Delete the file from storage if it exists
            $filePath = 'uploads/' . $upload->filename;
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }

            // Delete the upload record
            $upload->delete();

            $count++;
            $this->line("✓ Verwijderd: {$upload->filename} ({$recordCount} records)");
        }

        $this->info("✓ {$count} verlopen upload(s) verwijderd.");

        return Command::SUCCESS;
    }
}
