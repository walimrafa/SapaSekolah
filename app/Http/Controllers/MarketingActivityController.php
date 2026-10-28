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
        $query = MarketingActivity::withCount('schoolActivities');

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
            $validated['document_name'] = $file->getClientOriginalName();
            $validated['document_path'] = $file->store('activity-documents', 'public');
        }

        unset($validated['document']);
        MarketingActivity::create($validated);

        return redirect()->route('activities.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Aktivitas marketing baru dan dokumen lampiran berhasil disimpan.',
            ]);
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
