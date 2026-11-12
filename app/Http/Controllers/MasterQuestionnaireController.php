<?php

namespace App\Http\Controllers;

use App\Models\QuestionnaireQuestion;
use App\Models\StudentQuestionnaire;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MasterQuestionnaireController extends Controller
{
    /**
     * Memastikan hanya Admin, Pimpinan, dan Koordinator Marketing yang dapat mengelola master kuisioner.
     */
    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->hasRole('Pimpinan') && !$user->hasRole('Koordinator Marketing'))) {
            abort(403, 'Akses terbatas. Master Kuisioner hanya dapat dikelola oleh Administrator, Pimpinan, dan Koordinator Marketing.');
        }
    }

    /**
     * Tampilan Utama Master Pertanyaan Kuisioner Siswa.
     */
    public function index(Request $request): View
    {
        $this->authorizeAccess();

        // Pastikan default data terisi jika masih kosong
        QuestionnaireQuestion::seedDefaultsIfEmpty();

        $allQuestions = QuestionnaireQuestion::query()
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $sections = $allQuestions->groupBy('section_title');
        $existingSections = QuestionnaireQuestion::query()
            ->distinct()
            ->pluck('section_title')
            ->values()
            ->all();

        $stats = [
            'total_questions' => $allQuestions->count(),
            'total_active' => $allQuestions->where('is_active', true)->count(),
            'total_inactive' => $allQuestions->where('is_active', false)->count(),
            'total_sections' => $sections->count(),
            'total_responses' => StudentQuestionnaire::count(),
        ];

        return view('master-questionnaires.index', compact(
            'sections',
            'allQuestions',
            'existingSections',
            'stats'
        ));
    }

    /**
     * Tambah Pertanyaan / Butir Kuisioner Baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'section_title' => ['required', 'string', 'max:150'],
            'new_section_title' => ['nullable', 'string', 'max:150'],
            'question_key' => ['nullable', 'string', 'max:100', 'unique:questionnaire_questions,question_key'],
            'question_text' => ['required', 'string', 'max:1000'],
            'order_index' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable'],
        ]);

        $section = !empty($validated['new_section_title']) 
            ? trim($validated['new_section_title']) 
            : trim($validated['section_title']);

        // Generate key unik jika tidak diisi manual
        $key = !empty($validated['question_key']) 
            ? Str::slug($validated['question_key'], '_') 
            : 'q_' . Str::slug(Str::limit($validated['question_text'], 20, ''), '_') . '_' . substr(uniqid(), -4);

        // Pastikan unik
        $baseKey = $key;
        $counter = 1;
        while (QuestionnaireQuestion::where('question_key', $key)->exists()) {
            $key = "{$baseKey}_{$counter}";
            $counter++;
        }

        $orderIndex = isset($validated['order_index']) && $validated['order_index'] !== ''
            ? (int) $validated['order_index']
            : (QuestionnaireQuestion::max('order_index') + 1);

        QuestionnaireQuestion::create([
            'section_title' => $section,
            'question_key' => $key,
            'question_text' => trim($validated['question_text']),
            'order_index' => $orderIndex,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('master-questionnaires.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Pertanyaan kuisioner baru berhasil ditambahkan!',
            ]);
    }

    /**
     * Update Butir Pertanyaan Kuisioner.
     */
    public function update(Request $request, QuestionnaireQuestion $questionnaireQuestion): RedirectResponse
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'section_title' => ['required', 'string', 'max:150'],
            'question_key' => ['required', 'string', 'max:100', 'unique:questionnaire_questions,question_key,' . $questionnaireQuestion->id],
            'question_text' => ['required', 'string', 'max:1000'],
            'order_index' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable'],
        ]);

        $questionnaireQuestion->update([
            'section_title' => trim($validated['section_title']),
            'question_key' => Str::slug($validated['question_key'], '_'),
            'question_text' => trim($validated['question_text']),
            'order_index' => (int) ($validated['order_index'] ?? $questionnaireQuestion->order_index),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('master-questionnaires.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Pertanyaan kuisioner berhasil diperbarui!',
            ]);
    }

    /**
     * Hapus Butir Pertanyaan Kuisioner.
     */
    public function destroy(QuestionnaireQuestion $questionnaireQuestion): RedirectResponse
    {
        $this->authorizeAccess();

        $key = $questionnaireQuestion->question_key;
        $questionnaireQuestion->delete();

        return redirect()->route('master-questionnaires.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Pertanyaan '{$key}' berhasil dihapus dari template kuisioner.",
            ]);
    }

    /**
     * Reset Seluruh Pertanyaan ke Template Default Bawaan UBSI.
     */
    public function resetDefaults(): RedirectResponse
    {
        $this->authorizeAccess();

        QuestionnaireQuestion::resetToDefaults();

        return redirect()->route('master-questionnaires.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Seluruh pertanyaan kuisioner telah berhasil di-reset ke template standar UBSI!',
            ]);
    }
}

