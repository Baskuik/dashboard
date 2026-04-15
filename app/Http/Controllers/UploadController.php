<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Models\Record;
use App\Imports\RecordsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        // Delete all old uploads and their records for this user
        $userId = Auth::id();
        $oldUploads = Upload::where('user_id', $userId)->get();

        foreach ($oldUploads as $oldUpload) {
            // Delete all records associated with this upload
            Record::where('upload_id', $oldUpload->bestand_id)->delete();

            // Delete the file from storage if it exists
            $filePath = 'uploads/' . $oldUpload->filename;
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }

            // Delete the upload record
            $oldUpload->delete();
        }

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        // Sla op en bepaal het absolute pad
        $storedPath = $file->storeAs('uploads', $fileName, 'local');
        $absolutePath = storage_path('app/private/' . $storedPath);

        if (!file_exists($absolutePath)) {
            $absolutePath = storage_path('app/' . $storedPath);
        }

        Log::info('Upload opgeslagen', [
            'original_name' => $fileName,
            'absolute_path' => $absolutePath,
            'file_exists' => file_exists($absolutePath),
        ]);

        $upload = Upload::create([
            'user_id' => Auth::id(),
            'filename' => $fileName,
            'status' => 'pending',
            'processed_rows' => 0,
            'expires_at' => now()->addHours(24),
        ]);

        try {
            $importer = new RecordsImport($upload, Auth::id());
            $count = $importer->import($absolutePath);

            $upload->update([
                'status' => $count > 0 ? 'completed' : 'declined',
                'processed_rows' => $count,
            ]);

            return redirect()->route('dashboard')
                ->with('success', "'{$fileName}' succesvol geüpload ({$count} rijen verwerkt).");

        } catch (\Exception $e) {
            Log::error('Upload mislukt: ' . $e->getMessage(), [
                'upload_id' => $upload->bestand_id,
                'trace' => $e->getTraceAsString(),
            ]);

            $upload->update(['status' => 'failed']);

            return redirect()->route('dashboard')
                ->withErrors(['file' => 'Upload mislukt: ' . $e->getMessage()]);
        }
    }
}