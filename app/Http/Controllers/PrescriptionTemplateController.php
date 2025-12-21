<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use App\Models\PrescriptionTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrescriptionTemplateController extends Controller
{
    // 1. LIST (For the Settings Page)

    public function index(Request $request)
    {
        $query = PrescriptionTemplate::where('clinic_id', Auth::user()->clinic_id)
            ->orderBy('name');

        // Search Filter
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        // Type Filter (Medicine, Test, Mixed)
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $templates = $query->paginate(15);

        // AJAX: Return only the table rows
        if ($request->ajax()) {
            return view('layouts.partials.prescriptions_template_list', compact('templates'))->render();
        }

        return view('doctor.prescriptions_templates', compact('templates'));
    }




    public function store(Request $request)
    {
        // Update Validation: Allow custom text (catalog_item_id is nullable)
        $request->validate([
            'name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string', // Name is now the main requirement
            'items.*.catalog_item_id' => 'nullable|exists:catalog_items,id', // ID is optional
        ]);

        // Auto-Detect Type Logic (Ignore custom items for type detection)
        $itemIds = collect($request->items)->pluck('catalog_item_id')->filter(); // Filter out nulls

        if ($itemIds->isEmpty()) {
            // Default to medicine if only custom text is added
            $templateType = 'medicine';
        } else {
            $types = CatalogItem::whereIn('id', $itemIds)->pluck('type')->unique();
            $templateType = ($types->count() > 1) ? 'mixed' : $types->first();
        }

        PrescriptionTemplate::create([
            'clinic_id' => Auth::user()->clinic_id,
            'name' => $request->name,
            'type' => $templateType,
            'items' => $request->items,
        ]);

        return response()->json(['success' => true, 'message' => 'Template saved successfully.']);
    }


    public function show(PrescriptionTemplate $template)
    {
        if ($template->clinic_id !== Auth::user()->clinic_id)
            abort(403);
        return response()->json($template);
    }

    // 3. DESTROY
    public function destroy(PrescriptionTemplate $template)
    {
        if ($template->clinic_id !== Auth::user()->clinic_id)
            abort(403);
        $template->delete();
        return back()->with('success', 'Template removed.');
    }

    public function update(Request $request, PrescriptionTemplate $template)
    {
        if ($template->clinic_id !== Auth::user()->clinic_id)
            abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string', // Support custom text
            'items.*.catalog_item_id' => 'nullable|exists:catalog_items,id',
        ]);

        // Recalculate Type
        $itemIds = collect($request->items)->pluck('catalog_item_id')->filter();
        if ($itemIds->isEmpty()) {
            $type = 'medicine';
        } else {
            $types = \App\Models\CatalogItem::whereIn('id', $itemIds)->pluck('type')->unique();
            $type = ($types->count() > 1) ? 'mixed' : $types->first();
        }

        $template->update([
            'name' => $request->name,
            'type' => $type,
            'items' => $request->items,
        ]);

        return response()->json(['success' => true, 'message' => 'Template updated successfully.']);
    }


}