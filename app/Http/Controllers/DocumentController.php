<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentPlaceholders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            // VALIDATION: Use the Model's definition
            'role' => ['nullable', Rule::in(Document::getRoles())],
        ]);

        $user = Auth::user();

        $document = Document::create([
            'clinic_id' => $user->clinic_id,
            'user_id' => $user->id,
            'name' => $request->name,
            // Use constant or default
            'role' => $request->role ?? Document::ROLE_GENERAL,
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
        $document = Document::where('clinic_id', Auth::user()->clinic_id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'role' => ['required', Rule::in(Document::getRoles())],
        ]);

        $document->update([
            'name' => $request->name,
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'Document details updated successfully.');
    }


    public function duplicate($id)
    {
        // 1. Find Original
        $original = Document::where('clinic_id', Auth::user()->clinic_id)->findOrFail($id);

        // 2. Replicate (Laravel helper to copy attributes)
        $newDoc = $original->replicate();

        // 3. Customize the new copy
        $newDoc->name = $original->name . ' - Copy';
        $newDoc->created_by = Auth::id(); // Set current user as creator
        $newDoc->created_at = now();
        $newDoc->updated_at = now();

        // 4. Save
        $newDoc->save();

        return redirect()->back()->with('success', 'Document duplicated successfully.');
    }



    public function printPreview(Request $request, $id)
    {
        $document = Document::where('clinic_id', Auth::user()->clinic_id)->findOrFail($id);
        return $this->renderPreview($request, $document);
    }


    public function printPreviewByType(Request $request, $type)
    {
        // Find the latest document with this role (e.g., 'prescription')
        $document = Document::where('clinic_id', Auth::user()->clinic_id)
            ->where('role', $type)
            ->latest() // Get the most recently created one
            ->first();

        if (!$document) {
            return response()->view('errors.404', [
                'message' => "No template found for type: " . ucfirst($type) . ". Please create one in Documents."
            ], 404);
            // Or simply: abort(404, 'Template not found');
        }

        return $this->renderPreview($request, $document);
    }



    private function renderPreview(Request $request, Document $document)
    {
        // 1. Identify Data Source
        $modelType = $request->query('model');
        $modelId = $request->query('id');
        $options = ['rx_index' => $request->query('rx_index')];

        $placeholders = [];

        // 2. Fetch Data Dynamically
        if ($modelType && $modelId) {
            $modelClass = match ($modelType) {
                'appointment' => \App\Models\Appointment::class,
                'patient' => \App\Models\Patient::class,
                'doctor' => \App\Models\User::class,
                default => null,
            };

            if ($modelClass) {
                $dataModel = $modelClass::find($modelId);
                if ($dataModel) {
                    $placeholders = DocumentPlaceholders::map($dataModel, $options);
                }
            }
        }

        // 3. Hydrate Content
        $content = $document->content;
        $elements = $content['elements'] ?? [];

        foreach ($elements as &$el) {
            if ($el['type'] === 'text' && !empty($el['content'])) {
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