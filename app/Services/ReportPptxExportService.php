<?php

namespace App\Services;

use Carbon\Carbon;
use ZipArchive;

class ReportPptxExportService
{
    /**
     * Official UBSI SapaSekolah Presentation Palette
     */
    public const COLOR_NAVY_HEX = '0B023E';      // Primary Deep Navy
    public const COLOR_CRIMSON_HEX = 'DF0B2B';   // Accent Crimson Red
    public const COLOR_AMBER_HEX = 'F59E0B';     // Gold / Amber
    public const COLOR_DARK_HEX = '0F172A';      // Dark Slate
    public const COLOR_LIGHT_HEX = 'F8FAFC';     // Light Slate
    public const COLOR_WHITE_HEX = 'FFFFFF';     // White
    public const COLOR_CARD_HEX = '1E1B4B';      // Navy Card Fill

    /**
     * Generate complete Executive PPTX Presentation and return binary string.
     */
    public function generatePresentation(array $funnelData, array $chartsData, string $campusName, string $periodName, string $exportDate): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'sapa_pptx_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // 1. [Content_Types].xml
        $zip->addFromString('[Content_Types].xml', $this->getContentTypesXml(6));

        // 2. _rels/.rels
        $zip->addFromString('_rels/.rels', $this->getRootRelsXml());

        // 3. ppt/presentation.xml
        $zip->addFromString('ppt/presentation.xml', $this->getPresentationXml(6));

        // 4. ppt/_rels/presentation.xml.rels
        $zip->addFromString('ppt/_rels/presentation.xml.rels', $this->getPresentationRelsXml(6));

        // 5. Slide Master & Layout
        $zip->addFromString('ppt/slideMasters/slideMaster1.xml', $this->getSlideMasterXml());
        $zip->addFromString('ppt/slideMasters/_rels/slideMaster1.xml.rels', $this->getSlideMasterRelsXml());
        $zip->addFromString('ppt/slideLayouts/slideLayout1.xml', $this->getSlideLayoutXml());
        $zip->addFromString('ppt/slideLayouts/_rels/slideLayout1.xml.rels', $this->getSlideLayoutRelsXml());

        // 6. Slides 1 - 6
        $zip->addFromString('ppt/slides/slide1.xml', $this->getSlide1CoverXml($campusName, $periodName, $exportDate));
        $zip->addFromString('ppt/slides/_rels/slide1.xml.rels', $this->getSlideRelsXml());

        $zip->addFromString('ppt/slides/slide2.xml', $this->getSlide2KpiXml($funnelData, $campusName));
        $zip->addFromString('ppt/slides/_rels/slide2.xml.rels', $this->getSlideRelsXml());

        $zip->addFromString('ppt/slides/slide3.xml', $this->getSlide3FollowUpXml($chartsData, $funnelData, $campusName));
        $zip->addFromString('ppt/slides/_rels/slide3.xml.rels', $this->getSlideRelsXml());

        $zip->addFromString('ppt/slides/slide4.xml', $this->getSlide4QuestionnaireXml($chartsData, $funnelData, $campusName));
        $zip->addFromString('ppt/slides/_rels/slide4.xml.rels', $this->getSlideRelsXml());

        $zip->addFromString('ppt/slides/slide5.xml', $this->getSlide5ProspectsXml($chartsData, $funnelData, $campusName));
        $zip->addFromString('ppt/slides/_rels/slide5.xml.rels', $this->getSlideRelsXml());

        $zip->addFromString('ppt/slides/slide6.xml', $this->getSlide6ConclusionXml($campusName, $periodName, $exportDate));
        $zip->addFromString('ppt/slides/_rels/slide6.xml.rels', $this->getSlideRelsXml());

        $zip->close();

        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }

    protected function getContentTypesXml(int $slideCount): string
    {
        $overrides = '';
        for ($i = 1; $i <= $slideCount; $i++) {
            $overrides .= '<Override PartName="/ppt/slides/slide' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>'
            . '<Override PartName="/ppt/slideMasters/slideMaster1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideMaster+xml"/>'
            . '<Override PartName="/ppt/slideLayouts/slideLayout1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideLayout+xml"/>'
            . $overrides
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
        $slideList = '';
        for ($i = 1; $i <= $slideCount; $i++) {
            $id = 255 + $i;
            $rId = 'rId' . ($i + 1);
            $slideList .= '<p:sldId id="' . $id . '" r:id="' . $rId . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:presentation xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:sldMasterIdLst>'
            . '<p:sldMasterId id="2147483648" r:id="rId1"/>'
            . '</p:sldMasterIdLst>'
            . '<p:sldIdLst>'
            . $slideList
            . '</p:sldIdLst>'
            . '<p:sldSz cx="12192000" cy="6858000" type="screen16x9"/>'
            . '<p:notesSz cx="6858000" cy="9144000"/>'
            . '</p:presentation>';
    }

    protected function getPresentationRelsXml(int $slideCount): string
    {
        $rels = '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="slideMasters/slideMaster1.xml"/>';
        for ($i = 1; $i <= $slideCount; $i++) {
            $rId = 'rId' . ($i + 1);
            $rels .= '<Relationship Id="' . $rId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide' . $i . '.xml"/>';
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
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . '</p:spTree></p:cSld>'
            . '<p:clrMap bg1="lt1" tx1="dk1" bg2="lt2" tx2="dk2" accent1="accent1" accent2="accent2" accent3="accent3" accent4="accent4" accent5="accent5" accent6="accent6" hlink="hlink" folHlink="folHlink"/>'
            . '<p:sldLayoutIdLst>'
            . '<p:sldLayoutId id="2147483649" r:id="rId1"/>'
            . '</p:sldLayoutIdLst>'
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
            . '<p:sldLayout xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" type="blank" preserve="1">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . '</p:spTree></p:cSld>'
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

    /**
     * SLIDE 1: Cover Slide
     */
    protected function getSlide1CoverXml(string $campusName, string $periodName, string $exportDate): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            // Background Shape: Navy
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, self::COLOR_NAVY_HEX)
            // Accent Top Border Strip: Crimson
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 120000, self::COLOR_CRIMSON_HEX)
            // Gold Accent Pill Tag
            . $this->buildBoxShape(4, 'TagPill', 914400, 1400000, 3600000, 420000, self::COLOR_AMBER_HEX)
            . $this->buildTextShape(5, 'TagText', 914400, 1400000, 3600000, 420000, 'EXECUTIVE MARKETING REPORT', self::COLOR_NAVY_HEX, 12, true, 'ctr')
            // Title & Subtitle
            . $this->buildTextShape(6, 'Title', 914400, 2200000, 10363200, 1400000, 'LAPORAN CAPAIAN MARKETING & PMB', self::COLOR_WHITE_HEX, 36, true, 'l')
            . $this->buildTextShape(7, 'Subtitle', 914400, 3700000, 10363200, 600000, strtoupper($campusName) . ' • TAHUN AKADEMIK ' . strtoupper($periodName), self::COLOR_AMBER_HEX, 18, true, 'l')
            . $this->buildTextShape(8, 'Desc', 914400, 4400000, 10363200, 600000, 'Sistem Analitik Terpadu Relasi Sekolah, Follow Up Sosialisasi, dan Konversi Prospek Mahasiswa Baru', '94A3B8', 13, false, 'l')
            // Bottom Info Bar
            . $this->buildBoxShape(9, 'InfoBar', 914400, 5600000, 10363200, 700000, '1E1B4B')
            . $this->buildTextShape(10, 'InfoText', 1100000, 5700000, 10000000, 500000, 'Generated by SAPA Sekolah Command Center • Tanggal: ' . $exportDate . ' • ' . $campusName, 'E2E8F0', 11, false, 'l')
            . '</p:spTree></p:cSld></p:sld>';
    }

    /**
     * SLIDE 2: Ringkasan Eksekutif & KPI Funnel
     */
    protected function getSlide2KpiXml(array $funnel, string $campusName): string
    {
        $totalSchools = number_format($funnel['total_schools'] ?? 0);
        $visited = number_format($funnel['visited_schools'] ?? 0);
        $pctVisited = $funnel['visited_percentage'] ?? 0;
        $fixedSchedules = number_format($funnel['fixed_schedules'] ?? 0);
        $totalProspects = number_format($funnel['total_prospects'] ?? 0);
        $registered = number_format($funnel['registered'] ?? 0);
        $mahasiswa = number_format($funnel['mahasiswa'] ?? 0);
        $convRate = $funnel['conversion_rate'] ?? 0;

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            // Background
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, self::COLOR_NAVY_HEX)
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, self::COLOR_CRIMSON_HEX)
            // Slide Header
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'RINGKASAN EKSEKUTIF & CAPAIAN KPI UTAMA', self::COLOR_WHITE_HEX, 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, 'Performa Operasional Pemasaran, Sosialisasi Sekolah, dan Funnel Konversi PMB • ' . $campusName, self::COLOR_AMBER_HEX, 12, false, 'l')

            // 4 Primary Metric Cards across slide
            // Card 1: Sekolah
            . $this->buildBoxShape(6, 'Card1', 800000, 1800000, 2450000, 2100000, '1E293B')
            . $this->buildBoxShape(7, 'Card1Top', 800000, 1800000, 2450000, 50000, '3B82F6')
            . $this->buildTextShape(8, 'Card1Lbl', 950000, 1950000, 2150000, 350000, 'DATABASE SEKOLAH', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(9, 'Card1Val', 950000, 2300000, 2150000, 750000, $totalSchools, self::COLOR_WHITE_HEX, 32, true, 'l')
            . $this->buildTextShape(10, 'Card1Sub', 950000, 3200000, 2150000, 500000, "{$visited} Terkunjungi ({$pctVisited}%)", '60A5FA', 11, true, 'l')

            // Card 2: Jadwal Sosialisasi FIX
            . $this->buildBoxShape(11, 'Card2', 3450000, 1800000, 2450000, 2100000, '1E293B')
            . $this->buildBoxShape(12, 'Card2Top', 3450000, 1800000, 2450000, 50000, '10B981')
            . $this->buildTextShape(13, 'Card2Lbl', 3600000, 1950000, 2150000, 350000, 'JADWAL SOSIALISASI FIX', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(14, 'Card2Val', 3600000, 2300000, 2150000, 750000, $fixedSchedules, self::COLOR_WHITE_HEX, 32, true, 'l')
            . $this->buildTextShape(15, 'Card2Sub', 3600000, 3200000, 2150000, 500000, "Sekolah Terkunci Jadwal", '34D399', 11, true, 'l')

            // Card 3: Prospek Siswa
            . $this->buildBoxShape(16, 'Card3', 6100000, 1800000, 2450000, 2100000, '1E293B')
            . $this->buildBoxShape(17, 'Card3Top', 6100000, 1800000, 2450000, 50000, self::COLOR_AMBER_HEX)
            . $this->buildTextShape(18, 'Card3Lbl', 6250000, 1950000, 2150000, 350000, 'TOTAL PROSPEK CRM', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(19, 'Card3Val', 6250000, 2300000, 2150000, 750000, $totalProspects, self::COLOR_WHITE_HEX, 32, true, 'l')
            . $this->buildTextShape(20, 'Card3Sub', 6250000, 3200000, 2150000, 500000, "{$registered} Masuk Registrasi", 'FBBF24', 11, true, 'l')

            // Card 4: Mahasiswa Baru
            . $this->buildBoxShape(21, 'Card4', 8750000, 1800000, 2600000, 2100000, '1E293B')
            . $this->buildBoxShape(22, 'Card4Top', 8750000, 1800000, 2600000, 50000, self::COLOR_CRIMSON_HEX)
            . $this->buildTextShape(23, 'Card4Lbl', 8900000, 1950000, 2300000, 350000, 'MAHASISWA BARU (VALID)', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(24, 'Card4Val', 8900000, 2300000, 2300000, 750000, $mahasiswa, self::COLOR_WHITE_HEX, 32, true, 'l')
            . $this->buildTextShape(25, 'Card4Sub', 8900000, 3200000, 2300000, 500000, "Konversi Akhir: {$convRate}%", 'F87171', 11, true, 'l')

            // Bottom Highlight Box
            . $this->buildBoxShape(26, 'HighlightBox', 800000, 4300000, 10550000, 1900000, '0F172A')
            . $this->buildTextShape(27, 'HLTitle', 1050000, 4450000, 10000000, 350000, 'STRATEGI PIPELINE MARKETING KAMPUS', self::COLOR_AMBER_HEX, 13, true, 'l')
            . $this->buildTextShape(28, 'HLBody', 1050000, 4850000, 10000000, 1200000,
                "• Integrasi operasional lapangan memprioritaskan sekolah Top Prioritas (A & B) di seluruh kecamatan binaan.\n"
                . "• Agenda kunjungan yang telah terlaksana otomatis tercatat sebagai histori relasi aktif kampus.\n"
                . "• Peningkatan rasio closing difokuskan pada follow up kuisioner minat beasiswa dan pendaftaran aplikasi PMB.",
                'CBD5E1', 11, false, 'l')
            . '</p:spTree></p:cSld></p:sld>';
    }

    /**
     * SLIDE 3: Follow Up Sosialisasi & Jadwal FIX
     */
    protected function getSlide3FollowUpXml(array $chartsData, array $funnel, string $campusName): string
    {
        $fixedCount = number_format($funnel['fixed_schedules'] ?? 0);
        $totalFollowUps = number_format($funnel['total_followups'] ?? 0);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, self::COLOR_NAVY_HEX)
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, '10B981')
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'ANALISIS JADWAL SOSIALISASI FIX & FOLLOW UP', self::COLOR_WHITE_HEX, 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, 'Progres Penguncian Jadwal Presentasi PMB dan Sebaran Tim Marketing • ' . $campusName, '34D399', 12, false, 'l')

            // Left Box: Rekap Status
            . $this->buildBoxShape(6, 'LeftBox', 800000, 1700000, 5100000, 4500000, '1E293B')
            . $this->buildTextShape(7, 'LBTitle', 1050000, 1900000, 4600000, 400000, 'DISTRIBUSI STATUS FOLLOW UP', self::COLOR_AMBER_HEX, 14, true, 'l')
            . $this->buildTextShape(8, 'LBBody', 1050000, 2400000, 4600000, 3500000,
                "Total Agenda Follow Up: {$totalFollowUps} sekolah\n\n"
                . "✓ Jadwal Sosialisasi FIX: {$fixedCount} sekolah\n"
                . "✓ Tahap Negosiasi / Progress: Aktif dikawal staf marketing\n"
                . "✓ Syarat Proposal Masuk: Disinkronkan dengan event sekolah\n\n"
                . "Target Audiens Siswa Terkunci: Memaksimalkan paparan program beasiswa dan keunggulan program studi UBSI langsung di hadapan siswa kelas 12.",
                'E2E8F0', 12, false, 'l')

            // Right Box: Tindak Lanjut Lapangan
            . $this->buildBoxShape(9, 'RightBox', 6250000, 1700000, 5100000, 4500000, '1E293B')
            . $this->buildTextShape(10, 'RBTitle', 6500000, 1900000, 4600000, 400000, 'POIN PENGUATAN OPERASIONAL', '60A5FA', 14, true, 'l')
            . $this->buildTextShape(11, 'RBBody', 6500000, 2400000, 4600000, 3500000,
                "1. Manajemen Rute Marketing Otomatis:\n"
                . "   Memanfaatkan klaster geografis untuk efisiensi jarak tempuh mobil marketing antar sekolah satu kecamatan.\n\n"
                . "2. Penugasan PIC Berbasis Wilayah:\n"
                . "   Staf marketing dialokasikan sesuai teritori kampus binaan tanpa tumpang tindih.\n\n"
                . "3. Tindak Lanjut Pasca Sosialisasi:\n"
                . "   Pengumpulan kuisioner siswa secara digital via QR code instan dan tautan form publik kampus.",
                'E2E8F0', 12, false, 'l')
            . '</p:spTree></p:cSld></p:sld>';
    }

    /**
     * SLIDE 4: Kuisioner Siswa & Minat Beasiswa
     */
    protected function getSlide4QuestionnaireXml(array $chartsData, array $funnel, string $campusName): string
    {
        $totalResp = number_format($funnel['total_questionnaires'] ?? 0);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, self::COLOR_NAVY_HEX)
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, self::COLOR_AMBER_HEX)
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'ANALISIS HASIL KUISIONER & MINAT BEASISWA', self::COLOR_WHITE_HEX, 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, 'Preferensi Calon Mahasiswa, Minat Beasiswa, dan Program Studi Pilihan • ' . $campusName, self::COLOR_AMBER_HEX, 12, false, 'l')

            // 3 Column Metric Panels
            // Col 1: Responden
            . $this->buildBoxShape(6, 'Col1', 800000, 1700000, 3350000, 4500000, '1E293B')
            . $this->buildTextShape(7, 'Col1H', 1000000, 1950000, 2950000, 350000, 'TOTAL RESPONDEN', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(8, 'Col1V', 1000000, 2350000, 2950000, 650000, $totalResp, self::COLOR_WHITE_HEX, 28, true, 'l')
            . $this->buildTextShape(9, 'Col1D', 1000000, 3100000, 2950000, 2800000,
                "Responden berasal dari siswa kelas 12 SLTA yang mengikuti sosialisasi PMB.\n\n"
                . "Pengisian dilakukan langsung secara mandiri melalui form online dan verifikasi tim lapangan.",
                'CBD5E1', 11, false, 'l')

            // Col 2: Minat Beasiswa
            . $this->buildBoxShape(10, 'Col2', 4400000, 1700000, 3350000, 4500000, '1E293B')
            . $this->buildTextShape(11, 'Col2H', 4600000, 1950000, 2950000, 350000, 'PROGRAM BEASISWA', '34D399', 11, true, 'l')
            . $this->buildTextShape(12, 'Col2V', 4600000, 2350000, 2950000, 650000, 'TINGGI', '34D399', 28, true, 'l')
            . $this->buildTextShape(13, 'Col2D', 4600000, 3100000, 2950000, 2800000,
                "Mayoritas responden menunjukkan minat kuat pada beasiswa prestasi, KIP-K, dan beasiswa jalur rapor UBSI.\n\n"
                . "Faktor biaya dan beasiswa menjadi pertimbangan terbesar bagi siswa dalam melanjutkan ke perguruan tinggi.",
                'CBD5E1', 11, false, 'l')

            // Col 3: Preferensi Kampus
            . $this->buildBoxShape(14, 'Col3', 8000000, 1700000, 3350000, 4500000, '1E293B')
            . $this->buildTextShape(15, 'Col3H', 8200000, 1950000, 2950000, 350000, 'PREFERENSI PRODI', '60A5FA', 11, true, 'l')
            . $this->buildTextShape(16, 'Col3V', 8200000, 2350000, 2950000, 650000, 'IT & BISNIS', '60A5FA', 28, true, 'l')
            . $this->buildTextShape(17, 'Col3D', 8200000, 3100000, 2950000, 2800000,
                "Program studi bidang Teknologi Informasi (Sistem Informasi, Informatika) dan Manajemen menjadi pilihan teratas responden.\n\n"
                . "Peluang karir dan kemudahan penempatan kerja menjadi alasan utama pemilihan prodi.",
                'CBD5E1', 11, false, 'l')
            . '</p:spTree></p:cSld></p:sld>';
    }

    /**
     * SLIDE 5: Prospek CRM & Pipeline Mahasiswa Baru
     */
    protected function getSlide5ProspectsXml(array $chartsData, array $funnel, string $campusName): string
    {
        $totalProspects = number_format($funnel['total_prospects'] ?? 0);
        $registered = number_format($funnel['registered'] ?? 0);
        $mahasiswa = number_format($funnel['mahasiswa'] ?? 0);
        $convRate = $funnel['conversion_rate'] ?? 0;

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, self::COLOR_NAVY_HEX)
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, self::COLOR_CRIMSON_HEX)
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'PIPELINE KONVERSI PROSPEK PMB', self::COLOR_WHITE_HEX, 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, '5 Tahap Konversi Calon Mahasiswa Menuju Mahasiswa Baru Tervalidasi • ' . $campusName, 'F87171', 12, false, 'l')

            // 5 Funnel Step Bars
            . $this->buildBoxShape(6, 'Step1', 800000, 1700000, 10550000, 800000, '1E293B')
            . $this->buildBoxShape(7, 'Step1Num', 800000, 1700000, 800000, 800000, '3B82F6')
            . $this->buildTextShape(8, 'Step1NumT', 800000, 1700000, 800000, 800000, '01', self::COLOR_WHITE_HEX, 16, true, 'ctr')
            . $this->buildTextShape(9, 'Step1Text', 1800000, 1850000, 9000000, 500000, "Tahap 1: Pendaftaran Akun PMB — {$totalProspects} akun terdaftar di sistem", 'E2E8F0', 13, true, 'l')

            . $this->buildBoxShape(10, 'Step2', 800000, 2600000, 10550000, 800000, '1E293B')
            . $this->buildBoxShape(11, 'Step2Num', 800000, 2600000, 800000, 800000, 'F59E0B')
            . $this->buildTextShape(12, 'Step2NumT', 800000, 2600000, 800000, 800000, '02', self::COLOR_NAVY_HEX, 16, true, 'ctr')
            . $this->buildTextShape(13, 'Step2Text', 1800000, 2750000, 9000000, 500000, "Tahap 2: Pembayaran Biaya Registrasi — {$registered} calon mahasiswa menyelesaikan registrasi", 'E2E8F0', 13, true, 'l')

            . $this->buildBoxShape(14, 'Step3', 800000, 3500000, 10550000, 800000, '1E293B')
            . $this->buildBoxShape(15, 'Step3Num', 800000, 3500000, 800000, 800000, '8B5CF6')
            . $this->buildTextShape(16, 'Step3NumT', 800000, 3500000, 800000, 800000, '03', self::COLOR_WHITE_HEX, 16, true, 'ctr')
            . $this->buildTextShape(17, 'Step3Text', 1800000, 3650000, 9000000, 500000, "Tahap 3 & 4: Ujian Saringan & Daftar Ulang — Proses seleksi akademik & verifikasi jalur beasiswa", 'E2E8F0', 13, true, 'l')

            . $this->buildBoxShape(18, 'Step5', 800000, 4400000, 10550000, 800000, '1E293B')
            . $this->buildBoxShape(19, 'Step5Num', 800000, 4400000, 800000, 800000, self::COLOR_CRIMSON_HEX)
            . $this->buildTextShape(20, 'Step5NumT', 800000, 4400000, 800000, 800000, '05', self::COLOR_WHITE_HEX, 16, true, 'ctr')
            . $this->buildTextShape(21, 'Step5Text', 1800000, 4550000, 9000000, 500000, "Tahap 5: Mahasiswa Baru Resmi (Valid) — {$mahasiswa} mahasiswa siap mengikuti perkuliahan", 'FCA5A5', 13, true, 'l')

            // Conversion rate summary note
            . $this->buildBoxShape(22, 'RateBox', 800000, 5350000, 10550000, 900000, '0F172A')
            . $this->buildTextShape(23, 'RateText', 1050000, 5550000, 10000000, 500000, "Tingkat Konversi Akhir (Closing Rate): {$convRate}% • Data dimonitor realtime per kampus tanpa pencampuran antar cabang.", self::COLOR_AMBER_HEX, 12, true, 'l')
            . '</p:spTree></p:cSld></p:sld>';
    }

    /**
     * SLIDE 6: Kesimpulan & Penutup
     */
    protected function getSlide6ConclusionXml(string $campusName, string $periodName, string $exportDate): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, self::COLOR_NAVY_HEX)
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 120000, self::COLOR_CRIMSON_HEX)
            . $this->buildTextShape(4, 'Title', 914400, 1800000, 10363200, 800000, 'KESIMPULAN & REKOMENDASI TINDAK LANJUT', self::COLOR_WHITE_HEX, 28, true, 'l')
            . $this->buildTextShape(5, 'Sub', 914400, 2600000, 10363200, 400000, 'Rencana Aksi Berkelanjutan Tim Marketing ' . $campusName, self::COLOR_AMBER_HEX, 14, true, 'l')
            . $this->buildTextShape(6, 'Recs', 914400, 3200000, 10363200, 2200000,
                "1. Percepatan Sosialisasi Sekolah Sisa:\n"
                . "   Memanfaatkan fitur rute marketing otomatis untuk menyisir sisa sekolah target di kecamatan binaan.\n\n"
                . "2. Intensifikasi Follow Up Minat Beasiswa:\n"
                . "   Mengarahkan responden kuisioner yang berminat beasiswa untuk segera mendaftar akun PMB.\n\n"
                . "3. Penguatan Closing Mahasiswa Baru:\n"
                . "   Mengawal calon mahasiswa yang sudah membayar registrasi hingga menuntaskan daftar ulang.",
                'CBD5E1', 12, false, 'l')
            . $this->buildBoxShape(7, 'Footer', 914400, 5600000, 10363200, 700000, '1E1B4B')
            . $this->buildTextShape(8, 'FooterText', 1100000, 5750000, 10000000, 400000, "SAPA Sekolah • Universitas Bina Sarana Informatika • Diekspor pada {$exportDate}", 'E2E8F0', 11, false, 'l')
            . '</p:spTree></p:cSld></p:sld>';
    }

    /**
     * Helper to build OpenXML Box / Shape
     */
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

    /**
     * Helper to build OpenXML Text Shape
     */
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

