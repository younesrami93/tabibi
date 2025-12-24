<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentPlaceholders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $documents = Document::where('clinic_id', $user->clinic_id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('doctor.documents', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();

        $document = Document::create([
            'clinic_id' => $user->clinic_id,
            'user_id' => $user->id,
            'name' => $request->name,
            'role' => $request->role ?? 'General',
            'content' => [],
        ]);

        return redirect()->route('documents.editor', $document->id);
    }

    public function destroy($id)
    {
        $document = Document::where('clinic_id', Auth::user()->clinic_id)->findOrFail($id);
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Document moved to trash.');
    }


    public function edit($id)
    {
        $document = Document::where('clinic_id', Auth::user()->clinic_id)->findOrFail($id);

        $content = $document->content ?? [];

        $editorData = [
            'id' => $document->id,
            'name' => $document->name,
            'bgImage' => $content['bgImage'] ?? null,
            'elements' => $content['elements'] ?? [],
            // NEW: Load Paper Settings (Default to A4 if missing)
            'paper' => $content['paper'] ?? [
                'type' => 'a4',
                'orientation' => 'portrait',
                'w' => 794,
                'h' => 1123,
                'mmW' => 210,
                'mmH' => 297
            ],
        ];

        return view('layouts.editor.document_editor', compact('document', 'editorData'));
    }

    /**
     * API: Update Document Content
     */
    public function updateContent(Request $request, $id)
    {
        $document = Document::where('clinic_id', Auth::user()->clinic_id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'elements' => 'array',
            'bgImage' => 'nullable',
            'paper' => 'array' // <--- Validate the paper array
        ]);

        $document->name = $request->name;

        // Merge paper settings into the JSON content
        $document->content = [
            'bgImage' => $request->bgImage,
            'elements' => $request->elements ?? [],
            'paper' => $request->paper // <--- Save it!
        ];

        $document->save();

        return response()->json(['success' => true]);
    }


    public function update(Request $request, $id)
    {
        // Security: Ensure user owns the clinic
        $document = Document::where('clinic_id', Auth::user()->clinic_id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:50',
        ]);

        $document->update([
            'name' => $request->name,
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'Document details updated successfully.');
    }


    public function printPreview(Request $request, $id)
    {
        $document = Document::where('clinic_id', Auth::user()->clinic_id)->findOrFail($id);

        // 1. Identify Data Source
        $modelType = $request->query('model'); // e.g., 'appointment', 'patient'
        $modelId = $request->query('id');

        $dataModel = null;
        $placeholders = [];

        // 2. Fetch Data Dynamically
        if ($modelType && $modelId) {
            // Mapping string 'appointment' to Model Class
            // You can use a switch, or Laravel's Relation::morphMap if defined
            $modelClass = match ($modelType) {
                'appointment' => \App\Models\Appointment::class,
                'patient' => \App\Models\Patient::class,
                'doctor' => \App\Models\User::class,
                default => null,
            };

            if ($modelClass) {
                $dataModel = $modelClass::find($modelId);
                if ($dataModel) {
                    // 3. Generate Placeholders Dictionary
                    $placeholders = DocumentPlaceholders::map($dataModel);
                }
            }
        }

        // 4. Hydrate (Search & Replace)
        // We do this in PHP so the View receives "clean" data
        $content = $document->content;
        $elements = $content['elements'] ?? [];

        foreach ($elements as &$el) {
            if ($el['type'] === 'text' && !empty($el['content'])) {
                // Replace all keys in the content
                $el['content'] = str_replace(
                    array_keys($placeholders),
                    array_values($placeholders),
                    $el['content']
                );
            }
        }

        return view('layouts.document_print', compact('document', 'elements', 'content'));
    }
}