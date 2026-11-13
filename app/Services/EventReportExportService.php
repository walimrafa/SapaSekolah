<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\SchoolEventProposal;
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
use ZipArchive;

class EventReportExportService
{
    public const COLOR_NAVY_HEX = '0B023E';
    public const COLOR_CRIMSON_HEX = 'DF0B2B';
    public const COLOR_AMBER_HEX = 'F59E0B';
    public const COLOR_WHITE_HEX = 'FFFFFF';
    public const COLOR_DARK_HEX = '0F172A';

    protected function resolveActiveCampus(Request $request): ?Campus
    {
        $user = auth()->user();
        if ($user && !AppContextService::canSwitchCampus($user)) {
            $campusId = $user->campus_id ?? AppContextService::getActiveCampus()?->id;
        } else {
            $campusId = $request->filled('campus_id') && $request->campus_id !== 'ALL'
                ? $request->campus_id
                : AppContextService::getActiveCampus()?->id;
        }

        return ($campusId && $campusId !== 'ALL') ? Campus::find($campusId) : null;
    }

    /**
     * Query filtered events based on request.
     */
    public function getFilteredEvents(Request $request)
    {
        $activeCampus = $this->resolveActiveCampus($request);

        $query = SchoolEventProposal::where('status', SchoolEventProposal::STATUS_DISETUJUI)
            ->with(['school.district', 'reviewer', 'reporter', 'campus'])
            ->withCount('studentLeads')
            ->orderBy('event_date', 'desc');

        if ($activeCampus) {
            $query->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
            });
        }

        if ($request->filled('execution_status')) {
            $query->where('execution_status', $request->execution_status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('event_name', 'like', "%{$search}%")
                  ->orWhere('school_name', 'like', "%{$search}%")
                  ->orWhere('pic_name', 'like', "%{$search}%")
                  ->orWhere('event_location', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    /**
     * 1. Export Excel (.xlsx)
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $events = $this->getFilteredEvents($request);
        $activeCampus = $this->resolveActiveCampus($request);
        $campusName = $activeCampus ? $activeCampus->name : AppSettingService::get('campus_name', 'Universitas Bina Sarana Informatika');
        $fileName = 'Laporan_Dokumentasi_Support_Event_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($events, $campusName) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Laporan Kegiatan Event');

            // Header Title
            $sheet->setCellValue('A1', 'LAPORAN & DOKUMENTASI KEGIATAN SUPPORT EVENT SEKOLAH');
            $sheet->setCellValue('A2', 'Institusi: ' . $campusName . ' • Tanggal Ekspor: ' . Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB');
            $sheet->mergeCells('A1:N1');
            $sheet->mergeCells('A2:N2');

            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF0B023E');
            $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF64748B');

            // Table Headers
            $headers = [
                'No',
                'No Registrasi',
                'Nama Kegiatan Event',
                'Asal Sekolah',
                'Kecamatan',
                'Kampus',
                'Tanggal Pelaksanaan',
                'Lokasi / Tempat',
                'Target Peserta',
                'Realisasi Peserta',
                'Leads Siswa Masuk',
                'Status Pelaksanaan',
                'Reviewer / PIC',
                'Keterangan & Link Dokumentasi'
            ];

            $row = 4;
            foreach ($headers as $colIdx => $header) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->setCellValue($colLetter . $row, $header);
            }

            $sheet->getStyle('A4:N4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A4:N4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0B023E');
            $sheet->getStyle('A4:N4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension(4)->setRowHeight(28);

            $dataRow = 5;
            $no = 1;
            foreach ($events as $event) {
                $statusLabel = $event->execution_status_label ?: 'Menunggu Pelaksanaan';

                $docNote = $event->documentation_notes ?? '-';
                if ($event->drive_folder_url) {
                    $docNote .= ' | Drive: ' . $event->drive_folder_url;
                }

                $sheet->setCellValue('A' . $dataRow, $no++);
                $sheet->setCellValueExplicit('B' . $dataRow, $event->reference_no ?? '-', DataType::TYPE_STRING);
                $sheet->setCellValue('C' . $dataRow, $event->event_name ?? '-');
                $sheet->setCellValue('D' . $dataRow, $event->school_name ?? '-');
                $sheet->setCellValue('E' . $dataRow, $event->school?->district?->name ?? '-');
                $sheet->setCellValue('F' . $dataRow, $event->campus?->name ?? '-');
                $sheet->setCellValue('G' . $dataRow, $event->event_date ? Carbon::parse($event->event_date)->translatedFormat('d/m/Y') : '-');
                $sheet->setCellValue('H' . $dataRow, $event->event_location ?? '-');
                $sheet->setCellValue('I' . $dataRow, $event->target_participants ?? 0);
                $sheet->setCellValue('J' . $dataRow, $event->realized_participants ?? 0);
                $sheet->setCellValue('K' . $dataRow, $event->student_leads_count ?? 0);
                $sheet->setCellValue('L' . $dataRow, $statusLabel);
                $sheet->setCellValue('M' . $dataRow, $event->reviewer?->name ?? '-');
                $sheet->setCellValue('N' . $dataRow, $docNote);

                $dataRow++;
            }

            $lastRow = max(5, $dataRow - 1);
            $sheet->getStyle('A4:N' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFCBD5E1');
            $sheet->getStyle('A5:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G5:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I5:K' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('L5:L' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Auto-width
            for ($c = 1; $c <= 14; $c++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * 2. Export PDF Printable View
     */
    public function exportPdf(Request $request): View
    {
        $events = $this->getFilteredEvents($request);
        $activeCampus = $this->resolveActiveCampus($request);
        $campusName = $activeCampus ? $activeCampus->name : AppSettingService::get('campus_name', 'Universitas Bina Sarana Informatika (UBSI)');
        $exportDate = Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB';

        $totalApproved = $events->count();
        $totalTerlaksana = $events->where('execution_status', SchoolEventProposal::EXEC_SELESAI)->count();
        $totalRealizedParticipants = $events->sum('realized_participants');
        $totalLeads = $events->sum('student_leads_count');

        $metrics = [
            'total_approved' => $totalApproved,
            'total_terlaksana' => $totalTerlaksana,
            'total_participants' => $totalRealizedParticipants,
            'total_leads' => $totalLeads,
        ];

        return view('event-reports.pdf', compact('events', 'campusName', 'exportDate', 'metrics'));
    }

    /**
     * 3. Export PowerPoint (.pptx)
     */
    public function exportPptx(Request $request): StreamedResponse
    {
        $events = $this->getFilteredEvents($request);
        $activeCampus = $this->resolveActiveCampus($request);
        $campusName = $activeCampus ? $activeCampus->name : AppSettingService::get('campus_name', 'Universitas Bina Sarana Informatika');
        $periodName = AppContextService::getActivePeriod()?->name ?? (date('Y') . '/' . (date('Y') + 1));
        $exportDate = Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB';
        $fileName = 'Laporan_Dokumentasi_Support_Event_' . date('Ymd_His') . '.pptx';

        $totalApproved = $events->count();
        $totalTerlaksana = $events->where('execution_status', SchoolEventProposal::EXEC_SELESAI)->count();
        $totalTarget = $events->sum('target_participants');
        $totalRealized = $events->sum('realized_participants');
        $totalLeads = $events->sum('student_leads_count');

        $metrics = [
            'total_approved' => $totalApproved,
            'total_terlaksana' => $totalTerlaksana,
            'total_target' => $totalTarget,
            'total_realized' => $totalRealized,
            'total_leads' => $totalLeads,
        ];

        $binary = $this->generatePptxBinary($events, $metrics, $campusName, $periodName, $exportDate);

        return response()->streamDownload(function () use ($binary) {
            echo $binary;
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    protected function generatePptxBinary($events, array $metrics, string $campusName, string $periodName, string $exportDate): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'sapa_event_pptx_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $slideCount = 5;
        $zip->addFromString('[Content_Types].xml', $this->getContentTypesXml($slideCount));
        $zip->addFromString('_rels/.rels', $this->getRootRelsXml());
        $zip->addFromString('ppt/presentation.xml', $this->getPresentationXml($slideCount));
        $zip->addFromString('ppt/_rels/presentation.xml.rels', $this->getPresentationRelsXml($slideCount));

        $zip->addFromString('ppt/slideMasters/slideMaster1.xml', $this->getSlideMasterXml());
        $zip->addFromString('ppt/slideMasters/_rels/slideMaster1.xml.rels', $this->getSlideMasterRelsXml());
        $zip->addFromString('ppt/slideLayouts/slideLayout1.xml', $this->getSlideLayoutXml());
        $zip->addFromString('ppt/slideLayouts/_rels/slideLayout1.xml.rels', $this->getSlideLayoutRelsXml());

        // Slide 1: Cover
        $zip->addFromString('ppt/slides/slide1.xml', $this->getSlide1CoverXml($campusName, $periodName, $exportDate));
        $zip->addFromString('ppt/slides/_rels/slide1.xml.rels', $this->getSlideRelsXml());

        // Slide 2: KPI Capaian
        $zip->addFromString('ppt/slides/slide2.xml', $this->getSlide2KpiXml($metrics, $campusName));
        $zip->addFromString('ppt/slides/_rels/slide2.xml.rels', $this->getSlideRelsXml());

        // Slide 3: Rekap Pelaksanaan Event
        $zip->addFromString('ppt/slides/slide3.xml', $this->getSlide3EventListXml($events, $campusName));
        $zip->addFromString('ppt/slides/_rels/slide3.xml.rels', $this->getSlideRelsXml());

        // Slide 4: Realisasi Peserta & Leads
        $zip->addFromString('ppt/slides/slide4.xml', $this->getSlide4ParticipantsXml($metrics, $campusName));
        $zip->addFromString('ppt/slides/_rels/slide4.xml.rels', $this->getSlideRelsXml());

        // Slide 5: Rekomendasi & Penutup
        $zip->addFromString('ppt/slides/slide5.xml', $this->getSlide5ConclusionXml($campusName, $periodName, $exportDate));
        $zip->addFromString('ppt/slides/_rels/slide5.xml.rels', $this->getSlideRelsXml());

        $zip->close();
        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }

    protected function getContentTypesXml(int $slideCount): string
    {
        $slidesXml = '';
        for ($i = 1; $i <= $slideCount; $i++) {
            $slidesXml .= '<Override PartName="/ppt/slides/slide' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>'
            . '<Override PartName="/ppt/slideMasters/slideMaster1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideMaster+xml"/>'
            . '<Override PartName="/ppt/slideLayouts/slideLayout1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideLayout+xml"/>'
            . $slidesXml
            . '</Types>';
    }

    protected function getRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>'
            . '</Relationships>';
    }

    protected function getPresentationXml(int $slideCount): string
    {
        $sldIdList = '';
        for ($i = 1; $i <= $slideCount; $i++) {
            $sldIdList .= '<p:sldId id="' . (255 + $i) . '" r:id="rId' . ($i + 1) . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:presentation xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:sldMasterIdLst><p:sldMasterId id="2147483648" r:id="rId1"/></p:sldMasterIdLst>'
            . '<p:sldIdLst>' . $sldIdList . '</p:sldIdLst>'
            . '<p:sldSz cx="12192000" cy="6858000" type="screen16x9"/>'
            . '<p:notesSz cx="6858000" cy="9144000"/>'
            . '</p:presentation>';
    }

    protected function getPresentationRelsXml(int $slideCount): string
    {
        $rels = '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="slideMasters/slideMaster1.xml"/>';
        for ($i = 1; $i <= $slideCount; $i++) {
            $rels .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide' . $i . '.xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
            . '</Relationships>';
    }

    protected function getSlideMasterXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sldMaster xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr></p:spTree></p:cSld>'
            . '<p:clrMap bg1="lt1" tx1="dk1" bg2="lt2" tx2="dk2" accent1="accent1" accent2="accent2" accent3="accent3" accent4="accent4" accent5="accent5" accent6="accent6" hlink="hlink" folHlink="folHlink"/>'
            . '<p:sldLayoutIdLst><p:sldLayoutId id="2147483649" r:id="rId1"/></p:sldLayoutIdLst>'
            . '</p:sldMaster>';
    }

    protected function getSlideMasterRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
            . '</Relationships>';
    }

    protected function getSlideLayoutXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sldLayout xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" type="blank">'
            . '<p:cSld><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr></p:spTree></p:cSld>'
            . '</p:sldLayout>';
    }

    protected function getSlideLayoutRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="../slideMasters/slideMaster1.xml"/>'
            . '</Relationships>';
    }

    protected function getSlideRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
            . '</Relationships>';
    }

    protected function getSlide1CoverXml(string $campusName, string $periodName, string $exportDate): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, self::COLOR_NAVY_HEX)
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 120000, self::COLOR_CRIMSON_HEX)
            . $this->buildBoxShape(4, 'TagPill', 914400, 1400000, 3800000, 420000, self::COLOR_AMBER_HEX)
            . $this->buildTextShape(5, 'TagText', 914400, 1400000, 3800000, 420000, 'EXECUTIVE EVENT REPORT', self::COLOR_NAVY_HEX, 12, true, 'ctr')
            . $this->buildTextShape(6, 'Title', 914400, 2200000, 10363200, 1400000, 'LAPORAN & DOKUMENTASI KEGIATAN EVENT', self::COLOR_WHITE_HEX, 34, true, 'l')
            . $this->buildTextShape(7, 'Subtitle', 914400, 3700000, 10363200, 600000, strtoupper($campusName) . ' • TAHUN AKADEMIK ' . strtoupper($periodName), self::COLOR_AMBER_HEX, 18, true, 'l')
            . $this->buildTextShape(8, 'Desc', 914400, 4400000, 10363200, 600000, 'Rekapitulasi Pelaksanaan Support Event Sekolah, Realisasi Peserta, dan Penjaringan Leads Siswa', '94A3B8', 13, false, 'l')
            . $this->buildBoxShape(9, 'InfoBar', 914400, 5600000, 10363200, 700000, '1E1B4B')
            . $this->buildTextShape(10, 'InfoText', 1100000, 5700000, 10000000, 500000, 'Generated by SAPA Sekolah • Tanggal: ' . $exportDate . ' • ' . $campusName, 'E2E8F0', 11, false, 'l')
            . '</p:spTree></p:cSld></p:sld>';
    }

    protected function getSlide2KpiXml(array $metrics, string $campusName): string
    {
        $approved = number_format($metrics['total_approved'] ?? 0);
        $terlaksana = number_format($metrics['total_terlaksana'] ?? 0);
        $realized = number_format($metrics['total_realized'] ?? 0);
        $leads = number_format($metrics['total_leads'] ?? 0);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, self::COLOR_NAVY_HEX)
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, self::COLOR_CRIMSON_HEX)
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'RINGKASAN EKSEKUTIF CAPAIAN EVENT', self::COLOR_WHITE_HEX, 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, 'Capaian Pelaksanaan Support Event Sekolah & Efektivitas Penjaringan Siswa • ' . $campusName, self::COLOR_AMBER_HEX, 12, false, 'l')

            // Card 1: Event Disetujui
            . $this->buildBoxShape(6, 'Card1', 800000, 1800000, 2450000, 2200000, '1E293B')
            . $this->buildBoxShape(7, 'Card1Top', 800000, 1800000, 2450000, 50000, '3B82F6')
            . $this->buildTextShape(8, 'Card1Lbl', 950000, 1950000, 2150000, 350000, 'EVENT DISETUJUI', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(9, 'Card1Val', 950000, 2300000, 2150000, 750000, $approved, self::COLOR_WHITE_HEX, 34, true, 'l')
            . $this->buildTextShape(10, 'Card1Sub', 950000, 3200000, 2150000, 500000, 'Proposal Masuk & Lolos Review', '60A5FA', 11, true, 'l')

            // Card 2: Terlaksana
            . $this->buildBoxShape(11, 'Card2', 3450000, 1800000, 2450000, 2200000, '1E293B')
            . $this->buildBoxShape(12, 'Card2Top', 3450000, 1800000, 2450000, 50000, '10B981')
            . $this->buildTextShape(13, 'Card2Lbl', 3600000, 1950000, 2150000, 350000, 'EVENT TERLAKSANA', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(14, 'Card2Val', 3600000, 2300000, 2150000, 750000, $terlaksana, self::COLOR_WHITE_HEX, 34, true, 'l')
            . $this->buildTextShape(15, 'Card2Sub', 3600000, 3200000, 2150000, 500000, 'Lengkap Berita Acara & Foto', '34D399', 11, true, 'l')

            // Card 3: Realisasi Peserta
            . $this->buildBoxShape(16, 'Card3', 6100000, 1800000, 2450000, 2200000, '1E293B')
            . $this->buildBoxShape(17, 'Card3Top', 6100000, 1800000, 2450000, 50000, self::COLOR_AMBER_HEX)
            . $this->buildTextShape(18, 'Card3Lbl', 6250000, 1950000, 2150000, 350000, 'REALISASI PESERTA', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(19, 'Card3Val', 6250000, 2300000, 2150000, 750000, $realized, self::COLOR_WHITE_HEX, 34, true, 'l')
            . $this->buildTextShape(20, 'Card3Sub', 6250000, 3200000, 2150000, 500000, 'Total Audiens Sekolah', 'FBBF24', 11, true, 'l')

            // Card 4: Leads Siswa Masuk
            . $this->buildBoxShape(21, 'Card4', 8750000, 1800000, 2600000, 2200000, '1E293B')
            . $this->buildBoxShape(22, 'Card4Top', 8750000, 1800000, 2600000, 50000, '8B5CF6')
            . $this->buildTextShape(23, 'Card4Lbl', 8900000, 1950000, 2300000, 350000, 'LEADS SISWA MASUK', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(24, 'Card4Val', 8900000, 2300000, 2300000, 750000, $leads, self::COLOR_WHITE_HEX, 34, true, 'l')
            . $this->buildTextShape(25, 'Card4Sub', 8900000, 3200000, 2300000, 500000, 'Terjaring Masuk ke CRM Siswa', 'A78BFA', 11, true, 'l')

            // Summary Box Bottom
            . $this->buildBoxShape(26, 'SummaryBox', 800000, 4300000, 10550000, 1900000, '1E1B4B')
            . $this->buildTextShape(27, 'SumTitle', 1050000, 4450000, 10000000, 350000, 'ANALISIS EFEKTIVITAS SUPPORT EVENT:', self::COLOR_AMBER_HEX, 12, true, 'l')
            . $this->buildTextShape(28, 'SumText', 1050000, 4850000, 10000000, 1200000, "1. Seluruh kegiatan event yang didukung memiliki tingkat kehadiran rata-rata yang sangat baik.\n2. Tingkat konversi peserta menjadi leads kontak siswa siap di-follow up oleh tim marketing kampus.\n3. Dokumentasi foto dan tautan Google Drive tersimpan rapi dan dapat diverifikasi langsung.", 'E2E8F0', 11, false, 'l')

            . '</p:spTree></p:cSld></p:sld>';
    }

    protected function getSlide3EventListXml($events, string $campusName): string
    {
        $rowsXml = '';
        $topEvents = $events->take(5);
        $y = 1800000;

        foreach ($topEvents as $ev) {
            $date = $ev->event_date ? Carbon::parse($ev->event_date)->translatedFormat('d M Y') : '-';
            $sch = $ev->school_name ?? '-';
            $name = $ev->event_name ?? '-';
            $p = number_format($ev->realized_participants ?? 0);
            $l = number_format($ev->student_leads_count ?? 0);

            $rowsXml .= $this->buildBoxShape(rand(100, 9999), 'RowBg', 800000, $y, 10550000, 680000, '1E293B')
                . $this->buildTextShape(rand(100, 9999), 'RowTitle', 950000, $y + 80000, 6000000, 280000, "{$name} • {$sch}", self::COLOR_WHITE_HEX, 11, true, 'l')
                . $this->buildTextShape(rand(100, 9999), 'RowMeta', 950000, $y + 360000, 6000000, 240000, "Tanggal: {$date} | Lokasi: " . ($ev->event_location ?? '-'), '94A3B8', 9, false, 'l')
                . $this->buildTextShape(rand(100, 9999), 'RowStat', 7500000, $y + 150000, 3600000, 380000, "Peserta: {$p} Orang | Leads: {$l} Siswa", self::COLOR_AMBER_HEX, 11, true, 'r');
            $y += 780000;
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, self::COLOR_NAVY_HEX)
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, self::COLOR_CRIMSON_HEX)
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'REKAPITULASI AGENDA SUPPORT EVENT SEKOLAH', self::COLOR_WHITE_HEX, 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, 'Daftar kegiatan terlaksana dan capaian peserta lapangan • ' . $campusName, self::COLOR_AMBER_HEX, 12, false, 'l')
            . $rowsXml
            . '</p:spTree></p:cSld></p:sld>';
    }

    protected function getSlide4ParticipantsXml(array $metrics, string $campusName): string
    {
        $target = number_format($metrics['total_target'] ?? 0);
        $realized = number_format($metrics['total_realized'] ?? 0);
        $leads = number_format($metrics['total_leads'] ?? 0);
        $ratio = ($metrics['total_target'] > 0) ? round(($metrics['total_realized'] / $metrics['total_target']) * 100, 1) : 100;

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, self::COLOR_NAVY_HEX)
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, self::COLOR_CRIMSON_HEX)
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'PERBANDINGAN TARGET & REALISASI PESERTA', self::COLOR_WHITE_HEX, 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, 'Tingkat ketercapaian audiens event terhadap estimasi proposal • ' . $campusName, self::COLOR_AMBER_HEX, 12, false, 'l')

            // Box Target
            . $this->buildBoxShape(6, 'BoxTarget', 800000, 1800000, 5100000, 2400000, '1E293B')
            . $this->buildBoxShape(7, 'BoxTargetTop', 800000, 1800000, 5100000, 60000, '64748B')
            . $this->buildTextShape(8, 'TargetLbl', 1100000, 2050000, 4500000, 350000, 'ESTIMASI TARGET AUDIENS (PROPOSAL)', '94A3B8', 12, true, 'l')
            . $this->buildTextShape(9, 'TargetVal', 1100000, 2500000, 4500000, 900000, $target . ' Orang', self::COLOR_WHITE_HEX, 36, true, 'l')
            . $this->buildTextShape(10, 'TargetSub', 1100000, 3550000, 4500000, 400000, 'Berdasarkan rancangan proposal kepanitiaan', '64748B', 11, false, 'l')

            // Box Realisasi
            . $this->buildBoxShape(11, 'BoxReal', 6250000, 1800000, 5100000, 2400000, '1E293B')
            . $this->buildBoxShape(12, 'BoxRealTop', 6250000, 1800000, 5100000, 60000, '10B981')
            . $this->buildTextShape(13, 'RealLbl', 6550000, 2050000, 4500000, 350000, 'REALISASI PESERTA DI LAPANGAN', '34D399', 12, true, 'l')
            . $this->buildTextShape(14, 'RealVal', 6550000, 2500000, 4500000, 900000, $realized . ' Orang', self::COLOR_WHITE_HEX, 36, true, 'l')
            . $this->buildTextShape(15, 'RealSub', 6550000, 3550000, 4500000, 400000, "Tingkat Ketercapaian: {$ratio}% dari target", '10B981', 12, true, 'l')

            // Info Box
            . $this->buildBoxShape(16, 'BottomInfo', 800000, 4500000, 10550000, 1600000, '1E1B4B')
            . $this->buildTextShape(17, 'BInfoT', 1050000, 4700000, 10000000, 350000, 'LEADS SISWA POTENSIAL TERJARING:', self::COLOR_AMBER_HEX, 12, true, 'l')
            . $this->buildTextShape(18, 'BInfoD', 1050000, 5150000, 10000000, 800000, "Sebanyak {$leads} data kontak siswa berhasil dikumpulkan melalui barcode QR presensi & formulir registrasi booth. Seluruh kontak tersinkron langsung ke sistem Follow Up Siswa untuk penawaran beasiswa kuliah.", 'E2E8F0', 11, false, 'l')

            . '</p:spTree></p:cSld></p:sld>';
    }

    protected function getSlide5ConclusionXml(string $campusName, string $periodName, string $exportDate): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, self::COLOR_NAVY_HEX)
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, self::COLOR_CRIMSON_HEX)
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'KESIMPULAN & REKOMENDASI STRATEGIS', self::COLOR_WHITE_HEX, 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, 'Langkah tindak lanjut pemanfaatan hasil support event • ' . $campusName, self::COLOR_AMBER_HEX, 12, false, 'l')

            . $this->buildBoxShape(6, 'Pill1', 800000, 1800000, 10550000, 1100000, '1E293B')
            . $this->buildTextShape(7, 'Pill1T', 1050000, 1920000, 10000000, 300000, '1. SINKRONISASI & TIKET FOLLOW UP SISWA', self::COLOR_AMBER_HEX, 11, true, 'l')
            . $this->buildTextShape(8, 'Pill1D', 1050000, 2250000, 10000000, 500000, 'Segera distribusikan leads siswa event kepada staf marketing melalui fitur ploting tugas follow-up agar calon mahasiswa segera dihubungi sebelum momentum event memudar.', 'E2E8F0', 10, false, 'l')

            . $this->buildBoxShape(9, 'Pill2', 800000, 3050000, 10550000, 1100000, '1E293B')
            . $this->buildTextShape(10, 'Pill2T', 1050000, 3170000, 10000000, 300000, '2. HUBUNGAN KEMITRAAN DENGAN SEKOLAH MITRA', '38BDF8', 11, true, 'l')
            . $this->buildTextShape(11, 'Pill2D', 1050000, 3500000, 10000000, 500000, 'Sekolah yang telah sukses menyelenggarakan event bersama perlu dijadwalkan kunjungan lanjutan atau sosialisasi khusus kelas XII sebagai bentuk hubungan timbal balik.', 'E2E8F0', 10, false, 'l')

            . $this->buildBoxShape(12, 'Pill3', 800000, 4300000, 10550000, 1100000, '1E293B')
            . $this->buildTextShape(13, 'Pill3T', 1050000, 4420000, 10000000, 300000, '3. ARSIP DOKUMENTASI & AKUNTABILITAS SPJ', '34D399', 11, true, 'l')
            . $this->buildTextShape(14, 'Pill3D', 1050000, 4750000, 10000000, 500000, 'Pastikan seluruh foto dokumentasi pelaksanaan dan link Google Drive telah terunggah lengkap untuk audit kegiatan kemahasiswaan dan promosi PMB.', 'E2E8F0', 10, false, 'l')

            . $this->buildBoxShape(15, 'FooterBox', 800000, 5700000, 10550000, 600000, '1E1B4B')
            . $this->buildTextShape(16, 'FooterText', 1050000, 5820000, 10000000, 350000, "SAPA Sekolah • {$campusName} • Diekspor pada {$exportDate}", '94A3B8', 10, false, 'l')

            . '</p:spTree></p:cSld></p:sld>';
    }

    protected function buildBoxShape(int $id, string $name, int $x, int $y, int $cx, int $cy, string $colorHex): string
    {
        return '<p:sp>'
            . '<p:nvSpPr><p:cNvPr id="' . $id . '" name="' . $name . '"/><p:cNvSpPr><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'
            . '<p:spPr>'
            . '<a:xfrm><a:off x="' . $x . '" y="' . $y . '"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
            . '<a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val 8000"/></a:avLst></a:prstGeom>'
            . '<a:solidFill><a:srgbClr val="' . $colorHex . '"/></a:solidFill>'
            . '<a:ln w="0"><a:noFill/></a:ln>'
            . '</p:spPr>'
            . '</p:sp>';
    }

    protected function buildTextShape(int $id, string $name, int $x, int $y, int $cx, int $cy, string $text, string $colorHex, int $fontSize = 12, bool $bold = false, string $align = 'l'): string
    {
        $escaped = htmlspecialchars($text, ENT_XML1, 'UTF-8');
        $paragraphs = explode("\n", $escaped);
        $pXml = '';

        foreach ($paragraphs as $para) {
            $pXml .= '<a:p>'
                . '<a:pPr algn="' . $align . '"/>'
                . '<a:r>'
                . '<a:rPr lang="id-ID" sz="' . ($fontSize * 100) . '" b="' . ($bold ? '1' : '0') . '">'
                . '<a:solidFill><a:srgbClr val="' . $colorHex . '"/></a:solidFill>'
                . '<a:latin typeface="Segoe UI"/>'
                . '</a:rPr>'
                . '<a:t>' . $para . '</a:t>'
                . '</a:r>'
                . '</a:p>';
        }

        return '<p:sp>'
            . '<p:nvSpPr><p:cNvPr id="' . $id . '" name="' . $name . '"/><p:cNvSpPr txBox="1"><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvPr>'
            . '<p:spPr>'
            . '<a:xfrm><a:off x="' . $x . '" y="' . $y . '"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
            . '<a:noFill/>'
            . '<a:ln w="0"><a:noFill/></a:ln>'
            . '</p:spPr>'
            . '<p:txBody>'
            . '<a:bodyPr vert="horz" lIns="0" tIns="0" rIns="0" bIns="0" wrap="square"/>'
            . '<a:lstStyle/>'
            . $pXml
            . '</p:txBody>'
            . '</p:sp>';
    }
}
