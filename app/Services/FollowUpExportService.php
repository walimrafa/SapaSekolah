<?php

namespace App\Services;

use App\Models\FollowUp;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FollowUpExportService
{
    /**
     * Export follow up and fixed socialization schedules into a styled Excel file.
     */
    public function exportExcel(Collection $followUps, array $filterInfo = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        
        // -------------------------------------------------------------
        // SHEET 1: DETAIL JADWAL & FOLLOW UP
        // -------------------------------------------------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal & Follow Up');

        // Document Properties
        $spreadsheet->getProperties()
            ->setCreator('SAPA Sekolah UBSI Karawang')
            ->setTitle('Laporan Follow Up & Jadwal FIX Sosialisasi');

        // 1. Title Banner
        $sheet->setCellValue('A1', 'LAPORAN HASIL FOLLOW UP & JADWAL FIX SOSIALISASI');
        $sheet->mergeCells('A1:Q1');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('1E3A5F');

        $sheet->setCellValue('A2', 'SAPA SEKOLAH • UNIVERSITAS BINA SARANA INFORMATIKA (UBSI) KAMPUS KARAWANG');
        $sheet->mergeCells('A2:Q2');
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->getColor()->setRGB('64748B');

        // 2. Metadata Box
        $fixedCount = $followUps->filter(fn($f) => $f->is_schedule_fixed || $f->status === 'completed')->count();
        $totalAudience = $followUps->where('is_schedule_fixed', true)->sum('target_audience_count');
        $exportDateStr = Carbon::now()->translatedFormat('l, d F Y H:i') . ' WIB';

        $sheet->setCellValue('A4', 'Tanggal Unduh:');
        $sheet->setCellValue('B4', $exportDateStr);
        $sheet->setCellValue('A5', 'Total Data:');
        $sheet->setCellValue('B5', $followUps->count() . ' Sekolah');
        $sheet->setCellValue('A6', 'Filter Status:');
        $sheet->setCellValue('B6', $filterInfo['status_label'] ?? 'Semua Status');

        $sheet->setCellValue('E4', 'Jadwal FIX Terkunci:');
        $sheet->setCellValue('F4', $fixedCount . ' Sekolah');
        $sheet->setCellValue('E5', 'Total Estimasi Siswa:');
        $sheet->setCellValue('F5', number_format($totalAudience) . ' Siswa Kelas 12');
        $sheet->setCellValue('E6', 'Staf PIC Marketing:');
        $sheet->setCellValue('F6', $filterInfo['marketing_name'] ?? 'Semua Staf');

        $sheet->getStyle('A4:A6')->getFont()->setBold(true)->getColor()->setRGB('334155');
        $sheet->getStyle('E4:E6')->getFont()->setBold(true)->getColor()->setRGB('334155');
        $sheet->getStyle('B4:B6')->getFont()->setBold(true)->getColor()->setRGB('1E3A8A');
        $sheet->getStyle('F4')->getFont()->setBold(true)->getColor()->setRGB('15803D');
        $sheet->getStyle('F5')->getFont()->setBold(true)->getColor()->setRGB('7C3AED');

        // 3. Table Headers
        $headers = [
            'A8' => 'No',
            'B8' => 'Nama Sekolah',
            'C8' => 'NPSN',
            'D8' => 'Jenjang',
            'E8' => 'Kecamatan',
            'F8' => 'Status Follow Up',
            'G8' => 'Status Jadwal',
            'H8' => 'Tgl Sosialisasi FIX',
            'I8' => 'Jam Sosialisasi',
            'J8' => 'Format / Ruang Sosialisasi',
            'K8' => 'Target Siswa (Kls 12)',
            'L8' => 'Nama PIC Sekolah',
            'M8' => 'Jabatan PIC',
            'N8' => 'Kontak / No. WA PIC',
            'O8' => 'Staf Marketing UBSI',
            'P8' => 'Tgl Follow Up Terakhir',
            'Q8' => 'Catatan & Hasil Koordinasi',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A8:Q8')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A5F'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getRowDimension(8)->setRowHeight(30);

        // 4. Fill Data Rows
        $row = 9;
        $no = 1;

        foreach ($followUps as $item) {
            $isFixed = $item->is_schedule_fixed || $item->status === 'completed';
            $fixedDateStr = $item->fixed_date 
                ? Carbon::parse($item->fixed_date)->format('d/m/Y') 
                : ($item->visit?->target_date ? Carbon::parse($item->visit->target_date)->format('d/m/Y') . ' (Tentatif)' : '-');

            $statusText = match ($item->status) {
                'pending' => 'Menunggu Konfirmasi',
                'progress' => 'Koordinasi Jadwal',
                'completed' => 'Jadwal FIX',
                'failed' => 'Batal / Ditolak',
                default => ucfirst($item->status),
            };

            $scheduleStatusText = $isFixed ? 'JADWAL FIX' : 'BELUM FIX';

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $item->school?->name ?? '-');
            $sheet->setCellValueExplicit("C{$row}", (string)($item->school?->npsn ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue("D{$row}", $item->school?->type ?? '-');
            $sheet->setCellValue("E{$row}", $item->school?->district?->name ?? '-');
            $sheet->setCellValue("F{$row}", $statusText);
            $sheet->setCellValue("G{$row}", $scheduleStatusText);
            $sheet->setCellValue("H{$row}", $fixedDateStr);
            $sheet->setCellValue("I{$row}", $item->fixed_time ?: ($item->visit?->target_time ?: '-'));
            $sheet->setCellValue("J{$row}", $item->socialization_location ?: ($item->visit?->socialization_method ?: '-'));
            $sheet->setCellValue("K{$row}", $item->target_audience_count ? number_format($item->target_audience_count) : '-');
            $sheet->setCellValue("L{$row}", $item->pic_name ?: ($item->visit?->recipient_name ?: '-'));
            $sheet->setCellValue("M{$row}", $item->pic_position ?: ($item->visit?->recipient_position ?: '-'));
            $sheet->setCellValueExplicit("N{$row}", (string)($item->pic_phone ?: ($item->school?->primaryContact?->phone ?? '-')), DataType::TYPE_STRING);
            $sheet->setCellValue("O{$row}", $item->user?->name ?? 'Marketing UBSI');
            $sheet->setCellValue("P{$row}", $item->followup_date ? Carbon::parse($item->followup_date)->format('d/m/Y') : '-');
            $sheet->setCellValue("Q{$row}", $item->notes ?: ($item->description ?? '-'));

            // Styling per row
            $isZebra = ($no % 2 === 0);
            $bgColor = $isFixed 
                ? 'F0FDF4' // Soft green for fixed schedules
                : ($isZebra ? 'F8FAFC' : 'FFFFFF');

            $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgColor],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'font' => [
                    'size' => 9,
                ],
            ]);

            // Specific cell alignments
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("P{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($isFixed) {
                $sheet->getStyle("G{$row}")->getFont()->setBold(true)->getColor()->setRGB('166534');
                $sheet->getStyle("H{$row}")->getFont()->setBold(true)->getColor()->setRGB('166534');
            }

            $sheet->getRowDimension($row)->setRowHeight(24);
            $row++;
            $no++;
        }

        $lastDataRow = $row - 1;

        // Apply grid borders
        if ($lastDataRow >= 8) {
            $sheet->getStyle("A8:Q{$lastDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CBD5E1'],
                    ],
                ],
            ]);
        }

        // Auto width for columns
        foreach (range('A', 'Q') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // -------------------------------------------------------------
        // SHEET 2: REKAPITULASI & STATISTIK JADWAL FIX
        // -------------------------------------------------------------
        $summarySheet = $spreadsheet->createSheet();
        $summarySheet->setTitle('Rekapitulasi Statistik');

        $summarySheet->setCellValue('A1', 'RINGKASAN & STATISTIK JADWAL SOSIALISASI FIX');
        $summarySheet->mergeCells('A1:G1');
        $summarySheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('1E3A5F');

        // Table 1: Rekap Status Follow Up
        $summarySheet->setCellValue('A3', '1. Rekapitulasi Berdasarkan Status Follow Up');
        $summarySheet->getStyle('A3')->getFont()->setBold(true)->setSize(11);

        $summarySheet->setCellValue('A4', 'Status');
        $summarySheet->setCellValue('B4', 'Jumlah Sekolah');
        $summarySheet->setCellValue('C4', 'Persentase (%)');
        $summarySheet->getStyle('A4:C4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $statuses = [
            'completed' => 'Jadwal Sosialisasi FIX',
            'progress' => 'Koordinasi Jadwal (In Progress)',
            'pending' => 'Menunggu Konfirmasi',
            'failed' => 'Batal / Belum Diizinkan',
        ];

        $totalAll = $followUps->count();
        $sRow = 5;
        foreach ($statuses as $stKey => $stLabel) {
            $cnt = $followUps->where('status', $stKey)->count();
            $pct = $totalAll > 0 ? round(($cnt / $totalAll) * 100, 1) : 0;
            
            $summarySheet->setCellValue("A{$sRow}", $stLabel);
            $summarySheet->setCellValue("B{$sRow}", $cnt);
            $summarySheet->setCellValue("C{$sRow}", "{$pct}%");
            $summarySheet->getStyle("B{$sRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $summarySheet->getStyle("C{$sRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sRow++;
        }
        $summarySheet->setCellValue("A{$sRow}", 'Total Keseluruhan');
        $summarySheet->setCellValue("B{$sRow}", $totalAll);
        $summarySheet->setCellValue("C{$sRow}", '100%');
        $summarySheet->getStyle("A{$sRow}:C{$sRow}")->getFont()->setBold(true);
        $summarySheet->getStyle("B{$sRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $summarySheet->getStyle("C{$sRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $summarySheet->getStyle("A4:C{$sRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        // Table 2: Rekap Jadwal FIX per Bulan
        $mRow = $sRow + 3;
        $summarySheet->setCellValue("A{$mRow}", '2. Sebaran Jadwal Sosialisasi FIX per Bulan');
        $summarySheet->getStyle("A{$mRow}")->getFont()->setBold(true)->setSize(11);
        $mRow++;

        $summarySheet->setCellValue("A{$mRow}", 'Bulan Pelaksanaan');
        $summarySheet->setCellValue("B{$mRow}", 'Jumlah Sekolah FIX');
        $summarySheet->setCellValue("C{$mRow}", 'Total Estimasi Siswa');
        $summarySheet->getStyle("A{$mRow}:C{$mRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $fixedFollowUps = $followUps->filter(fn($f) => $f->is_schedule_fixed || $f->status === 'completed');
        $monthlyMap = [];
        foreach ($fixedFollowUps as $ff) {
            $mKey = $ff->fixed_date ? Carbon::parse($ff->fixed_date)->translatedFormat('F Y') : 'Tentatif';
            if (!isset($monthlyMap[$mKey])) {
                $monthlyMap[$mKey] = ['count' => 0, 'audience' => 0];
            }
            $monthlyMap[$mKey]['count']++;
            $monthlyMap[$mKey]['audience'] += (int) ($ff->target_audience_count ?: 0);
        }

        $mRowStart = $mRow + 1;
        $currMRow = $mRowStart;
        if (empty($monthlyMap)) {
            $summarySheet->setCellValue("A{$currMRow}", 'Belum ada jadwal yang di-FIX kan');
            $summarySheet->setCellValue("B{$currMRow}", 0);
            $summarySheet->setCellValue("C{$currMRow}", 0);
            $currMRow++;
        } else {
            foreach ($monthlyMap as $mName => $mData) {
                $summarySheet->setCellValue("A{$currMRow}", $mName);
                $summarySheet->setCellValue("B{$currMRow}", $mData['count']);
                $summarySheet->setCellValue("C{$currMRow}", number_format($mData['audience']));
                $summarySheet->getStyle("B{$currMRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $summarySheet->getStyle("C{$currMRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $currMRow++;
            }
        }
        $summarySheet->getStyle("A{$mRow}:C" . ($currMRow - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        // Auto width for Summary Sheet
        foreach (range('A', 'D') as $col) {
            $summarySheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Return first sheet as active view
        $spreadsheet->setActiveSheetIndex(0);

        // Filename
        $dateSlug = date('Y-m-d_His');
        $filename = "Jadwal_Sosialisasi_FIX_FollowUp_{$dateSlug}.xlsx";

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
