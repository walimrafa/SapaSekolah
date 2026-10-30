<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Campus;
use App\Models\FollowUp;
use App\Models\Prospect;
use App\Models\School;
use App\Models\SchoolEventProposal;
use App\Models\SchoolVisit;
use App\Models\StudentQuestionnaire;
use App\Services\ActivityLogService;
use App\Services\AppContextService;
use App\Services\AppSettingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComprehensiveReportController extends Controller
{
    /**
     * Display Comprehensive Report Hub UI.
     */
    public function index(Request $request): View
    {
        $currentUser = auth()->user();
        if ($currentUser && !$currentUser->isAdmin()) {
            $campusId = $currentUser->campus_id ?? AppContextService::getActiveCampus()?->id;
            $campuses = Campus::where('id', $campusId)->get();
        } else {
            $campuses = Campus::where('is_active', true)->orderBy('is_default', 'desc')->get();
            $campusId = $request->get('campus_id', AppContextService::getActiveCampus()?->id);
        }

        $periods = AcademicPeriod::orderBy('start_date', 'desc')->get();

        $selectedCategory = $request->get('category', 'all');
        $dateMode = $request->get('date_mode', 'all'); // 'all' (Dari awal sampai akhir) atau 'custom'
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $periodId = $request->get('academic_period_id', AppContextService::getActivePeriod()?->id);

        // Hitung ringkasan data yang masuk berdasarkan filter terpilih
        $counts = [
            'visits' => $this->filterQuery(SchoolVisit::query(), $dateMode, $startDate, $endDate, $campusId, $periodId, 'visit_date')->count(),
            'socializations' => $this->filterQuery(FollowUp::whereNotNull('socialization_completed_at'), $dateMode, $startDate, $endDate, $campusId, $periodId, 'socialization_completed_at')->count(),
            'questionnaires' => $this->filterQuery(StudentQuestionnaire::query(), $dateMode, $startDate, $endDate, $campusId, $periodId, 'created_at')->count(),
            'followups' => $this->filterQuery(StudentQuestionnaire::where('followup_status', '!=', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI), $dateMode, $startDate, $endDate, $campusId, $periodId, 'last_followup_at')->count(),
            'prospects' => $this->filterQuery(Prospect::query(), $dateMode, $startDate, $endDate, $campusId, $periodId, 'created_at')->count(),
            'new_students' => $this->filterQuery(Prospect::where('status', Prospect::STATUS_MAHASISWA), $dateMode, $startDate, $endDate, $campusId, $periodId, 'validated_at')->count(),
            'events' => $this->filterQuery(SchoolEventProposal::query(), $dateMode, $startDate, $endDate, $campusId, $periodId, 'event_date')->count(),
            'schools' => School::when($campusId && $campusId !== 'ALL', fn($q) => $q->where('campus_id', $campusId))->count(),
        ];

        return view('reports.comprehensive', compact(
            'campuses',
            'periods',
            'selectedCategory',
            'dateMode',
            'startDate',
            'endDate',
            'campusId',
            'periodId',
            'counts'
        ));
    }

    /**
     * Download comprehensive reports as formatted Excel (.xlsx).
     */
    public function download(Request $request): StreamedResponse
    {
        $currentUser = auth()->user();
        $category = $request->get('category', 'all');
        $dateMode = $request->get('date_mode', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($currentUser && !$currentUser->isAdmin()) {
            $campusId = $currentUser->campus_id ?? AppContextService::getActiveCampus()?->id;
        } else {
            $campusId = $request->get('campus_id');
        }

        $periodId = $request->get('academic_period_id');

        $activeCampus = ($campusId && $campusId !== 'ALL') ? Campus::find($campusId) : null;
        $activePeriod = ($periodId && $periodId !== 'ALL') ? AcademicPeriod::find($periodId) : null;

        $campusName = $activeCampus ? $activeCampus->name : 'Seluruh Kampus';
        $periodName = $activePeriod ? $activePeriod->name : 'Seluruh Periode';
        $rangeStr = ($dateMode === 'custom' && $startDate && $endDate) 
            ? "{$startDate} s/d {$endDate}" 
            : 'Dari Awal s/d Akhir Data Masuk';

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator(auth()->user()->name ?? 'SAPA Sekolah')
            ->setTitle("Laporan Khusus SAPA Sekolah - {$category}");

        if ($category === 'all') {
            // Multi-sheet workbook
            $this->buildSummarySheet($spreadsheet->getActiveSheet(), $campusName, $periodName, $rangeStr, $dateMode, $startDate, $endDate, $campusId, $periodId);
            $this->buildVisitsSheet($spreadsheet->createSheet(), $dateMode, $startDate, $endDate, $campusId, $periodId);
            $this->buildSocializationsSheet($spreadsheet->createSheet(), $dateMode, $startDate, $endDate, $campusId, $periodId);
            $this->buildQuestionnairesSheet($spreadsheet->createSheet(), $dateMode, $startDate, $endDate, $campusId, $periodId);
            $this->buildProspectsSheet($spreadsheet->createSheet(), $dateMode, $startDate, $endDate, $campusId, $periodId);
            $this->buildEventsSheet($spreadsheet->createSheet(), $dateMode, $startDate, $endDate, $campusId, $periodId);
            $this->buildSchoolsSheet($spreadsheet->createSheet(), $campusId);
            $spreadsheet->setActiveSheetIndex(0);
        } else {
            $sheet = $spreadsheet->getActiveSheet();
            match($category) {
                'visits' => $this->buildVisitsSheet($sheet, $dateMode, $startDate, $endDate, $campusId, $periodId),
                'socializations' => $this->buildSocializationsSheet($sheet, $dateMode, $startDate, $endDate, $campusId, $periodId),
                'questionnaires' => $this->buildQuestionnairesSheet($sheet, $dateMode, $startDate, $endDate, $campusId, $periodId),
                'prospects' => $this->buildProspectsSheet($sheet, $dateMode, $startDate, $endDate, $campusId, $periodId),
                'events' => $this->buildEventsSheet($sheet, $dateMode, $startDate, $endDate, $campusId, $periodId),
                'schools' => $this->buildSchoolsSheet($sheet, $campusId),
                default => $this->buildVisitsSheet($sheet, $dateMode, $startDate, $endDate, $campusId, $periodId),
            };
        }

        ActivityLogService::log(
            'EXPORT_REPORT',
            'report',
            null,
            "Mengunduh Laporan Khusus ({$category}) - {$campusName} - Periode: {$periodName} ({$rangeStr})"
        );

        $safeName = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '_', "Laporan_SAPA_{$category}_{$campusName}_{$periodName}_" . date('Ymd_His')));
        $filename = "{$safeName}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function filterQuery($query, $dateMode, $startDate, $endDate, $campusId, $periodId, string $dateColumn = 'created_at')
    {
        if ($campusId && $campusId !== 'ALL') {
            $query->where(function($q) use ($campusId) {
                $q->where('campus_id', $campusId)
                  ->orWhereNull('campus_id');
            });
        }

        if ($periodId && $periodId !== 'ALL') {
            $query->where('academic_period_id', $periodId);
        }

        if ($dateMode === 'custom' && !empty($startDate) && !empty($endDate)) {
            $query->whereBetween($dateColumn, [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }

        return $query;
    }

    private function applyHeaderStyling($sheet, array $headers, string $title, array $meta = [])
    {
        $appSettings = AppSettingService::all();
        $sheet->setCellValue('A1', strtoupper($appSettings['app_name'] ?? 'SAPA SEKOLAH') . ' — ' . strtoupper($title));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0B023E'));

        $row = 2;
        foreach ($meta as $label => $val) {
            $sheet->setCellValue("A{$row}", "{$label}: {$val}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(false)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF475569'));
            $row++;
        }
        $row++; // blank space

        $tableHeaderRow = $row;
        foreach ($headers as $idx => $hdr) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            $sheet->setCellValue("{$colLetter}{$tableHeaderRow}", $hdr);
        }

        $highestCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A{$tableHeaderRow}:{$highestCol}{$tableHeaderRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle("A{$tableHeaderRow}:{$highestCol}{$tableHeaderRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0B023E'); // Brand Navy

        return $tableHeaderRow + 1;
    }

    private function buildSummarySheet($sheet, $campusName, $periodName, $rangeStr, $dateMode, $startDate, $endDate, $campusId, $periodId)
    {
        $sheet->setTitle('Ringkasan Eksekutif');
        $headers = ['No', 'Indikator Operasional Marketing PMB', 'Total Data Masuk', 'Keterangan'];
        $row = $this->applyHeaderStyling($sheet, $headers, 'RINGKASAN REKAPITULASI DATA OPERASIONAL', [
            'Kampus' => $campusName,
            'Periode Akademik' => $periodName,
            'Rentang Data' => $rangeStr,
            'Tanggal Cetak' => date('d/m/Y H:i:s') . ' WIB oleh ' . (auth()->user()->name ?? 'System'),
        ]);

        $rows = [
            [1, 'Total Target Sekolah Terdaftar', School::count(), 'Data sekolah SLTA terintegrasi'],
            [2, 'Total Kunjungan Lapangan Terlaksana', $this->filterQuery(SchoolVisit::query(), $dateMode, $startDate, $endDate, $campusId, $periodId, 'visit_date')->count(), 'Kunjungan terverifikasi GPS 500m'],
            [3, 'Total Sosialisasi Terlaksana (FIX)', $this->filterQuery(FollowUp::whereNotNull('socialization_completed_at'), $dateMode, $startDate, $endDate, $campusId, $periodId, 'socialization_completed_at')->count(), 'Presentasi PMB di sekolah'],
            [4, 'Total Responden Kuisioner Siswa', $this->filterQuery(StudentQuestionnaire::query(), $dateMode, $startDate, $endDate, $campusId, $periodId, 'created_at')->count(), 'Minat kuliah & beasiswa'],
            [5, 'Total Siswa Sudah Di-Follow Up', $this->filterQuery(StudentQuestionnaire::where('followup_status', '!=', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI), $dateMode, $startDate, $endDate, $campusId, $periodId, 'last_followup_at')->count(), 'Interaksi telepon & WhatsApp'],
            [6, 'Total Prospek Calon Mahasiswa (CRM)', $this->filterQuery(Prospect::query(), $dateMode, $startDate, $endDate, $campusId, $periodId, 'created_at')->count(), 'Pipeline tahap 1 s/d 5'],
            [7, 'Total Mahasiswa Baru Resmi (Validasi NIM)', $this->filterQuery(Prospect::where('status', Prospect::STATUS_MAHASISWA), $dateMode, $startDate, $endDate, $campusId, $periodId, 'validated_at')->count(), 'Sudah memiliki NIM resmi'],
            [8, 'Total Proposal Event Sekolah Masuk', $this->filterQuery(SchoolEventProposal::query(), $dateMode, $startDate, $endDate, $campusId, $periodId, 'event_date')->count(), 'Pengajuan sponsorship sekolah'],
        ];

        foreach ($rows as $r) {
            $sheet->setCellValue("A{$row}", $r[0]);
            $sheet->setCellValue("B{$row}", $r[1]);
            $sheet->setCellValue("C{$row}", $r[2]);
            $sheet->setCellValue("D{$row}", $r[3]);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->getFont()->setBold(true);
            $row++;
        }

        foreach (range(1, 4) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
    }

    private function buildVisitsSheet($sheet, $dateMode, $startDate, $endDate, $campusId, $periodId)
    {
        $sheet->setTitle('Kunjungan Sekolah');
        $headers = [
            'No', 'Tanggal Kunjungan', 'Nama Sekolah', 'NPSN', 'Kecamatan', 'Petugas Bertugas',
            'Kepala Sekolah', 'Nama Penerima (PIC)', 'Jabatan', 'No. WhatsApp', 'Jml Kls 12',
            'Target Waktu', 'Metode Sosialisasi', 'Status Kunjungan', 'Link Google Drive', 'Catatan Kunjungan'
        ];

        $startRow = $this->applyHeaderStyling($sheet, $headers, 'LAPORAN REKAPITULASI KUNJUNGAN SEKOLAH');
        $query = $this->filterQuery(SchoolVisit::with(['school.district', 'user']), $dateMode, $startDate, $endDate, $campusId, $periodId, 'visit_date');
        $visits = $query->orderBy('visit_date', 'desc')->get();

        $row = $startRow;
        foreach ($visits as $idx => $v) {
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $v->visit_date ? $v->visit_date->format('Y-m-d') : '-');
            $sheet->setCellValue("C{$row}", $v->school?->name ?? '-');
            $sheet->setCellValueExplicit("D{$row}", $v->school?->npsn ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("E{$row}", $v->school?->district?->name ?? '-');
            $sheet->setCellValue("F{$row}", $v->user?->name ?? '-');
            $sheet->setCellValue("G{$row}", $v->headmaster_name ?? ($v->school?->headmaster_name ?? '-'));
            $sheet->setCellValue("H{$row}", $v->recipient_name ?? '-');
            $sheet->setCellValue("I{$row}", $v->recipient_position ?? '-');
            $sheet->setCellValueExplicit("J{$row}", $v->recipient_phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("K{$row}", $v->grade_12_class_count ?? '-');
            $sheet->setCellValue("L{$row}", ($v->target_date ? $v->target_date->format('Y-m-d') : $v->target_month_year) . ' ' . $v->target_time);
            $sheet->setCellValue("M{$row}", $v->socialization_method ?? '-');
            $sheet->setCellValue("N{$row}", strtoupper($v->status));
            $sheet->setCellValue("O{$row}", $v->drive_folder_url ?? '-');
            $sheet->setCellValue("P{$row}", $v->notes ?? '-');
            $row++;
        }

        foreach (range(1, count($headers)) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
    }

    private function buildSocializationsSheet($sheet, $dateMode, $startDate, $endDate, $campusId, $periodId)
    {
        $sheet->setTitle('Sosialisasi FIX');
        $headers = [
            'No', 'Tanggal Sosialisasi', 'Nama Sekolah', 'Kecamatan', 'Petugas Bertanggung Jawab',
            'PIC Sekolah', 'No. WhatsApp PIC', 'Jumlah Peserta / Audiens', 'Tempat Kegiatan', 'Link Google Drive', 'Catatan Pelaksanaan'
        ];

        $startRow = $this->applyHeaderStyling($sheet, $headers, 'LAPORAN SOSIALISASI PMB & BEASISWA TERLAKSANA');
        $query = $this->filterQuery(FollowUp::with(['school.district', 'user'])->whereNotNull('socialization_completed_at'), $dateMode, $startDate, $endDate, $campusId, $periodId, 'socialization_completed_at');
        $records = $query->orderByDesc('socialization_completed_at')->get();

        $row = $startRow;
        foreach ($records as $idx => $f) {
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $f->socialization_completed_at ? Carbon::parse($f->socialization_completed_at)->format('Y-m-d') : '-');
            $sheet->setCellValue("C{$row}", $f->school?->name ?? '-');
            $sheet->setCellValue("E{$row}", $f->school?->district?->name ?? '-');
            $sheet->setCellValue("E{$row}", $f->user?->name ?? '-');
            $sheet->setCellValue("F{$row}", $f->pic_name ?? '-');
            $sheet->setCellValueExplicit("G{$row}", $f->pic_phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("H{$row}", $f->target_audience_count ?? '-');
            $sheet->setCellValue("I{$row}", $f->socialization_location ?? '-');
            $sheet->setCellValue("J{$row}", $f->drive_folder_url ?? '-');
            $sheet->setCellValue("K{$row}", $f->socialization_completion_notes ?? '-');
            $row++;
        }

        foreach (range(1, count($headers)) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
    }

    private function buildQuestionnairesSheet($sheet, $dateMode, $startDate, $endDate, $campusId, $periodId)
    {
        $sheet->setTitle('Kuisioner & Follow Up Siswa');
        $headers = [
            'No', 'Waktu Pengisian', 'Nama Siswa', 'Asal Sekolah', 'Kelas', 'Jurusan', 'No. WhatsApp Siswa',
            'Email Siswa', 'Nama Orang Tua / Wali', 'No. WhatsApp Wali', 'Minat Beasiswa', 'Status Follow Up',
            'Petugas Ditugaskan', 'Waktu Follow Up Terakhir', 'Catatan Follow Up', 'Sumber Data'
        ];

        $startRow = $this->applyHeaderStyling($sheet, $headers, 'LAPORAN RESIDEN KUISIONER & PROSES FOLLOW UP SISWA');
        $query = $this->filterQuery(StudentQuestionnaire::with(['school.district', 'assignedUser', 'lastFollowedUpBy']), $dateMode, $startDate, $endDate, $campusId, $periodId, 'created_at');
        $students = $query->orderBy('created_at', 'desc')->get();

        $row = $startRow;
        foreach ($students as $idx => $s) {
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $s->created_at ? $s->created_at->format('Y-m-d H:i') : '-');
            $sheet->setCellValue("C{$row}", $s->student_name);
            $sheet->setCellValue("D{$row}", $s->school_display_name);
            $sheet->setCellValue("E{$row}", $s->student_class ?? '-');
            $sheet->setCellValue("F{$row}", $s->student_major ?? '-');
            $sheet->setCellValueExplicit("G{$row}", $s->student_phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("H{$row}", $s->student_email ?? '-');
            $sheet->setCellValue("I{$row}", $s->guardian_name ?? '-');
            $sheet->setCellValueExplicit("J{$row}", $s->guardian_phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("K{$row}", strtoupper($s->scholarship_interest));
            $sheet->setCellValue("L{$row}", $s->followup_status_label);
            $sheet->setCellValue("M{$row}", $s->assignedUser?->name ?? 'Belum Di-ploting');
            $sheet->setCellValue("N{$row}", $s->last_followup_at ? $s->last_followup_at->format('Y-m-d H:i') : '-');
            $sheet->setCellValue("O{$row}", $s->followup_notes ?? '-');
            $sheet->setCellValue("P{$row}", $s->source ?? 'kuisioner');
            $row++;
        }

        foreach (range(1, count($headers)) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
    }

    private function buildProspectsSheet($sheet, $dateMode, $startDate, $endDate, $campusId, $periodId)
    {
        $sheet->setTitle('Prospek & Mahasiswa Baru');
        $headers = [
            'No', 'Status Pipeline', 'NIM Resmi', 'Nama Lengkap', 'Jenis Kelamin', 'Asal Sekolah',
            'No. WhatsApp', 'Email', 'Tahun Lulus', 'Program Studi Minat', 'Waktu Kuliah', 'Jalur Beasiswa',
            'Marketing PIC', 'Tanggal Tervalidasi', 'Sumber Lead', 'Catatan CRM'
        ];

        $startRow = $this->applyHeaderStyling($sheet, $headers, 'LAPORAN DATA PROSPEK & MAHASISWA BARU (CRM PIPELINE)');
        $query = $this->filterQuery(Prospect::with(['school', 'marketingUser']), $dateMode, $startDate, $endDate, $campusId, $periodId, 'created_at');
        $prospects = $query->orderBy('created_at', 'desc')->get();

        $row = $startRow;
        foreach ($prospects as $idx => $p) {
            $statusLabels = Prospect::getStatusLabels();
            $statusLabel = $statusLabels[$p->status] ?? $p->status;

            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $statusLabel);
            $sheet->setCellValueExplicit("C{$row}", $p->nim ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("D{$row}", $p->name);
            $sheet->setCellValue("E{$row}", $p->gender === 'L' ? 'Laki-laki' : ($p->gender === 'P' ? 'Perempuan' : '-'));
            $sheet->setCellValue("F{$row}", $p->school?->name ?? '-');
            $sheet->setCellValueExplicit("G{$row}", $p->phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("H{$row}", $p->email ?? '-');
            $sheet->setCellValue("I{$row}", $p->graduation_year ?? '-');
            $sheet->setCellValue("J{$row}", $p->interest_program ?? '-');
            $sheet->setCellValue("K{$row}", $p->study_time ?? '-');
            $sheet->setCellValue("L{$row}", $p->scholarship_type ?? '-');
            $sheet->setCellValue("M{$row}", $p->marketingUser?->name ?? '-');
            $sheet->setCellValue("N{$row}", $p->validated_at ? Carbon::parse($p->validated_at)->format('Y-m-d') : '-');
            $sheet->setCellValue("O{$row}", $p->source ?? '-');
            $sheet->setCellValue("P{$row}", $p->notes ?? '-');
            $row++;
        }

        foreach (range(1, count($headers)) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
    }

    private function buildEventsSheet($sheet, $dateMode, $startDate, $endDate, $campusId, $periodId)
    {
        $sheet->setTitle('Support Event Sekolah');
        $headers = [
            'No', 'No. Referensi', 'Nama Event', 'Sekolah Pengaju', 'PIC Narahubung', 'No. WhatsApp PIC',
            'Tanggal Event', 'Lokasi', 'Estimasi Peserta', 'Anggaran Disetujui (Rp)', 'Status Proposal', 'Status Pelaksanaan'
        ];

        $startRow = $this->applyHeaderStyling($sheet, $headers, 'LAPORAN PROPOSAL DUKUNGAN & EVENT SEKOLAH');
        $query = $this->filterQuery(SchoolEventProposal::query(), $dateMode, $startDate, $endDate, $campusId, $periodId, 'event_date');
        $events = $query->orderBy('event_date', 'desc')->get();

        $row = $startRow;
        foreach ($events as $idx => $e) {
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $e->reference_no);
            $sheet->setCellValue("C{$row}", $e->event_name);
            $sheet->setCellValue("D{$row}", $e->school_name);
            $sheet->setCellValue("E{$row}", $e->pic_name);
            $sheet->setCellValueExplicit("F{$row}", $e->pic_phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("G{$row}", $e->event_date ? $e->event_date->format('Y-m-d') : '-');
            $sheet->setCellValue("H{$row}", $e->event_location);
            $sheet->setCellValue("I{$row}", $e->target_participants);
            $sheet->setCellValue("J{$row}", (float)($e->approved_amount ?? 0));
            $sheet->setCellValue("K{$row}", $e->status_label);
            $sheet->setCellValue("L{$row}", $e->execution_status_label);
            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        foreach (range(1, count($headers)) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
    }

    private function buildSchoolsSheet($sheet, $campusId)
    {
        $sheet->setTitle('Master Data Sekolah');
        $headers = [
            'No', 'NPSN', 'Nama Sekolah', 'Jenjang', 'Status', 'Akreditasi', 'Kecamatan', 'Kabupaten/Kota',
            'Kepala Sekolah', 'Jml Siswa Kls 12', 'Jarak dari Kampus (km)', 'Skor Potensi', 'Prioritas', 'Status Sekolah'
        ];

        $startRow = $this->applyHeaderStyling($sheet, $headers, 'LAPORAN MASTER DATA SEKOLAH & SKOR PRIORITAS');
        $query = School::with(['district', 'regency', 'schoolType', 'accreditation']);
        if ($campusId && $campusId !== 'ALL') {
            $query->where('campus_id', $campusId);
        }
        $schools = $query->orderBy('name')->get();

        $row = $startRow;
        foreach ($schools as $idx => $s) {
            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValueExplicit("B{$row}", $s->npsn ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("C{$row}", $s->name);
            $sheet->setCellValue("D{$row}", $s->schoolType?->name ?? '-');
            $sheet->setCellValue("E{$row}", $s->status ?? '-');
            $sheet->setCellValue("F{$row}", $s->accreditation?->name ?? '-');
            $sheet->setCellValue("G{$row}", $s->district?->name ?? '-');
            $sheet->setCellValue("H{$row}", $s->regency?->name ?? '-');
            $sheet->setCellValue("I{$row}", $s->headmaster_name ?? '-');
            $sheet->setCellValue("J{$row}", $s->grade_12_count ?? 0);
            $sheet->setCellValue("K{$row}", $s->distance_from_campus ?? 0);
            $sheet->setCellValue("L{$row}", $s->potential_score ?? 0);
            $sheet->setCellValue("M{$row}", $s->priority_level ?? '-');
            $sheet->setCellValue("N{$row}", strtoupper($s->status_school ?? 'aktif'));
            $row++;
        }

        foreach (range(1, count($headers)) as $c) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
    }
}

