<?php

use App\Models\Campus;
use App\Models\AcademicPeriod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultCampus = Campus::where('is_default', true)->first() ?? Campus::first();
        $defaultCampusId = $defaultCampus?->id ?? 1;

        $currentPeriod = AcademicPeriod::where('is_active', true)->first() ?? AcademicPeriod::first();
        $currentPeriodId = $currentPeriod?->id ?? 1;

        // 1. Backfill follow_ups campus_id & academic_period_id from schools and school_visits
        try {
            DB::statement("
                UPDATE follow_ups f
                INNER JOIN schools s ON f.school_id = s.id
                SET f.campus_id = s.campus_id
                WHERE f.campus_id IS NULL AND s.campus_id IS NOT NULL
            ");
        } catch (\Throwable) {
            // Fallback for drivers not supporting multi-table update directly
            DB::table('follow_ups')->whereNull('campus_id')->whereNotNull('school_id')->chunkById(100, function ($records) {
                foreach ($records as $r) {
                    $schoolCampusId = DB::table('schools')->where('id', $r->school_id)->value('campus_id');
                    if ($schoolCampusId) {
                        DB::table('follow_ups')->where('id', $r->id)->update(['campus_id' => $schoolCampusId]);
                    }
                }
            });
        }

        try {
            DB::statement("
                UPDATE follow_ups f
                INNER JOIN school_visits v ON f.visit_id = v.id
                SET f.campus_id = COALESCE(f.campus_id, v.campus_id),
                    f.academic_period_id = COALESCE(f.academic_period_id, v.academic_period_id)
                WHERE (f.campus_id IS NULL AND v.campus_id IS NOT NULL)
                   OR (f.academic_period_id IS NULL AND v.academic_period_id IS NOT NULL)
            ");
        } catch (\Throwable) {
            DB::table('follow_ups')->where(function ($q) {
                $q->whereNull('campus_id')->orWhereNull('academic_period_id');
            })->whereNotNull('visit_id')->chunkById(100, function ($records) {
                foreach ($records as $r) {
                    $visit = DB::table('school_visits')->where('id', $r->visit_id)->first();
                    if ($visit) {
                        $updates = [];
                        if (!$r->campus_id && $visit->campus_id) $updates['campus_id'] = $visit->campus_id;
                        if (!$r->academic_period_id && $visit->academic_period_id) $updates['academic_period_id'] = $visit->academic_period_id;
                        if (!empty($updates)) {
                            DB::table('follow_ups')->where('id', $r->id)->update($updates);
                        }
                    }
                }
            });
        }

        // 2. Backfill prospects campus_id from schools
        try {
            DB::statement("
                UPDATE prospects p
                INNER JOIN schools s ON p.school_id = s.id
                SET p.campus_id = s.campus_id
                WHERE p.campus_id IS NULL AND s.campus_id IS NOT NULL
            ");
        } catch (\Throwable) {
            DB::table('prospects')->whereNull('campus_id')->whereNotNull('school_id')->chunkById(100, function ($records) {
                foreach ($records as $r) {
                    $sCampus = DB::table('schools')->where('id', $r->school_id)->value('campus_id');
                    if ($sCampus) {
                        DB::table('prospects')->where('id', $r->id)->update(['campus_id' => $sCampus]);
                    }
                }
            });
        }

        // 3. Backfill student_questionnaires campus_id from schools and school_event_proposals
        try {
            DB::statement("
                UPDATE student_questionnaires q
                INNER JOIN schools s ON q.school_id = s.id
                SET q.campus_id = s.campus_id
                WHERE q.campus_id IS NULL AND s.campus_id IS NOT NULL
            ");
        } catch (\Throwable) {
            DB::table('student_questionnaires')->whereNull('campus_id')->whereNotNull('school_id')->chunkById(100, function ($records) {
                foreach ($records as $r) {
                    $sCampus = DB::table('schools')->where('id', $r->school_id)->value('campus_id');
                    if ($sCampus) {
                        DB::table('student_questionnaires')->where('id', $r->id)->update(['campus_id' => $sCampus]);
                    }
                }
            });
        }

        try {
            DB::statement("
                UPDATE student_questionnaires q
                INNER JOIN school_event_proposals ep ON q.school_event_proposal_id = ep.id
                SET q.campus_id = ep.campus_id
                WHERE q.campus_id IS NULL AND ep.campus_id IS NOT NULL
            ");
        } catch (\Throwable) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reverse needed
    }
};
