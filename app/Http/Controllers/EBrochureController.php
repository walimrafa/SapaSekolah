<?php

namespace App\Http\Controllers;

use App\Models\EBrochure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EBrochureController extends Controller
{
    /**
     * Display a listing of E-Brochures.
     */
    public function index(Request $request): View
    {
        $query = EBrochure::with('creator')->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('type')) {
            $query->where('file_type', $request->type);
        }

        if ($request->filled('year')) {
            $query->where('academic_year', $request->year);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        $brochures = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => EBrochure::count(),
            'downloads' => EBrochure::sum('download_count'),
            'pdf_count' => EBrochure::where('file_type', 'pdf')->count(),
            'image_count' => EBrochure::where('file_type', 'image')->count(),
        ];

        $categories = [
            'Brosur Utama',
            'Biaya Kuliah & Lokasi',
            'Program Studi & Fakultas',
            'Beasiswa UBSI',
            'Flyer & Sosialisasi',
        ];

        return view('e-brochures.index', compact('brochures', 'stats', 'categories'));
    }

    /**
     * Store a newly created E-Brochure.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string', 'max:100'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'brochure_file' => [
                'required',
                'file',
                'max:51200', // 50MB
                'mimes:pdf,jpg,jpeg,png,webp',
            ],
        ], [
            'title.required' => 'Judul E-Brosur wajib diisi.',
            'category.required' => 'Kategori brosur wajib dipilih.',
            'brochure_file.required' => 'File E-Brosur wajib diunggah.',
            'brochure_file.mimes' => 'Format file yang didukung: PDF, JPG, PNG, atau WEBP.',
            'brochure_file.max' => 'Ukuran file maksimal adalah 50MB.',
        ]);

        $file = $request->file('brochure_file');
        $ext = strtolower($file->getClientOriginalExtension());
        $fileType = ($ext === 'pdf') ? 'pdf' : 'image';

        $storedPath = $file->store('ebrochures', 'public');

        EBrochure::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'academic_year' => $validated['academic_year'] ?? '2026/2027',
            'description' => $validated['description'] ?? null,
            'file_path' => $storedPath,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $fileType,
            'file_extension' => $ext,
            'file_size' => $file->getSize(),
            'created_by' => auth()->id(),
            'is_active' => true,
            'download_count' => 0,
        ]);

        return redirect()->route('e-brochures.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'E-Brosur Berhasil Diunggah',
                'message' => 'Brosur ' . $validated['title'] . ' telah siap untuk dilihat dan diunduh.',
            ]);
    }

    /**
     * View/stream the E-Brochure file inline (useful for in-app viewing).
     */
    public function show(EBrochure $eBrochure): BinaryFileResponse|StreamedResponse
    {
        if (!Storage::disk('public')->exists($eBrochure->file_path)) {
            abort(404, 'File brosur tidak ditemukan di server.');
        }

        $fullPath = Storage::disk('public')->path($eBrochure->file_path);
        $mime = Storage::disk('public')->mimeType($eBrochure->file_path) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $eBrochure->file_name . '"',
        ]);
    }

    /**
     * Download the E-Brochure file and increment the download counter.
     */
    public function download(EBrochure $eBrochure): StreamedResponse
    {
        if (!Storage::disk('public')->exists($eBrochure->file_path)) {
            abort(404, 'File brosur tidak ditemukan di server.');
        }

        $eBrochure->increment('download_count');

        return Storage::disk('public')->download($eBrochure->file_path, $eBrochure->file_name);
    }

    /**
     * Remove the specified E-Brochure.
     */
    public function destroy(EBrochure $eBrochure): RedirectResponse
    {
        $title = $eBrochure->title;

        // Delete the physical file if it exists
        if (Storage::disk('public')->exists($eBrochure->file_path)) {
            Storage::disk('public')->delete($eBrochure->file_path);
        }

        $eBrochure->delete();

        return redirect()->route('e-brochures.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'E-Brosur Dihapus',
                'message' => 'Brosur ' . $title . ' telah berhasil dihapus.',
            ]);
    }
}
