<?php

namespace Database\Seeders;

use App\Models\StudentQuestionnaire;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StudentQuestionnaireDummySeeder extends Seeder
{
    private const ANSWER_KEYS = [
        'motivation_job',
        'motivation_interest',
        'motivation_parent',
        'information_social_media',
        'information_testimonials',
        'information_comparison',
        'social_parent',
        'social_friends',
        'social_teacher',
        'campus_cost',
        'campus_reputation',
        'campus_career',
        'campus_location',
        'intention_ubsi',
        'intention_information',
        'barrier_cost',
    ];

    public function run(): void
    {
        $classes = ['XII IPA 1', 'XII IPA 2', 'XII IPS 1', 'XII IPS 2', 'XII RPL 1', 'XII TKJ 1'];
        $majors = ['IPA', 'IPA', 'IPS', 'IPS', 'Rekayasa Perangkat Lunak', 'Teknik Komputer dan Jaringan'];
        $socialPlatforms = ['Instagram', 'TikTok', 'YouTube', 'Facebook'];
        $schools = \App\Models\School::limit(20)->get();

        foreach (range(1, 100) as $number) {
            $answers = [];
            foreach (self::ANSWER_KEYS as $index => $key) {
                $answers[$key] = (($number * 3 + $index * 2 + intdiv($number, 7)) % 5) + 1;
            }

            $date = Carbon::now()
                ->subDays(($number * 7) % 180)
                ->subHours(($number * 3) % 24)
                ->subMinutes(($number * 11) % 60);

            $school = $schools->isNotEmpty() ? $schools[($number - 1) % $schools->count()] : null;

            StudentQuestionnaire::updateOrCreate(
                ['student_email' => sprintf('dummy.siswa.%03d@example.invalid', $number)],
                [
                    'school_id' => $school?->id,
                    'school_name' => $school?->name ?? 'SMAN 1 Karawang',
                    'student_name' => sprintf('DUMMY Siswa %03d', $number),
                    'student_class' => $classes[($number - 1) % count($classes)],
                    'student_major' => $majors[($number - 1) % count($majors)],
                    'student_phone' => sprintf('0800000%04d', $number),
                    'student_social_media' => $socialPlatforms[$number % count($socialPlatforms)]
                        . ' @dummy_siswa_' . sprintf('%03d', $number),
                    'guardian_name' => sprintf('DUMMY Wali %03d', $number),
                    'guardian_phone' => sprintf('0800001%04d', $number),
                    'guardian_social_media' => $socialPlatforms[($number + 2) % count($socialPlatforms)]
                        . ' @dummy_wali_' . sprintf('%03d', $number),
                    'scholarship_interest' => $number % 4 === 0 ? 'tidak' : 'ya',
                    'answers' => $answers,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]
            );
        }
    }
}
