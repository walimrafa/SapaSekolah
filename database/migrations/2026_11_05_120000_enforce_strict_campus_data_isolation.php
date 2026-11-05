<?php

use App\Models\Campus;
use App\Models\District;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Enforce strict multi-campus data separation and correct all existing records
     * so that Karawang and Cikampek schools and transactions never cross-contaminate.
     */
    public function up(): void
    {
        $cikampekCampus = Campus::where('name', 'like', '%Cikampek%')->first();
        $karawangCampus = Campus::where('name', 'like', '%Karawang%')->first() ?? Campus::first();

        $cikampekId = $cikampekCampus?->id ?? 2;
        $karawangId = $karawangCampus?->id ?? 1;

        $cikampekDistricts = [
            'Cilamaya Kulon',
            'Cilamaya Wetan',
            'Purwasari',
            'Kotabaru',
            'Banyusari',
            'Tirtamulya',
            'Cikampek',
            'Jatisari',
        ];

        // 1. Assign correct campus_id to all schools according to geographical district
        $cikampekDistrictIds = District::whereIn('name', $cikampekDistricts)->pluck('id')->toArray();
        if (!empty($cikampekDistrictIds)) {
            DB::table('schools')->whereIn('district_id', $cikampekDistrictIds)->update(['campus_id' => $cikampekId]);
            DB::table('schools')->whereNotIn('district_id', $cikampekDistrictIds)->update(['campus_id' => $karawangId]);
        } else {
            // Fallback by district name join if districts table is empty or differently keyed
            try {
                DB::statement("
                    UPDATE schools s
                    INNER JOIN districts d ON s.district_id = d.id
                    SET s.campus_id = CASE 
                        WHEN d.name IN ('Cilamaya Kulon', 'Cilamaya Wetan', 'Purwasari', 'Kotabaru', 'Banyusari', 'Tirtamulya', 'Cikampek', 'Jatisari') THEN {$cikampekId}
                        ELSE {$karawangId}
                    END
                ");
            } catch (\Throwable) {}
        }

        // 2. Strict sync of school_visits campus_id with their respective school's campus
        try {
            DB::statement("
                UPDATE school_visits v
                INNER JOIN schools s ON v.school_id = s.id
                SET v.campus_id = s.campus_id
                WHERE s.campus_id IS NOT NULL
            ");
        } catch (\Throwable) {
            DB::table('school_visits')->whereNotNull('school_id')->chunkById(100, function ($records) {
                foreach ($records as $r) {
                    $sCampus = DB::table('schools')->where('id', $r->school_id)->value('campus_id');
                    if ($sCampus && $sCampus != $r->campus_id) {
                        DB::table('school_visits')->where('id', $r->id)->update(['campus_id' => $sCampus]);
                    }
                }
            });
        }

        // 3. Strict sync of follow_ups campus_id with their respective school's campus
        try {
            DB::statement("
                UPDATE follow_ups f
                INNER JOIN schools s ON f.school_id = s.id
                SET f.campus_id = s.campus_id
                WHERE s.campus_id IS NOT NULL
            ");
        } catch (\Throwable) {
            DB::table('follow_ups')->whereNotNull('school_id')->chunkById(100, function ($records) {
                foreach ($records as $r) {
                    $sCampus = DB::table('schools')->where('id', $r->school_id)->value('campus_id');
                    if ($sCampus && $sCampus != $r->campus_id) {
                        DB::table('follow_ups')->where('id', $r->id)->update(['campus_id' => $sCampus]);
                    }
                }
            });
        }

        // 4. Strict sync of student_questionnaires campus_id
        try {
            DB::statement("
                UPDATE student_questionnaires q
                INNER JOIN schools s ON q.school_id = s.id
                SET q.campus_id = s.campus_id
                WHERE s.campus_id IS NOT NULL
            ");
        } catch (\Throwable) {
            DB::table('student_questionnaires')->whereNotNull('school_id')->chunkById(100, function ($records) {
                foreach ($records as $r) {
                    $sCampus = DB::table('schools')->where('id', $r->school_id)->value('campus_id');
                    if ($sCampus && $sCampus != $r->campus_id) {
                        DB::table('student_questionnaires')->where('id', $r->id)->update(['campus_id' => $sCampus]);
                    }
                }
            });
        }

        // 5. Strict sync of school_event_proposals campus_id
        try {
            DB::statement("
                UPDATE school_event_proposals p
                INNER JOIN schools s ON p.school_id = s.id
                SET p.campus_id = s.campus_id
                WHERE s.campus_id IS NOT NULL
            ");
        } catch (\Throwable) {
            DB::table('school_event_proposals')->whereNotNull('school_id')->chunkById(100, function ($records) {
                foreach ($records as $r) {
                    $sCampus = DB::table('schools')->where('id', $r->school_id)->value('campus_id');
                    if ($sCampus && $sCampus != $r->campus_id) {
                        DB::table('school_event_proposals')->where('id', $r->id)->update(['campus_id' => $sCampus]);
                    }
                }
            });
        }

        // 6. Strict sync of visit_plans campus_id
        try {
            DB::statement("
                UPDATE visit_plans vp
                INNER JOIN schools s ON vp.school_id = s.id
                SET vp.campus_id = s.campus_id
                WHERE s.campus_id IS NOT NULL
            ");
        } catch (\Throwable) {
            DB::table('visit_plans')->whereNotNull('school_id')->chunkById(100, function ($records) {
                foreach ($records as $r) {
                    $sCampus = DB::table('schools')->where('id', $r->school_id)->value('campus_id');
                    if ($sCampus && $sCampus != $r->campus_id) {
                        DB::table('visit_plans')->where('id', $r->id)->update(['campus_id' => $sCampus]);
                    }
                }
            });
        }

        // 7. Strict sync of prospects campus_id
        try {
            DB::statement("
                UPDATE prospects pr
                INNER JOIN schools s ON pr.school_id = s.id
                SET pr.campus_id = s.campus_id
                WHERE s.campus_id IS NOT NULL AND pr.school_id IS NOT NULL
            ");
        } catch (\Throwable) {
            DB::table('prospects')->whereNotNull('school_id')->chunkById(100, function ($records) {
                foreach ($records as $r) {
                    $sCampus = DB::table('schools')->where('id', $r->school_id)->value('campus_id');
                    if ($sCampus && $sCampus != $r->campus_id) {
                        DB::table('prospects')->where('id', $r->id)->update(['campus_id' => $sCampus]);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data alignment migration; no destructive rollback needed.
    }
};

