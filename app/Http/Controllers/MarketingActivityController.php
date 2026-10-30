<?php

namespace App\Http\Controllers;

use App\Models\MarketingActivity;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MarketingActivityController extends Controller
{
    public function index(Request $request): View
    {
        // Auto-sync any completed socializations from FollowUp to MarketingActivity
        try {
            $completedFollowUps = \App\Models\FollowUp::whereNotNull('socialization_completed_at')
                ->with(['school.district'])
                ->get();

            foreach ($completedFollowUps as $fu) {
                FollowUpController::syncFollowUpToMarketingActivity($fu);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error syncing completed socializations to activities: ' . $e->getMessage());
        }

        $query = MarketingActivity::with(['schoolActivities.school.district'])
            ->withCount('schoolActivities');

        if ($request->filled('type')) {
            $query->where('activity_type', $request->type);
        }

        $activities = $query->orderBy('start_date', 'desc')->paginate(15)->withQueryString();
        $schools = School::orderBy('name')->get();

        return view('activities.index', compact('activities', 'schools'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'activity_type' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'document' => [
                'nullable',
                'file',
                'max:51200', // 50MB
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['rar', 'zip', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'webp', '7z'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Format dokumen harus berupa: RAR, ZIP, PDF, Word, Excel, PowerPoint, atau Gambar (JPG/PNG).');
                    }
                },
            ],
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $gdriveResult = \App\Services\GoogleDriveService::uploadDocumentation(
                $file,
                $validated['name'],
                $validated['start_date']
            );

            $validated['document_name'] = $file->getClientOriginalName();
            $validated['document_path'] = $gdriveResult['stored_path'];
            $validated['google_drive_folder_url'] = $gdriveResult['drive_folder_url'];
            $validated['google_drive_file_url'] = $gdriveResult['drive_file_url'];
        } else {
            $validated['google_drive_folder_url'] = \App\Services\GoogleDriveService::buildFolderUrlForSchool(
                $validated['name'],
                $validated['start_date']
            );
        }

        unset($validated['document']);
        MarketingActivity::create($validated);

        return redirect()->route('activities.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Aktivitas marketing baru dan dokumen lampiran berhasil disimpan ke sistem & disinkronkan ke Google Drive.',
            ]);
    }

    /**
     * Stream or redirect to document (Google Drive / Local Storage) securely without 404 port issues.
     */
    public function document(MarketingActivity $activity)
    {
        // 1. If uploaded to Google Drive with real drive URL, redirect directly
        if (!empty($activity->google_drive_file_url) && str_contains($activity->google_drive_file_url, 'drive.google.com')) {
            return redirect()->away($activity->google_drive_file_url);
        }

        // 2. Serve local file directly with proper headers
        if (!empty($activity->document_path) && Storage::disk('public')->exists($activity->document_path)) {
            return response()->file(Storage::disk('public')->path($activity->document_path), [
                'Content-Disposition' => 'inline; filename="' . ($activity->document_name ?: basename($activity->document_path)) . '"',
            ]);
        }

        // 3. Fallback to Google Drive Folder
        if (!empty($activity->drive_folder_url)) {
            return redirect()->away($activity->drive_folder_url);
        }

        abort(404, 'Dokumen lampiran aktivitas marketing tidak ditemukan.');
    }

    public function destroy(MarketingActivity $activity): RedirectResponse
    {
        $name = $activity->name;
        if ($activity->document_path && Storage::disk('public')->exists($activity->document_path)) {
            Storage::disk('public')->delete($activity->document_path);
        }
        $activity->delete();

        return redirect()->route('activities.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Aktivitas {$name} berhasil dihapus.",
            ]);
    }
}
