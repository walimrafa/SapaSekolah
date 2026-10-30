<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\Campus;
use App\Models\Prospect;
use App\Models\School;
use App\Models\StudentQuestionnaire;
use App\Models\StudyProgram;
use Illuminate\Support\Facades\Log;

class ProspectSyncService
{
    /**
     * Synchronize a student questionnaire with the prospects CRM pipeline.
     */
    public static function syncStudentQuestionnaire(StudentQuestionnaire $student, array $extra = []): ?Prospect
    {
        $status = $student->followup_status;
        $isDaftarAplikasi = in_array($status, [
            StudentQuestionnaire::STATUS_DAFTAR_APLIKASI,
            'closing',
            'daftar_aplikasi',
        ]);

        // Check if student has reached daftar_aplikasi status
        if (!$isDaftarAplikasi) {
            return null;
        }

        // 1. Resolve Campus ID
        $campusId = $student->campus_id
            ?: ($student->school?->campus_id
                ?: (AppContextService::getActiveCampus()?->id
                    ?: Campus::getDefault()?->id));

        // 2. Resolve Academic Period ID
        $periodId = $student->academic_period_id
            ?: (AppContextService::getActivePeriod()?->id
                ?: AcademicPeriod::getCurrent()?->id);

        // 3. Resolve School ID
        $schoolId = $student->school_id
            ?: ($student->school?->id
                ?: School::first()?->id);

        // 4. Resolve Interest Program & Study Time
        $interestProgram = $extra['interest_program']
            ?? $student->college_interest_notes
            ?? 'S1 Sistem Informasi';

        $studyTime = null;
        if ($interestProgram) {
            if (str_contains($interestProgram, ' - ')) {
                $parts = explode(' - ', $interestProgram);
                $potentialName = trim($parts[0]);
                $matchedProdi = StudyProgram::where('name', $potentialName)
                    ->orWhere('code', $potentialName)
                    ->first();
                if ($matchedProdi) {
                    $interestProgram = $matchedProdi->name;
                    $lowerSuffix = strtolower($interestProgram . ' ' . ($student->college_interest_notes ?? ''));
                    if (str_contains($lowerSuffix, 'karyawan') || str_contains($lowerSuffix, 'sabtu')) {
                        $studyTime = StudyProgram::TIME_KARYAWAN;
                    } elseif (str_contains($lowerSuffix, 'malam') || str_contains($lowerSuffix, 'sore')) {
                        $studyTime = StudyProgram::TIME_REGULER_MALAM;
                    } else {
                        $studyTime = StudyProgram::TIME_REGULER_PAGI;
                    }
                }
            } else {
                $matchedProdi = StudyProgram::where('name', $interestProgram)->first();
                if ($matchedProdi && !empty($matchedProdi->study_times)) {
                    $studyTime = $matchedProdi->study_times[0];
                }
            }
        }

        // 5. Resolve Scholarship Type
        $scholarshipType = $extra['scholarship_type']
            ?? $student->scholarship_notes;
        if (!$scholarshipType) {
            $scholarshipType = ($student->scholarship_interest === 'ya')
                ? 'Beasiswa KIP-Kuliah'
                : 'Non Beasiswa (Reguler Mandiri)';
        }

        // 6. Notes Compilation
        $noteParts = [];
        if (!empty($extra['notes'])) {
            $noteParts[] = "Catatan Follow Up: " . $extra['notes'];
        } elseif (!empty($student->followup_notes)) {
            $noteParts[] = "Catatan Follow Up: " . $student->followup_notes;
        }
        if (!empty($student->college_interest_notes)) {
            $noteParts[] = "Minat Prodi: " . $student->college_interest_notes;
        }
        if (!empty($student->scholarship_notes)) {
            $noteParts[] = "Minat Beasiswa: " . $student->scholarship_notes;
        }
        if ($student->guardian_name || $student->guardian_phone) {
            $noteParts[] = "Wali/Ortu: " . ($student->guardian_name ?? '-') . " (WA: " . ($student->guardian_phone ?? '-') . ")";
        }
        $noteText = implode(" | ", array_unique(array_filter($noteParts)));

        // 7. Source
        $prospectSource = ($student->source === 'support_event' || $student->school_event_proposal_id)
            ? 'Support Event Sekolah (Daftar Aplikasi)'
            : 'Kuisioner Siswa (Daftar Aplikasi)';

        // 8. Find existing Prospect by Phone, Email, or Name + School
        $prospect = null;
        if (!empty($student->student_phone)) {
            $prospect = Prospect::where('phone', $student->student_phone)->first();
        }
        if (!$prospect && !empty($student->student_email)) {
            $prospect = Prospect::where('email', $student->student_email)->first();
        }
        if (!$prospect && !empty($student->student_name)) {
            $prospect = Prospect::where('name', $student->student_name)
                ->when($schoolId, fn($q) => $q->where('school_id', $schoolId))
                ->first();
        }

        $marketingUserId = $student->assigned_to
            ?: ($student->last_followed_up_by ?: auth()->id());

        $prospectData = [
            'name' => $student->student_name,
            'phone' => $student->student_phone,
            'email' => $student->student_email ?: ($prospect?->email ?? null),
            'school_id' => $schoolId,
            'campus_id' => $campusId,
            'academic_period_id' => $periodId,
            'graduation_year' => (int) date('Y'),
            'interest_program' => $interestProgram ?: ($prospect?->interest_program ?? 'S1 Sistem Informasi'),
            'study_time' => $studyTime ?: ($prospect?->study_time ?? StudyProgram::TIME_REGULER_PAGI),
            'scholarship_type' => $scholarshipType ?: ($prospect?->scholarship_type ?? 'Non Beasiswa (Reguler Mandiri)'),
            'source' => $prospectSource,
            'status' => $prospect ? ($prospect->status === Prospect::STATUS_MAHASISWA ? Prospect::STATUS_MAHASISWA : ($prospect->status ?: Prospect::STATUS_DAFTAR_AKUN)) : Prospect::STATUS_DAFTAR_AKUN,
            'marketing_user_id' => $marketingUserId ?: ($prospect?->marketing_user_id ?? 1),
            'notes' => $noteText ?: ($prospect?->notes ?? 'Calon Mahasiswa Baru telah melakukan pendaftaran aplikasi.'),
        ];

        if ($prospect) {
            $prospect->update($prospectData);
        } else {
            $prospect = Prospect::create($prospectData);
        }

        return $prospect;
    }

    /**
     * Sync all existing student questionnaires with status daftar_aplikasi.
     */
    public static function syncAllDaftarAplikasi(): int
    {
        $students = StudentQuestionnaire::whereIn('followup_status', [
            StudentQuestionnaire::STATUS_DAFTAR_APLIKASI,
            'closing',
            'daftar_aplikasi',
        ])->get();

        $count = 0;
        foreach ($students as $student) {
            self::syncStudentQuestionnaire($student);
            $count++;
        }

        return $count;
    }
}

