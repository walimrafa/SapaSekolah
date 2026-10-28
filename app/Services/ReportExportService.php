<?php

namespace App\Services;

use App\Models\District;
use App\Models\FollowUp;
use App\Models\MarketingActivity;
use App\Models\Prospect;
use App\Models\School;
use App\Models\SchoolVisit;
use App\Models\StudentQuestionnaire;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Export report data to Excel (.xlsx) based on active tab and filters.
     */
    public function export(Request $request): StreamedResponse
    {
        $tab = $request->get('tab', 'overview');
        $spreadsheet = new Spreadsheet();
        
        $campusName = AppSettingService::get('campus_name', 'Universitas Bina Sarana Informatika (UBSI) Kampus Karawang');
        $exportDate = Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB';

        switch ($tab) {
            case 'visits':
                $this->buildVisitsSheet($spreadsheet, $request, $campusName, $exportDate);
                $filename = 'Laporan_Progres_Kunjungan_Sekolah_' . date('Ymd_His') . '.xlsx';
                break;

            case 'followups':
                $this->buildFollowupsSheet($spreadsheet, $request, $campusName, $exportDate);
                $filename = 'Laporan_Progres_Follow_Up_Sekolah_' . date('Ymd_His') . '.xlsx';
                break;

            case 'activities':
                $this->buildActivitiesSheet($spreadsheet, $request, $campusName, $exportDate);
                $filename = 'Laporan_Progres_Aktivitas_Marketing_' . date('Ymd_His') . '.xlsx';
                break;

            case 'prospects':
                $this->buildProspectsSheet($spreadsheet, $request, $campusName, $exportDate);
                $filename = 'Laporan_Progres_Prospek_Siswa_' . date('Ymd_His') . '.xlsx';
                break;

            case 'questionnaires':
                $this->buildQuestionnairesSheet($spreadsheet, $request, $campusName, $exportDate);
                $filename = 'Laporan_Progres_Kuisioner_Siswa_' . date('Ymd_His') . '.xlsx';
                break;

            case 'schools':
                $this->buildSchoolsSheet($spreadsheet, $request, $campusName, $exportDate);
                $filename = 'Laporan_Progres_Pemetaan_Sekolah_' . date('Ymd_His') . '.xlsx';
                break;

            case 'overview':
            default:
                $this->buildOverviewSheet($spreadsheet, $request, $campusName, $exportDate);
                $filename = 'Laporan_Keseluruhan_Marketing_UBSI_' . date('Ymd_His') . '.xlsx';
                break;
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * SHEET: Laporan Keseluruhan (Ringkasan Eksekutif & Analisis)
     */
    protected function buildOverviewSheet(Spreadsheet $spreadsheet, Request $request, string $campusName, string $exportDate): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Keseluruhan');

        // Header Title
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI KESELURUHAN MARKETING & PROGRES');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setSize(15)->setBold(true)->getColor()->setRGB('1E3A5F');

        $sheet->setCellValue('A2', strtoupper($campusName) . ' • SAPA SEKOLAH COMMAND CENTER');
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('64748B');

        $sheet->setCellValue('A3', 'Dicetak pada: ' . $exportDate);
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('94A3B8');

        // Ringkasan KPI Global
        $totalSchools = School::count();
        $visitedSchools = School::has('visits')->count();
        $totalVisits = SchoolVisit::count();
        $totalFollowUps = FollowUp::count();
        $totalProspects = Prospect::count();
        $totalMahasiswa = Prospect::where('status', 'mahasiswa')->count();
        $conversionRate = $totalProspects > 0 ? round(($totalMahasiswa / $totalProspects) * 100, 1) : 0;

        $sheet->setCellValue('A5', 'RINGKASAN KPI UTAMA');
        $sheet->mergeCells('A5:D5');
        $sheet->getStyle('A5')->getFont()->setBold(true)->getColor()->setRGB('1E3A5F');

        $kpis = [
            ['Total Sekolah Terdata', $totalSchools, 'Total Kunjungan Lapangan', $totalVisits],
            ['Sekolah Sudah Dikunjungi', $visitedSchools . ' (' . ($totalSchools > 0 ? round(($visitedSchools / $totalSchools) * 100, 1) : 0) . '%)', 'Total Follow Up Sekolah', $totalFollowUps],
            ['Total Prospek Siswa', $totalProspects, 'Mahasiswa Fix Baru (Closing)', $totalMahasiswa],
            ['Rasio Konversi PMB', $conversionRate . '%', 'Total Siswa Kls XII Terdata', number_format(School::sum('grade_12_count'))],
        ];

        $kpiRow = 6;
        foreach ($kpis as $row) {
            $sheet->setCellValue("A{$kpiRow}", $row[0]);
            $sheet->setCellValue("B{$kpiRow}", $row[1]);
            $sheet->setCellValue("C{$kpiRow}", $row[2]);
            $sheet->setCellValue("D{$kpiRow}", $row[3]);
            $sheet->getStyle("A{$kpiRow}")->getFont()->setBold(true)->getColor()->setRGB('475569');
            $sheet->getStyle("B{$kpiRow}")->getFont()->setBold(true)->getColor()->setRGB('1E3A8A');
            $sheet->getStyle("C{$kpiRow}")->getFont()->setBold(true)->getColor()->setRGB('475569');
            $sheet->getStyle("D{$kpiRow}")->getFont()->setBold(true)->getColor()->setRGB('15803D');
            $kpiRow++;
        }

        // Section: Rekapitulasi per Kecamatan
        $secRow = $kpiRow + 2;
        $sheet->setCellValue("A{$secRow}", 'REKAPITULASI PROGRES PER KECAMATAN');
        $sheet->getStyle("A{$secRow}")->getFont()->setSize(12)->setBold(true)->getColor()->setRGB('1E3A5F');

        $headers = ['No', 'Kecamatan', 'Total Sekolah', 'Prio A', 'Prio B', 'Prio C', 'Siswa XII', 'Dikunjungi', 'Follow Up', 'Prospek', 'Mahasiswa Fix'];
        $headerRow = $secRow + 1;
        $colIndex = 1;
        foreach ($headers as $h) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$colLetter}{$headerRow}", $h);
            $colIndex++;
        }
        $highestCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A{$headerRow}:{$highestCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $districtsSummary = District::select(
            'districts.id',
            'districts.name',
            DB::raw('COUNT(DISTINCT schools.id) as total_schools'),
            DB::raw('SUM(CASE WHEN schools.priority_level = "A" THEN 1 ELSE 0 END) as priority_a'),
            DB::raw('SUM(CASE WHEN schools.priority_level = "B" THEN 1 ELSE 0 END) as priority_b'),
            DB::raw('SUM(CASE WHEN schools.priority_level = "C" THEN 1 ELSE 0 END) as priority_c'),
            DB::raw('SUM(schools.grade_12_count) as total_grade_12')
        )
        ->leftJoin('schools', 'districts.id', '=', 'schools.district_id')
        ->groupBy('districts.id', 'districts.name')
        ->having('total_schools', '>', 0)
        ->orderBy('total_schools', 'desc')
        ->get();

        $dataRow = $headerRow + 1;
        $num = 1;
        foreach ($districtsSummary as $dist) {
            $visitedCount = SchoolVisit::whereHas('school', fn($q) => $q->where('district_id', $dist->id))->distinct('school_id')->count('school_id');
            $followupCount = FollowUp::whereHas('school', fn($q) => $q->where('district_id', $dist->id))->count();
            $prospectCount = Prospect::whereHas('school', fn($q) => $q->where('district_id', $dist->id))->count();
            $mhsCount = Prospect::whereHas('school', fn($q) => $q->where('district_id', $dist->id))->where('status', 'mahasiswa')->count();

            $sheet->setCellValue("A{$dataRow}", $num++);
            $sheet->setCellValue("B{$dataRow}", $dist->name);
            $sheet->setCellValue("C{$dataRow}", $dist->total_schools);
            $sheet->setCellValue("D{$dataRow}", $dist->priority_a);
            $sheet->setCellValue("E{$dataRow}", $dist->priority_b);
            $sheet->setCellValue("F{$dataRow}", $dist->priority_c);
            $sheet->setCellValue("G{$dataRow}", $dist->total_grade_12);
            $sheet->setCellValue("H{$dataRow}", $visitedCount);
            $sheet->setCellValue("I{$dataRow}", $followupCount);
            $sheet->setCellValue("J{$dataRow}", $prospectCount);
            $sheet->setCellValue("K{$dataRow}", $mhsCount);

            $sheet->getStyle("A{$dataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$dataRow}:K{$dataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $dataRow++;
        }

        $sheet->getStyle("A{$headerRow}:{$highestCol}" . ($dataRow - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        // Auto width
        for ($col = 1; $col <= count($headers); $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }

    /**
     * SHEET: Detail Progres Kunjungan Sekolah
     */
    protected function buildVisitsSheet(Spreadsheet $spreadsheet, Request $request, string $campusName, string $exportDate): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detail Kunjungan');

        $sheet->setCellValue('A1', 'LAPORAN DETAIL PROGRES KUNJUNGAN SEKOLAH');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E3A5F');

        $sheet->setCellValue('A2', strtoupper($campusName) . ' • Dicetak pada: ' . $exportDate);
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'No', 'Tanggal', 'Nama Sekolah', 'Kecamatan', 'Petugas Marketing',
            'Penerima / PIC', 'Jabatan PIC', 'Jml Kls 12', 'Est. Siswa',
            'Metode Sosialisasi', 'Hasil Kunjungan', 'Tgl Follow Up Berikutnya', 'Status'
        ];

        $headerRow = 4;
        $colIndex = 1;
        foreach ($headers as $h) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$col}{$headerRow}", $h);
            $colIndex++;
        }
        $highestCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A{$headerRow}:{$highestCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $query = SchoolVisit::with(['school.district', 'user'])->latest('visit_date');
        $this->applyCommonFilters($query, $request, 'visit_date');
        $visits = $query->get();

        $row = 5;
        $num = 1;
        foreach ($visits as $v) {
            $sheet->setCellValue("A{$row}", $num++);
            $sheet->setCellValue("B{$row}", $v->visit_date ? $v->visit_date->format('d/m/Y') : '-');
            $sheet->setCellValue("C{$row}", $v->school->name ?? '-');
            $sheet->setCellValue("D{$row}", $v->school->district->name ?? '-');
            $sheet->setCellValue("E{$row}", $v->user->name ?? '-');
            $sheet->setCellValue("F{$row}", $v->recipient_name ?: ($v->contact_person ?: '-'));
            $sheet->setCellValue("G{$row}", $v->recipient_position ?? '-');
            $sheet->setCellValue("H{$row}", $v->grade_12_class_count ?? '-');
            $sheet->setCellValue("I{$row}", ($v->grade_12_class_count && $v->students_per_class) ? ($v->grade_12_class_count * $v->students_per_class) : '-');
            $sheet->setCellValue("J{$row}", $v->socialization_method ?? '-');
            $sheet->setCellValue("K{$row}", $v->result ?: ($v->notes ?: '-'));
            $sheet->setCellValue("L{$row}", $v->next_followup_date ? $v->next_followup_date->format('d/m/Y') : '-');
            $sheet->setCellValue("M{$row}", ucfirst(str_replace('_', ' ', $v->status ?? 'pending')));

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$row}:M{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        $sheet->getStyle("A{$headerRow}:{$highestCol}" . max($row - 1, $headerRow))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        for ($col = 1; $col <= count($headers); $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }

    /**
     * SHEET: Detail Progres Follow Up Sekolah
     */
    protected function buildFollowupsSheet(Spreadsheet $spreadsheet, Request $request, string $campusName, string $exportDate): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detail Follow Up');

        $sheet->setCellValue('A1', 'LAPORAN DETAIL PROGRES FOLLOW UP SEKOLAH & JADWAL SOSIALISASI');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E3A5F');

        $sheet->setCellValue('A2', strtoupper($campusName) . ' • Dicetak pada: ' . $exportDate);
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'No', 'Tanggal Follow Up', 'Nama Sekolah', 'Kecamatan', 'Petugas Marketing',
            'Metode', 'PIC Sekolah', 'Jabatan PIC', 'No. HP / WA PIC',
            'Status Follow Up', 'Jadwal FIX Sosialisasi', 'Target Siswa', 'Catatan / Hasil'
        ];

        $headerRow = 4;
        $colIndex = 1;
        foreach ($headers as $h) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$col}{$headerRow}", $h);
            $colIndex++;
        }
        $highestCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A{$headerRow}:{$highestCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $query = FollowUp::with(['school.district', 'user'])->latest('followup_date');
        $this->applyCommonFilters($query, $request, 'followup_date');
        $followups = $query->get();

        $row = 5;
        $num = 1;
        foreach ($followups as $f) {
            $scheduleFixText = '-';
            if ($f->is_schedule_fixed || $f->fixed_date) {
                $scheduleFixText = ($f->fixed_date ? $f->fixed_date->format('d/m/Y') : '') . ' ' . ($f->fixed_time ?? '') . ' (' . ($f->socialization_location ?? 'Sekolah') . ')';
            }

            $sheet->setCellValue("A{$row}", $num++);
            $sheet->setCellValue("B{$row}", $f->followup_date ? $f->followup_date->format('d/m/Y') : '-');
            $sheet->setCellValue("C{$row}", $f->school->name ?? '-');
            $sheet->setCellValue("D{$row}", $f->school->district->name ?? '-');
            $sheet->setCellValue("E{$row}", $f->user->name ?? '-');
            $sheet->setCellValue("F{$row}", ucfirst($f->method ?? 'WA'));
            $sheet->setCellValue("G{$row}", $f->pic_name ?? '-');
            $sheet->setCellValue("H{$row}", $f->pic_position ?? '-');
            $sheet->setCellValueExplicit("I{$row}", $f->pic_phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("J{$row}", FollowUp::statusLabels()[$f->status] ?? ucfirst($f->status));
            $sheet->setCellValue("K{$row}", $scheduleFixText);
            $sheet->setCellValue("L{$row}", $f->target_audience_count ?: '-');
            $sheet->setCellValue("M{$row}", $f->notes ?: ($f->result ?: '-'));

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        $sheet->getStyle("A{$headerRow}:{$highestCol}" . max($row - 1, $headerRow))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        for ($col = 1; $col <= count($headers); $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }

    /**
     * SHEET: Detail Progres Prospek Siswa (Pipeline CRM)
     */
    protected function buildProspectsSheet(Spreadsheet $spreadsheet, Request $request, string $campusName, string $exportDate): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detail Prospek Siswa');

        $sheet->setCellValue('A1', 'LAPORAN DETAIL PROGRES PROSPEK SISWA & KONVERSI PMB');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E3A5F');

        $sheet->setCellValue('A2', strtoupper($campusName) . ' • Dicetak pada: ' . $exportDate);
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'No', 'Tgl Terdaftar', 'Nama Lengkap Siswa', 'JK', 'Asal Sekolah',
            'Kecamatan', 'Program Studi Minat', 'No. WhatsApp / HP', 'Petugas Marketing', 'Status Pipeline CRM'
        ];

        $headerRow = 4;
        $colIndex = 1;
        foreach ($headers as $h) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$col}{$headerRow}", $h);
            $colIndex++;
        }
        $highestCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A{$headerRow}:{$highestCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $query = Prospect::with(['school.district', 'marketingUser'])->latest('created_at');
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('district_id')) {
            $query->whereHas('school', fn($q) => $q->where('district_id', $request->district_id));
        }
        if ($request->filled('user_id')) {
            $query->where('marketing_user_id', $request->user_id);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('school', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }
        $prospects = $query->get();

        $row = 5;
        $num = 1;
        foreach ($prospects as $p) {
            $sheet->setCellValue("A{$row}", $num++);
            $sheet->setCellValue("B{$row}", $p->created_at ? $p->created_at->format('d/m/Y') : '-');
            $sheet->setCellValue("C{$row}", $p->name);
            $sheet->setCellValue("D{$row}", $p->gender ?? '-');
            $sheet->setCellValue("E{$row}", $p->school->name ?? '-');
            $sheet->setCellValue("F{$row}", $p->school->district->name ?? '-');
            $sheet->setCellValue("G{$row}", $p->interest_program ?? '-');
            $sheet->setCellValueExplicit("H{$row}", $p->phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("I{$row}", $p->marketingUser->name ?? '-');
            $sheet->setCellValue("J{$row}", strtoupper(str_replace('_', ' ', $p->status ?? 'lead')));

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        $sheet->getStyle("A{$headerRow}:{$highestCol}" . max($row - 1, $headerRow))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        for ($col = 1; $col <= count($headers); $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }

    /**
     * SHEET: Detail Progres Aktivitas Marketing
     */
    protected function buildActivitiesSheet(Spreadsheet $spreadsheet, Request $request, string $campusName, string $exportDate): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detail Aktivitas');

        $sheet->setCellValue('A1', 'LAPORAN DETAIL PROGRES AKTIVITAS & SOSIALISASI MARKETING');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E3A5F');

        $sheet->setCellValue('A2', strtoupper($campusName) . ' • Dicetak pada: ' . $exportDate);
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'No', 'Nama Aktivitas / Kegiatan', 'Tipe Aktivitas', 'Tanggal Mulai',
            'Tanggal Selesai', 'Sekolah Terlibat', 'Deskripsi / Capaian'
        ];

        $headerRow = 4;
        $colIndex = 1;
        foreach ($headers as $h) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$col}{$headerRow}", $h);
            $colIndex++;
        }
        $highestCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A{$headerRow}:{$highestCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $query = MarketingActivity::with(['schoolActivities.school'])->latest('start_date');
        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('activity_type', $request->status);
        }
        $activities = $query->get();

        $row = 5;
        $num = 1;
        foreach ($activities as $a) {
            $schools = $a->schoolActivities->map(fn($sa) => $sa->school->name ?? '')->filter()->implode(', ');

            $sheet->setCellValue("A{$row}", $num++);
            $sheet->setCellValue("B{$row}", $a->name);
            $sheet->setCellValue("C{$row}", ucfirst($a->activity_type ?? 'Sosialisasi'));
            $sheet->setCellValue("D{$row}", $a->start_date ? $a->start_date->format('d/m/Y') : '-');
            $sheet->setCellValue("E{$row}", $a->end_date ? $a->end_date->format('d/m/Y') : '-');
            $sheet->setCellValue("F{$row}", $schools ?: 'Semua Sekolah');
            $sheet->setCellValue("G{$row}", $a->description ?? '-');

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        $sheet->getStyle("A{$headerRow}:{$highestCol}" . max($row - 1, $headerRow))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        for ($col = 1; $col <= count($headers); $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }

    /**
     * SHEET: Detail Progres Kuisioner Siswa
     */
    protected function buildQuestionnairesSheet(Spreadsheet $spreadsheet, Request $request, string $campusName, string $exportDate): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detail Kuisioner');

        $sheet->setCellValue('A1', 'LAPORAN DETAIL PROGRES KUISIONER & MINAT SISWA');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E3A5F');

        $sheet->setCellValue('A2', strtoupper($campusName) . ' • Dicetak pada: ' . $exportDate);
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'No', 'Waktu Masuk', 'Nama Siswa', 'Kelas / Jurusan', 'No. WhatsApp Siswa',
            'Minat Beasiswa', 'Status Follow Up', 'Petugas Terakhir', 'Catatan Follow Up'
        ];

        $headerRow = 4;
        $colIndex = 1;
        foreach ($headers as $h) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$col}{$headerRow}", $h);
            $colIndex++;
        }
        $highestCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A{$headerRow}:{$highestCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $query = StudentQuestionnaire::with('lastFollowedUpBy')->latest('created_at');
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('followup_status', $request->status);
        }
        if ($request->filled('user_id')) {
            $query->where('last_followed_up_by', $request->user_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_phone', 'like', "%{$search}%")
                  ->orWhere('student_class', 'like', "%{$search}%");
            });
        }
        $records = $query->get();

        $row = 5;
        $num = 1;
        foreach ($records as $item) {
            $sheet->setCellValue("A{$row}", $num++);
            $sheet->setCellValue("B{$row}", $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-');
            $sheet->setCellValue("C{$row}", $item->student_name);
            $sheet->setCellValue("D{$row}", ($item->student_class ?? '') . ' ' . ($item->student_major ?? ''));
            $sheet->setCellValueExplicit("E{$row}", $item->student_phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("F{$row}", $item->scholarship_interest ?? 'Tidak');
            $sheet->setCellValue("G{$row}", StudentQuestionnaire::followupStatusOptions()[$item->followup_status] ?? $item->followup_status);
            $sheet->setCellValue("H{$row}", $item->lastFollowedUpBy->name ?? '-');
            $sheet->setCellValue("I{$row}", $item->followup_notes ?: ($item->college_interest_notes ?: '-'));

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        $sheet->getStyle("A{$headerRow}:{$highestCol}" . max($row - 1, $headerRow))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        for ($col = 1; $col <= count($headers); $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }

    /**
     * SHEET: Detail Progres Pemetaan & Prioritas Sekolah
     */
    protected function buildSchoolsSheet(Spreadsheet $spreadsheet, Request $request, string $campusName, string $exportDate): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Pemetaan Sekolah');

        $sheet->setCellValue('A1', 'LAPORAN DETAIL PROGRES PEMETAAN & PRIORITAS SEKOLAH');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E3A5F');

        $sheet->setCellValue('A2', strtoupper($campusName) . ' • Dicetak pada: ' . $exportDate);
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'No', 'NPSN', 'Nama Sekolah', 'Status', 'Kecamatan',
            'Akreditasi', 'Kategori Prioritas', 'Siswa Kls 12', 'Status Kunjungan', 'Jml Follow Up'
        ];

        $headerRow = 4;
        $colIndex = 1;
        foreach ($headers as $h) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$col}{$headerRow}", $h);
            $colIndex++;
        }
        $highestCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A{$headerRow}:{$highestCol}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $query = School::with(['district', 'accreditation'])
            ->withCount(['visits', 'followUps', 'prospects'])
            ->orderBy('priority_level')
            ->orderBy('name');

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('priority_level', $request->status);
        }
        if ($request->filled('visited_status')) {
            if ($request->visited_status === 'visited') {
                $query->has('visits');
            } elseif ($request->visited_status === 'unvisited') {
                $query->doesntHave('visits');
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('npsn', 'like', "%{$search}%");
            });
        }
        $schools = $query->get();

        $row = 5;
        $num = 1;
        foreach ($schools as $s) {
            $visitedStatus = $s->visits_count > 0 ? "Sudah ({$s->visits_count}x)" : 'Belum';

            $sheet->setCellValue("A{$row}", $num++);
            $sheet->setCellValueExplicit("B{$row}", $s->npsn ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("C{$row}", $s->name);
            $sheet->setCellValue("D{$row}", $s->status ?? 'Swasta');
            $sheet->setCellValue("E{$row}", $s->district->name ?? '-');
            $sheet->setCellValue("F{$row}", $s->accreditation->code ?? ($s->accreditation->name ?? '-'));
            $sheet->setCellValue("G{$row}", 'Prioritas ' . ($s->priority_level ?? 'C'));
            $sheet->setCellValue("H{$row}", $s->grade_12_count ? number_format($s->grade_12_count) : '-');
            $sheet->setCellValue("I{$row}", $visitedStatus);
            $sheet->setCellValue("J{$row}", $s->follow_ups_count);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        $sheet->getStyle("A{$headerRow}:{$highestCol}" . max($row - 1, $headerRow))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        for ($col = 1; $col <= count($headers); $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }

    /**
     * Helper to apply common date, district, user, and status filters.
     */
    protected function applyCommonFilters($query, Request $request, string $dateColumn): void
    {
        if ($request->filled('start_date')) {
            $query->whereDate($dateColumn, '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate($dateColumn, '<=', $request->end_date);
        }
        if ($request->filled('district_id')) {
            $query->whereHas('school', fn($q) => $q->where('district_id', $request->district_id));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('school', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }
    }
}
