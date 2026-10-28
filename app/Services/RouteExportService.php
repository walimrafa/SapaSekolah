<?php

namespace App\Services;

use App\Models\MarketingRoute;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RouteExportService
{
    /**
     * Prepare complete data sequence, distances, and contact details for a saved route.
     */
    public function prepareRouteData(MarketingRoute $route): array
    {
        Carbon::setLocale('id');

        $ubsiLat = SchoolScoringService::UBSI_LATITUDE;
        $ubsiLng = SchoolScoringService::UBSI_LONGITUDE;

        $route->loadMissing([
            'user',
            'routeSchools' => function ($q) {
                $q->orderBy('sequence', 'asc');
            },
            'routeSchools.school.district',
            'routeSchools.school.village',
            'routeSchools.school.schoolType',
            'routeSchools.school.accreditation',
            'routeSchools.school.primaryContact.position',
            'routeSchools.school.contacts.position',
        ]);

        $prevLat = $ubsiLat;
        $prevLng = $ubsiLng;
        $schoolsData = [];
        $totalGrade12 = 0;
        $priorityCounts = ['A' => 0, 'B' => 0, 'C' => 0];
        $waypoints = [];

        foreach ($route->routeSchools as $rs) {
            $school = $rs->school;
            $legDist = 0.0;

            if ($school && $school->latitude && $school->longitude) {
                $legDist = round($this->haversine($prevLat, $prevLng, (float)$school->latitude, (float)$school->longitude), 2);
                $prevLat = (float)$school->latitude;
                $prevLng = (float)$school->longitude;
                $waypoints[] = $school->latitude . ',' . $school->longitude;
            }

            if ($school) {
                $totalGrade12 += (int)$school->grade_12_count;
                $prio = strtoupper($school->priority_level ?? 'C');
                if (isset($priorityCounts[$prio])) {
                    $priorityCounts[$prio]++;
                }

                // Determine primary contact
                $contact = $school->primaryContact ?? $school->contacts->first();
                $contactName = $contact ? $contact->name : '-';
                $contactPosition = $contact && $contact->position ? $contact->position->name : ($contact && $contact->position_id ? '-' : '-');
                $contactPhone = $contact ? ($contact->phone ?: $contact->whatsapp ?: '-') : ($school->phone ?: '-');
            } else {
                $contactName = '-';
                $contactPosition = '-';
                $contactPhone = '-';
            }

            $schoolsData[] = [
                'sequence' => $rs->sequence,
                'school' => $school,
                'route_school' => $rs,
                'leg_distance' => $legDist,
                'contact_name' => $contactName,
                'contact_position' => $contactPosition,
                'contact_phone' => $contactPhone,
            ];
        }

        // Return trip to UBSI Karawang
        $returnDist = round($this->haversine($prevLat, $prevLng, $ubsiLat, $ubsiLng), 2);

        // Build Google Maps driving URL
        $ubsiStr = "{$ubsiLat},{$ubsiLng}";
        $waypointsStr = implode('|', $waypoints);
        $gmapsUrl = "https://www.google.com/maps/dir/?api=1&origin={$ubsiStr}&destination={$ubsiStr}&waypoints={$waypointsStr}&travelmode=driving";
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=10&data=" . urlencode($gmapsUrl);

        return [
            'route' => $route,
            'schoolsData' => $schoolsData,
            'totalSchools' => count($schoolsData),
            'totalGrade12' => $totalGrade12,
            'priorityCounts' => $priorityCounts,
            'returnDistance' => $returnDist,
            'gmapsUrl' => $gmapsUrl,
            'qrCodeUrl' => $qrCodeUrl,
            'ubsiLat' => $ubsiLat,
            'ubsiLng' => $ubsiLng,
        ];
    }

    /**
     * Prepare sequence and contact data from an on-the-fly preview cluster.
     */
    public function preparePreviewData(array $cluster, array $metadata = []): array
    {
        Carbon::setLocale('id');

        $ubsiLat = SchoolScoringService::UBSI_LATITUDE;
        $ubsiLng = SchoolScoringService::UBSI_LONGITUDE;

        $schoolsData = [];
        $totalGrade12 = 0;
        $priorityCounts = ['A' => 0, 'B' => 0, 'C' => 0];

        $rawSequence = $cluster['sequence'] ?? [];

        foreach ($rawSequence as $idx => $s) {
            $schoolModel = School::with(['district', 'village', 'schoolType', 'accreditation', 'primaryContact.position', 'contacts.position'])
                ->find($s['id'] ?? null);

            $contactName = '-';
            $contactPosition = '-';
            $contactPhone = '-';

            if ($schoolModel) {
                $totalGrade12 += (int)$schoolModel->grade_12_count;
                $prio = strtoupper($schoolModel->priority_level ?? 'C');
                if (isset($priorityCounts[$prio])) {
                    $priorityCounts[$prio]++;
                }

                $contact = $schoolModel->primaryContact ?? $schoolModel->contacts->first();
                $contactName = $contact ? $contact->name : '-';
                $contactPosition = $contact && $contact->position ? $contact->position->name : '-';
                $contactPhone = $contact ? ($contact->phone ?: $contact->whatsapp ?: '-') : ($schoolModel->phone ?: '-');
            }

            $schoolsData[] = [
                'sequence' => $idx + 1,
                'school' => $schoolModel,
                'route_school' => null,
                'leg_distance' => $s['leg_distance'] ?? 0.0,
                'contact_name' => $contactName,
                'contact_position' => $contactPosition,
                'contact_phone' => $contactPhone,
            ];
        }

        $gmapsUrl = $cluster['google_maps_driving_url'] ?? "https://www.google.com/maps";
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=10&data=" . urlencode($gmapsUrl);

        $fakeRoute = new MarketingRoute([
            'name' => $metadata['name'] ?? 'Preview Rute Mobil Klaster',
            'date' => $metadata['date'] ?? today()->format('Y-m-d'),
            'start_location' => $cluster['start_location'] ?? 'Kampus UBSI Karawang',
            'end_location' => $cluster['end_location'] ?? 'Kampus UBSI Karawang',
            'total_distance' => $cluster['total_distance_km'] ?? 0,
            'estimated_duration' => $cluster['estimated_duration_minutes'] ?? 0,
            'status' => 'planned',
        ]);
        if (!empty($metadata['user_name'])) {
            $fakeRoute->setRelation('user', (object)['name' => $metadata['user_name']]);
        }

        return [
            'route' => $fakeRoute,
            'schoolsData' => $schoolsData,
            'totalSchools' => count($schoolsData),
            'totalGrade12' => $totalGrade12,
            'priorityCounts' => $priorityCounts,
            'returnDistance' => 0.0,
            'gmapsUrl' => $gmapsUrl,
            'qrCodeUrl' => $qrCodeUrl,
            'ubsiLat' => $ubsiLat,
            'ubsiLng' => $ubsiLng,
        ];
    }

    /**
     * Export prepared route data to styled Excel (.xlsx) file.
     */
    public function exportExcel(MarketingRoute $route, array $routeData): StreamedResponse
    {
        $schoolsData = $routeData['schoolsData'] ?? [];
        $gmapsUrl = $routeData['gmapsUrl'] ?? '';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rute Kunjungan Sekolah');

        // Document Properties
        $spreadsheet->getProperties()
            ->setCreator('SAPA Sekolah UBSI Karawang')
            ->setTitle('Daftar Sekolah Rute - ' . $route->name);

        // 1. Header Title
        $sheet->setCellValue('A1', 'DAFTAR SEKOLAH RUTE MARKETING (KUNJUNGAN OUTING)');
        $sheet->mergeCells('A1:T1');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('1E3A5F');

        $sheet->setCellValue('A2', 'SAPA SEKOLAH • UNIVERSITAS BINA SARANA INFORMATIKA (UBSI) KAMPUS KARAWANG');
        $sheet->mergeCells('A2:T2');
        $sheet->getStyle('A2')->getFont()->setSize(11)->setItalic(true)->getColor()->setRGB('64748B');

        // 2. Metadata Box
        $sheet->setCellValue('A4', 'Nama Rute:');
        $sheet->setCellValue('B4', $route->name);
        $sheet->setCellValue('A5', 'Tanggal Pelaksanaan:');
        $sheet->setCellValue('B5', $route->date ? Carbon::parse($route->date)->translatedFormat('l, d F Y') : '-');
        $sheet->setCellValue('A6', 'Staff PIC Marketing:');
        $sheet->setCellValue('B6', $route->user->name ?? ($route->relationLoaded('user') && $route->user ? $route->user->name : '-'));
        $sheet->setCellValue('A7', 'Status Rute:');
        $sheet->setCellValue('B7', strtoupper($route->status ?? 'PLANNED'));

        $sheet->setCellValue('D4', 'Jumlah Sasaran Sekolah:');
        $sheet->setCellValue('E4', count($schoolsData) . ' Sekolah');
        $sheet->setCellValue('D5', 'Total Estimasi Jarak:');
        $sheet->setCellValue('E5', number_format($route->total_distance, 1) . ' km');
        $sheet->setCellValue('D6', 'Estimasi Durasi Total:');
        $sheet->setCellValue('E6', number_format($route->estimated_duration) . ' Menit');
        $sheet->setCellValue('D7', 'Titik Berangkat / Selesai:');
        $sheet->setCellValue('E7', ($route->start_location ?? 'Kampus UBSI') . ' -> ' . ($route->end_location ?? 'Kampus UBSI'));

        $sheet->setCellValue('H4', 'Link Navigasi Google Maps Driving:');
        $sheet->setCellValue('H5', $gmapsUrl);
        $sheet->getCell('H5')->getHyperlink()->setUrl($gmapsUrl);
        $sheet->getStyle('H5')->getFont()->getColor()->setRGB('2563EB')->setUnderline(true);

        $sheet->getStyle('A4:A7')->getFont()->setBold(true);
        $sheet->getStyle('D4:D7')->getFont()->setBold(true);
        $sheet->getStyle('H4')->getFont()->setBold(true);

        // 3. Table Headers (Row 9)
        $headers = [
            'A9' => 'No Urut',
            'B9' => 'Nama Sekolah',
            'C9' => 'NPSN',
            'D9' => 'Jenjang',
            'E9' => 'Status',
            'F9' => 'Akreditasi',
            'G9' => 'Kecamatan',
            'H9' => 'Alamat Sekolah',
            'I9' => 'Kontak Utama (BK / Kepsek)',
            'J9' => 'Jabatan Kontak',
            'K9' => 'No. HP / WhatsApp',
            'L9' => 'Siswa Kls 12',
            'M9' => 'Skor Potensi',
            'N9' => 'Prioritas',
            'O9' => 'Jarak Leg (KM)',
            'P9' => 'Jam Tiba (Lap.)',
            'Q9' => 'Jam Selesai (Lap.)',
            'R9' => 'Status Kunjungan',
            'S9' => 'Catatan Hasil Sosialisasi Lapangan',
            'T9' => 'Link Google Maps',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerStyle = [
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
        ];
        $sheet->getStyle('A9:T9')->applyFromArray($headerStyle);
        $sheet->getRowDimension(9)->setRowHeight(28);

        // 4. Fill Data Rows
        $currentRow = 10;
        foreach ($schoolsData as $item) {
            $school = $item['school'];
            $rs = $item['route_school'];

            $npsn = $school ? (string)($school->npsn ?? '-') : '-';
            $schoolName = $school ? $school->name : '-';
            $schoolType = $school && $school->schoolType ? $school->schoolType->name : '-';
            $status = $school ? strtoupper($school->status ?? '-') : '-';
            $accreditation = $school && $school->accreditation ? $school->accreditation->code : '-';
            $district = $school && $school->district ? $school->district->name : '-';
            $address = $school ? ($school->address ?? '-') : '-';
            $contactName = $item['contact_name'];
            $contactPosition = $item['contact_position'];
            $contactPhone = (string)$item['contact_phone'];
            $grade12 = $school ? (int)($school->grade_12_count ?? 0) : 0;
            $potentialScore = $school ? (float)($school->potential_score ?? 0) : 0.0;
            $priority = $school ? strtoupper($school->priority_level ?? 'C') : 'C';
            $legDist = (float)($item['leg_distance'] ?? 0.0);
            $arrivalTime = $rs && $rs->arrival_time ? substr($rs->arrival_time, 0, 5) : '';
            $departureTime = $rs && $rs->departure_time ? substr($rs->departure_time, 0, 5) : '';
            $visitStatus = $rs ? strtoupper($rs->status ?? 'PENDING') : 'PENDING';
            $notes = $rs ? ($rs->notes ?? '') : '';
            $schoolGmaps = $school && $school->latitude ? "https://www.google.com/maps/search/?api=1&query={$school->latitude},{$school->longitude}" : '';

            $sheet->setCellValue("A{$currentRow}", $item['sequence']);
            $sheet->setCellValue("B{$currentRow}", $schoolName);
            $sheet->getCell("C{$currentRow}")->setValueExplicit($npsn, DataType::TYPE_STRING);
            $sheet->setCellValue("D{$currentRow}", $schoolType);
            $sheet->setCellValue("E{$currentRow}", $status);
            $sheet->setCellValue("F{$currentRow}", $accreditation);
            $sheet->setCellValue("G{$currentRow}", $district);
            $sheet->setCellValue("H{$currentRow}", $address);
            $sheet->setCellValue("I{$currentRow}", $contactName);
            $sheet->setCellValue("J{$currentRow}", $contactPosition);
            $sheet->getCell("K{$currentRow}")->setValueExplicit($contactPhone, DataType::TYPE_STRING);
            $sheet->setCellValue("L{$currentRow}", $grade12);
            $sheet->setCellValue("M{$currentRow}", $potentialScore);
            $sheet->setCellValue("N{$currentRow}", $priority);
            $sheet->setCellValue("O{$currentRow}", $legDist);
            $sheet->setCellValue("P{$currentRow}", $arrivalTime);
            $sheet->setCellValue("Q{$currentRow}", $departureTime);
            $sheet->setCellValue("R{$currentRow}", $visitStatus);
            $sheet->setCellValue("S{$currentRow}", $notes);

            if ($schoolGmaps) {
                $sheet->setCellValue("T{$currentRow}", 'Buka Maps');
                $sheet->getCell("T{$currentRow}")->getHyperlink()->setUrl($schoolGmaps);
                $sheet->getStyle("T{$currentRow}")->getFont()->getColor()->setRGB('2563EB')->setUnderline(true);
            } else {
                $sheet->setCellValue("T{$currentRow}", '-');
            }

            // Alignments
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$currentRow}:F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("K{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("L{$currentRow}:O{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("N{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("P{$currentRow}:R{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("T{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Row zebra background
            if ($currentRow % 2 == 1) {
                $sheet->getStyle("A{$currentRow}:T{$currentRow}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }

            $sheet->getRowDimension($currentRow)->setRowHeight(22);
            $currentRow++;
        }

        $lastRow = max(10, $currentRow - 1);

        // Border styling for entire data table
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle("A9:T{$lastRow}")->applyFromArray($borderStyle);

        // Auto column width
        foreach (range('A', 'T') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generate Filename
        $cleanName = Str::slug($route->name ?: 'rute-sekolah');
        $cleanDate = $route->date ? Carbon::parse($route->date)->format('Y-m-d') : date('Y-m-d');
        $filename = "Rute_Sekolah_{$cleanName}_{$cleanDate}.xlsx";

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Haversine formula distance between two coordinates in kilometers.
     */
    public function haversine($lat1, $lon1, $lat2, $lon2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
