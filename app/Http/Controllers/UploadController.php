<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Imports\RecordsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();

        // Sla het bestand eerst op in storage
        $file->storeAs('uploads', $fileName, 'local');

        // Maak upload record aan met status 'processing'
        $upload = Upload::create([
            'user_id' => Auth::id(),
            'filename' => $fileName,
            'status' => 'processing',
            'processed_rows' => 0,
        ]);

        try {
            $importer = new RecordsImport($upload, Auth::id());
            $count = $importer->import($file->getRealPath());

            // Upload markeren als voltooid
            $upload->update([
                'status' => 'completed',
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