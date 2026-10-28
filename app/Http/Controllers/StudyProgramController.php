<?php

namespace App\Http\Controllers;

use App\Models\StudyProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudyProgramController extends Controller
{
    public function index(): View
    {
        $programs = StudyProgram::orderBy('name')->get();
        $availableStudyTimes = StudyProgram::getStudyTimeOptions();

        return view('study-programs.index', compact('programs', 'availableStudyTimes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20'],
            'faculty' => ['nullable', 'string', 'max:100'],
            'study_times' => ['nullable', 'array'],
            'study_times.*' => ['string', 'in:reguler_pagi,reguler_malam,karyawan'],
            'status' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama Program Studi wajib diisi.',
            'code.required' => 'Kode Program Studi wajib diisi.',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $submittedTimes = $request->input('study_times', []);

        $existing = StudyProgram::where('code', $code)
            ->orWhereRaw('LOWER(name) = ?', [strtolower(trim($validated['name']))])
            ->first();

        if ($existing) {
            $currentTimes = is_array($existing->study_times) ? $existing->study_times : [];
            $mergedTimes = array_values(array_unique(array_merge($currentTimes, $submittedTimes)));
            $existing->update([
                'study_times' => $mergedTimes,
                'faculty' => $validated['faculty'] ?? $existing->faculty,
                'status' => $validated['status'],
            ]);

            return redirect()->route('study-programs.index')
                ->with('toast', ['type' => 'success', 'message' => "Waktu kuliah untuk Program Studi {$existing->name} berhasil diperbarui."]);
        }

        $validated['code'] = $code;
        $validated['study_times'] = $submittedTimes;

        StudyProgram::create($validated);

        return redirect()->route('study-programs.index')
            ->with('toast', ['type' => 'success', 'message' => 'Program Studi dan pilihan Waktu Kuliah berhasil ditambahkan.']);
    }

    public function update(Request $request, StudyProgram $studyProgram): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:study_programs,code,' . $studyProgram->id],
            'faculty' => ['nullable', 'string', 'max:100'],
            'study_times' => ['nullable', 'array'],
            'study_times.*' => ['string', 'in:reguler_pagi,reguler_malam,karyawan'],
            'status' => ['required', 'boolean'],
        ], [
            'name.required' => 'Nama Program Studi wajib diisi.',
            'code.required' => 'Kode Program Studi wajib diisi.',
            'code.unique' => 'Kode Program Studi sudah digunakan prodi lain.',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['study_times'] = $request->input('study_times', []);

        $studyProgram->update($validated);

        return redirect()->route('study-programs.index')
            ->with('toast', ['type' => 'success', 'message' => "Program Studi {$studyProgram->name} dan Waktu Kuliah berhasil diperbarui."]);
    }

    public function destroy(Request $request, StudyProgram $studyProgram): RedirectResponse
    {
        $shiftKey = $request->input('study_time');

        if ($shiftKey && is_array($studyProgram->study_times) && in_array($shiftKey, $studyProgram->study_times, true)) {
            $remainingTimes = array_values(array_diff($studyProgram->study_times, [$shiftKey]));
            $studyProgram->update(['study_times' => $remainingTimes]);

            $options = StudyProgram::getStudyTimeOptions();
            $shiftLabel = $options[$shiftKey]['label'] ?? $shiftKey;

            return redirect()->route('study-programs.index')
                ->with('toast', ['type' => 'success', 'message' => "Waktu kuliah '{$shiftLabel}' berhasil dihapus dari prodi {$studyProgram->name}."]);
        }

        $name = $studyProgram->name;
        $studyProgram->delete();

        return redirect()->route('study-programs.index')
            ->with('toast', ['type' => 'success', 'message' => "Program Studi {$name} berhasil dihapus."]);
    }
}
