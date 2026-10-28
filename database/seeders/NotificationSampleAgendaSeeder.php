<?php

namespace Database\Seeders;

use App\Models\FollowUp;
use App\Models\School;
use App\Models\SchoolEventProposal;
use App\Models\User;
use App\Models\VisitPlan;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class NotificationSampleAgendaSeeder extends Seeder
{
    public function run(): void
    {
        $markomUser = User::where('email', 'karawangmarkom@gmail.com')->first() 
            ?? User::first();

        $schools = School::take(5)->get();
        if ($schools->isEmpty()) {
            return;
        }

        $s1 = $schools[0];
        $s2 = $schools[1] ?? $s1;
        $s3 = $schools[2] ?? $s1;
        $s4 = $schools[3] ?? $s1;

        $tomorrow = Carbon::tomorrow();
        $h5 = Carbon::today()->addDays(5);
        $h7 = Carbon::today()->addDays(7);

        // 1. VisitPlan untuk besok (H-1)
        VisitPlan::firstOrCreate([
            'school_id' => $s1->id,
            'planned_date' => $tomorrow->toDateString(),
        ], [
            'user_id' => $markomUser?->id,
            'purpose' => 'Sosialisasi & Presentasi Program Beasiswa PMB UBSI 2026/2027',
            'priority' => 'A',
            'status' => 'planned',
            'notes' => 'Siapkan 200 brosur PMB, merchandise gantungan kunci, dan roll banner UBSI',
        ]);

        // 2. FollowUp untuk H-5 (Sosialisasi FIX)
        FollowUp::firstOrCreate([
            'school_id' => $s2->id,
            'fixed_date' => $h5->toDateString(),
        ], [
            'user_id' => $markomUser?->id,
            'followup_date' => Carbon::today(),
            'method' => 'Kunjungan Langsung',
            'description' => 'Koordinasi teknis pelaksanaan sosialisasi dengan Guru BK',
            'status' => FollowUp::STATUS_COMPLETED,
            'fixed_time' => '09:00',
            'pic_name' => 'Dra. Hj. Nurjanah (Koordinator BK)',
            'pic_phone' => '081298765432',
            'pic_position' => 'Koordinator BK',
            'socialization_location' => 'Aula Utama Lantai 2',
            'is_schedule_fixed' => true,
            'target_audience_count' => 240,
        ]);

        // 3. SchoolEventProposal (H-7 Event Pelaksanaan Disetujui)
        SchoolEventProposal::firstOrCreate([
            'reference_no' => 'PRP-EVT-' . date('Ymd') . '-01',
        ], [
            'school_id' => $s3->id,
            'school_name' => $s3->name,
            'pic_name' => 'Rahmat Hidayat, S.Pd',
            'pic_phone' => '085712345678',
            'pic_position' => 'Ketua Panitia Pentas Seni',
            'event_name' => 'Pekan Kreativitas Seni & Expo Pendidikan 2026',
            'event_date' => $h7->toDateString(),
            'event_time' => '08:00 - 14:00 WIB',
            'event_location' => 'Lapangan Utama Kampus ' . $s3->name,
            'target_participants' => '800 Siswa & Pengunjung',
            'support_type_requested' => 'Sponsorship, Tenda Booth, Merchandise',
            'estimated_budget' => 3500000,
            'approved_amount' => 3000000,
            'approved_support_items' => '1 Tenda Booth UBSI, 2 Roll Banner, Merchandise Goodie Bag',
            'proposal_file_path' => 'proposals/sample_proposal_event_2026.pdf',
            'proposal_file_name' => 'Proposal_Pensi_UBSI_2026.pdf',
            'proposal_file_size' => 1024000,
            'status' => SchoolEventProposal::STATUS_DISETUJUI,
            'execution_status' => SchoolEventProposal::EXEC_MENUNGGU_PELAKSANAAN,
        ]);

        // 4. SchoolEventProposal (H-0 Proposal Baru Masuk Menunggu Review)
        SchoolEventProposal::firstOrCreate([
            'reference_no' => 'PRP-EVT-' . date('Ymd') . '-02',
        ], [
            'school_id' => $s4->id,
            'school_name' => $s4->name,
            'pic_name' => 'Siti Aminah, M.Pd',
            'pic_phone' => '081399887766',
            'pic_position' => 'Wakasek Kesiswaan',
            'event_name' => 'Festival Sains & Teknologi Pelajar Mandiri 2026',
            'event_date' => Carbon::today()->addDays(14)->toDateString(),
            'event_time' => '08:30 - 13:00 WIB',
            'event_location' => 'Auditorium ' . $s4->name,
            'target_participants' => '500 Siswa SLTA',
            'support_type_requested' => 'Sponsorship & Pemateri Workshop AI',
            'estimated_budget' => 2500000,
            'proposal_file_path' => 'proposals/sample_proposal_festival_2026.pdf',
            'proposal_file_name' => 'Proposal_Festival_Sains_2026.pdf',
            'proposal_file_size' => 2048000,
            'status' => SchoolEventProposal::STATUS_MENUNGGU_REVIEW,
        ]);
    }
}
