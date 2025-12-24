<?php

namespace App\Http\Controllers;

use App\Models\ClinicImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClinicImageController extends Controller
{
    /**
     * Return JSON list of images for the library.
     */
    public function index()
    {
        $images = ClinicImage::where('clinic_id', Auth::user()->clinic_id)
            ->latest()
            ->get(['id', 'path', 'filename']); // Only fetch what we need

        return response()->json($images);
    }

    /**
     * Handle AJAX Upload.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
        ]);

        $file = $request->file('image');
        $clinicId = Auth::user()->clinic_id;

        // Store file: storage/app/public/clinics/{id}/images
        $path = $file->store("clinics/{$clinicId}/images", 'public');

        $image = ClinicImage::create([
            'clinic_id' => $clinicId,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json($image);
    }

    /**
     * Soft Delete the image.
     */
    public function destroy($id)
    {
        $image = ClinicImage::where('clinic_id', Auth::user()->clinic_id)->findOrFail($id);
        $image->delete();

        return response()->json(['success' => true]);
    }
}