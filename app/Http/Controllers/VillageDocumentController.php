<?php

namespace App\Http\Controllers;

use App\Http\Requests\VillageRequest\StoreVillageDocumentRequest;
use App\Models\Village;
use App\Models\VillageDocument;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VillageDocumentController extends Controller
{
    use \App\Http\Traits\VillageAccessAware;

    public function index(Village $village): View
    {
        $this->enforceVillageAccess(auth()->user(), $village->id);
        $documents = $village->documents()->latest()->paginate(15);

        return view('villages.documents', compact('village', 'documents'));
    }

    public function store(Village $village, StoreVillageDocumentRequest $request): RedirectResponse
    {
        $this->enforceVillageAccess(auth()->user(), $village->id);
        $file = $request->file('file');
        $filename = 'doc_' . time() . '_' . $file->getClientOriginalName();
        $file->storeAs('village_documents', $filename, 'public');

        VillageDocument::create([
            'village_id' => $village->id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => 'village_documents/' . $filename,
            'file_type' => $file->getClientOriginalExtension(),
            'uploaded_by' => auth()->id(),
        ]);

        AuditLogService::log('Upload Document', 'Village Documents', "Uploaded document to {$village->village_name}: {$request->title}");

        return redirect()->route('villages.documents.index', $village)->with('success', 'Document uploaded successfully.');
    }

    public function download(Village $village, VillageDocument $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->enforceVillageAccess(auth()->user(), $village->id);
        $diskPath = $document->file_path;

        if (!Storage::disk('public')->exists($diskPath)) {
            abort(404, 'Document file not found.');
        }

        $downloadName = $document->title . '.' . strtolower($document->file_type);
        return Storage::disk('public')->download($diskPath, $downloadName);
    }

    public function destroy(Village $village, VillageDocument $document): RedirectResponse
    {
        $this->enforceVillageAccess(auth()->user(), $village->id);
        $path = storage_path('app/public/' . $document->file_path);
        if (file_exists($path)) {
            unlink($path);
        }

        $title = $document->title;
        $document->delete();

        AuditLogService::log('Delete Document', 'Village Documents', "Deleted document from {$village->village_name}: {$title}");

        return redirect()->route('villages.documents.index', $village)->with('success', "Document \"{$title}\" has been deleted.");
    }
}
