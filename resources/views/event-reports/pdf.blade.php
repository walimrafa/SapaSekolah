<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Dokumentasi Kegiatan Support Event - {{ $campusName }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif;
        }
        body {
            background-color: #f8fafc;
            color: #0f172a;
            padding: 24px;
            font-size: 11px;
            line-height: 1.4;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            padding: 28px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0B023E;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .header-title h1 {
            font-size: 18px;
            font-weight: 900;
            color: #0B023E;
            text-transform: uppercase;
            letter-spacing: -0.3px;
        }
        .header-title p {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            margin-top: 4px;
        }
        .meta-box {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }
        .meta-box strong {
            color: #0f172a;
        }
        .tag-badge {
            display: inline-block;
            padding: 3px 8px;
            background: #fef3c7;
            color: #92400e;
            border-radius: 6px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            border-top: 3px solid #0B023E;
        }
        .kpi-card.terlaksana { border-top-color: #10b981; }
        .kpi-card.peserta { border-top-color: #f59e0b; }
        .kpi-card.leads { border-top-color: #8b5cf6; }
        .kpi-label {
            font-size: 9px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            margin: 4px 0 2px 0;
        }
        .kpi-desc {
            font-size: 9px;
            color: #94a3b8;
            font-weight: 600;
        }
        .table-section {
            margin-bottom: 24px;
        }
        .section-title {
            font-size: 13px;
            font-weight: 800;
            color: #0B023E;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th {
            background: #0B023E;
            color: #ffffff;
            font-weight: 700;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #0B023E;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .badge-status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
        }
        .status-terlaksana { background: #d1fae5; color: #065f46; }
        .status-menunggu { background: #fef3c7; color: #92400e; }
        .status-batal { background: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 28px;
            padding-top: 14px;
            border-top: 1px dashed #cbd5e1;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #94a3b8;
        }
        .print-btn-bar {
            margin-bottom: 16px;
            text-align: right;
        }
        .print-btn {
            background: #0B023E;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(11,2,62,0.3);
        }
        .print-btn:hover { background: #1e1b4b; }
        @media print {
            body { padding: 0; background: #fff; }
            .container { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .print-btn-bar { display: none; }
        }
    </style>
</head>
<body>
    <div class="print-btn-bar">
        <button class="print-btn" onclick="window.print()">Cetak / Simpan ke PDF</button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-title">
                <span class="tag-badge">Laporan Resmi Support Event</span>
                <h1>Laporan &amp; Dokumentasi Kegiatan</h1>
                <p>{{ $campusName }} • Sistem Analitik Promosi dan Relasi Sekolah</p>
            </div>
            <div class="meta-box">
                <p>Tanggal Cetak: <strong>{{ $exportDate }}</strong></p>
                <p>Status Data: <strong>Tersinkron Otomatis</strong></p>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Event Disetujui</div>
                <div class="kpi-value">{{ number_format($metrics['total_approved']) }}</div>
                <div class="kpi-desc">Proposal Kegiatan Mitra</div>
            </div>
            <div class="kpi-card terlaksana">
                <div class="kpi-label">Event Terlaksana</div>
                <div class="kpi-value">{{ number_format($metrics['total_terlaksana']) }}</div>
                <div class="kpi-desc">Lengkap Berita Acara</div>
            </div>
            <div class="kpi-card peserta">
                <div class="kpi-label">Realisasi Peserta</div>
                <div class="kpi-value">{{ number_format($metrics['total_participants']) }}</div>
                <div class="kpi-desc">Total Audiens Siswa</div>
            </div>
            <div class="kpi-card leads">
                <div class="kpi-label">Leads Siswa Masuk</div>
                <div class="kpi-value">{{ number_format($metrics['total_leads']) }}</div>
                <div class="kpi-desc">Tersinkron ke Follow Up</div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="table-section">
            <div class="section-title">
                <span>Daftar Pelaksanaan Support Event Sekolah ({{ $events->count() }} Kegiatan)</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 30px; text-align: center;">No</th>
                        <th>Agenda &amp; Sekolah</th>
                        <th>Wilayah / Kampus</th>
                        <th>Waktu &amp; Lokasi</th>
                        <th style="text-align: right;">Peserta</th>
                        <th style="text-align: right;">Leads</th>
                        <th style="text-align: center;">Status</th>
                        <th>PIC / Reviewer</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $idx => $event)
                        @php
                            $statusClass = match($event->execution_status) {
                                \App\Models\SchoolEventProposal::EXEC_SELESAI => 'status-terlaksana',
                                \App\Models\SchoolEventProposal::EXEC_SEDANG_BERJALAN => 'status-menunggu',
                                default => 'status-menunggu'
                            };
                            $statusLabel = $event->execution_status_label ?: 'Menunggu';
                        @endphp
                        <tr>
                            <td style="text-align: center;">{{ $idx + 1 }}</td>
                            <td>
                                <strong style="color: #0B023E;">{{ $event->event_name }}</strong><br>
                                <span style="color: #64748b;">{{ $event->school_name }}</span>
                                @if($event->reference_no)
                                    <br><span style="font-size: 8px; color: #94a3b8;">Ref: {{ $event->reference_no }}</span>
                                @endif
                            </td>
                            <td>
                                {{ $event->school?->district?->name ?? '-' }}<br>
                                <span style="font-size: 8px; color: #64748b;">{{ $event->campus?->name ?? '-' }}</span>
                            </td>
                            <td>
                                {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y') : '-' }}<br>
                                <span style="font-size: 8px; color: #64748b;">{{ $event->event_location ?? '-' }}</span>
                            </td>
                            <td style="text-align: right; font-weight: 700;">
                                {{ number_format($event->realized_participants ?? 0) }}
                                <div style="font-size: 8px; color: #94a3b8; font-weight: 400;">Target: {{ number_format($event->target_participants ?? 0) }}</div>
                            </td>
                            <td style="text-align: right; font-weight: 800; color: #8b5cf6;">
                                {{ number_format($event->student_leads_count ?? 0) }}
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-status {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                {{ $event->reviewer?->name ?? ($event->pic_name ?? '-') }}
                                @if($event->documentation_notes)
                                    <div style="font-size: 8px; color: #64748b; margin-top: 2px;">{{ Str::limit($event->documentation_notes, 60) }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px; color: #94a3b8;">Tidak ada data laporan kegiatan event yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <span>SAPA Sekolah Command Center • Universitas Bina Sarana Informatika</span>
            <span>Dokumen Resmi Rekapitulasi Kegiatan &amp; Dokumentasi Support Event</span>
        </div>
    </div>
</body>
</html>
