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

        // Sla het bestand op en gebruik het opgeslagen pad
        $storedPath = $file->storeAs('uploads', $fileName, 'local');
        $absolutePath = storage_path('app/' . $storedPath);  // ← gebruik dit

        // Maak upload record aan met status 'pending'
        // (de importer zet dit zelf op 'processing' en daarna 'completed'/'declined')
        $upload = Upload::create([
            'user_id' => Auth::id(),
            'filename' => $fileName,
            'status' => 'pending',
            'processed_rows' => 0,
        ]);

        try {
            $importer = new RecordsImport($upload, Auth::id());
            $count = $importer->import($absolutePath); // ← niet meer getRealPath()

            // Upload bijwerken met het aantal verwerkte rijen
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