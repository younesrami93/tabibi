<?php

namespace App\Http\Controllers;

use App\Models\CatalogItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogItemController extends Controller
{
    // 1. SETTINGS PAGE: List items

    // In App\Http\Controllers\CatalogItemController.php

    public function index(Request $request)
    {
        $clinicId = Auth::user()->clinic_id;
        $type = $request->get('type', 'medicine');
        $search = $request->get('q');
        $scope = $request->get('scope', 'all'); // New Parameter (Default: 'all')

        // Start building the query
        $query = CatalogItem::where('type', $type);

        // Apply Scope Filter
        if ($scope === 'mine') {
            // Show ONLY items created by this clinic
            $query->where('clinic_id', $clinicId);
        } elseif ($scope === 'system') {
            // Show ONLY global system items
            $query->whereNull('clinic_id');
        } else {
            // Show BOTH (Default behavior)
            $query->where(function ($q) use ($clinicId) {
                $q->where('clinic_id', $clinicId)
                    ->orWhereNull('clinic_id');
            });
        }

        // Apply Search Filter
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('name')->paginate(20);

        // Keep filters in pagination links
        $items->appends(['type' => $type, 'q' => $search, 'scope' => $scope]);

        if ($request->ajax()) {
            return view('layouts.partials.catalog_list', compact('items', 'type'))->render();
        }

        return view('doctor.catalog', compact('items', 'type', 'scope'));
    }

    // 2. STORE: Create new item
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:medicine,test',
            'name' => 'required|string|max:255',
            // Form/Strength required only if it's a medicine
            'form' => 'nullable|required_if:type,medicine',
            'default_quantity' => 'nullable|integer|min:1',
            'default_frequency' => 'nullable|integer|min:1',
            'default_duration' => 'nullable|integer|min:1',
        ]);

        CatalogItem::create([
            'clinic_id' => Auth::user()->clinic_id, // Always private when created by doc
            'type' => $request->type,
            'name' => $request->name,
            'form' => $request->form,
            'strength' => $request->strength,
            'default_quantity' => $request->default_quantity,
            'default_frequency' => $request->default_frequency,
            'default_duration' => $request->default_duration,
        ]);

        return back()->with('success', 'Item added to catalog.');
    }

    // 3. API SEARCH: For the Prescription Modal
    public function search(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type'); // 'medicine' or 'test'

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = CatalogItem::forClinic(Auth::user()->clinic_id)
            ->where('name', 'like', "%{$query}%")
            ->when($type, function ($q) use ($type) {
                return $q->where('type', $type);
            })
            ->limit(10)
            ->get();

        return response()->json($results);
    }

    // 4. DESTROY: Delete item
    public function destroy(CatalogItem $catalog)
    {
        // Security: Ensure doc only deletes THEIR own items, not global ones
        if ($catalog->clinic_id !== Auth::user()->clinic_id) {
            return back()->with('error', 'You cannot delete system default items.');
        }

        $catalog->delete();
        return back()->with('success', 'Item removed.');
    }
}