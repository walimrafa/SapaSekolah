<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Data Siswa Hasil Support Event - {{ $campusName }}</title>
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
            max-width: 1050px;
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
            background: #ede9fe;
            color: #6d28d9;
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
        .kpi-card.beasiswa { border-top-color: #10b981; }
        .kpi-card.belum { border-top-color: #f59e0b; }
        .kpi-card.terfollowup { border-top-color: #3b82f6; }
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
            padding: 7px 9px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .badge-pill {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
        }
        .beasiswa-ya { background: #d1fae5; color: #065f46; font-weight: 800; }
        .beasiswa-tidak { background: #f1f5f9; color: #475569; }
        .status-belum { background: #fef3c7; color: #92400e; }
        .status-sudah { background: #e0e7ff; color: #3730a3; }
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
                <span class="tag-badge">Database Leads Siswa Event</span>
                <h1>Data Siswa Hasil Support Event</h1>
                <p>{{ $campusName }} • Sistem Analitik Penjaringan Calon Mahasiswa Baru</p>
            </div>
            <div class="meta-box">
                <p>Tanggal Cetak: <strong>{{ $exportDate }}</strong></p>
                <p>Status: <strong>Tersinkron ke Follow Up Siswa</strong></p>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Total Siswa Terdaftar</div>
                <div class="kpi-value">{{ number_format($metrics['total']) }}</div>
                <div class="kpi-desc">Semua Leads Booth Event</div>
            </div>
            <div class="kpi-card beasiswa">
                <div class="kpi-label">Minat Beasiswa Kuliah</div>
                <div class="kpi-value">{{ number_format($metrics['scholarship_yes']) }}</div>
                <div class="kpi-desc">Prioritas Penawaran Admisi</div>
            </div>
            <div class="kpi-card belum">
                <div class="kpi-label">Belum Dihubungi</div>
                <div class="kpi-value">{{ number_format($metrics['belum_dihubungi']) }}</div>
                <div class="kpi-desc">Menunggu Antrian Kontak</div>
            </div>
            <div class="kpi-card terfollowup">
                <div class="kpi-label">Sudah Ter-Follow Up</div>
                <div class="kpi-value">{{ number_format($metrics['terfollowup']) }}</div>
                <div class="kpi-desc">Telah Dihubungi Tim CRM</div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="table-section">
            <div class="section-title">
                <span>Daftar Siswa Terdaftar ({{ $students->count() }} Data)</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 25px; text-align: center;">No</th>
                        <th>Nama Siswa &amp; JK</th>
                        <th>Asal Sekolah &amp; Jurusan</th>
                        <th>No. WhatsApp / HP</th>
                        <th>Nama &amp; Kontak Wali</th>
                        <th style="text-align: center;">Beasiswa</th>
                        <th>Event Asal</th>
                        <th style="text-align: center;">Status Follow Up</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $idx => $s)
                        <tr>
                            <td style="text-align: center;">{{ $idx + 1 }}</td>
                            <td>
                                <strong style="color: #0B023E;">{{ $s->student_name }}</strong><br>
                                <span style="font-size: 8px; color: #64748b;">JK: {{ $s->gender_label ?: '-' }}</span>
                            </td>
                            <td>
                                {{ $s->school_display_name ?: '-' }}<br>
                                <span style="font-size: 8px; color: #64748b;">{{ $s->student_class ?: '-' }} - {{ $s->student_major ?: '-' }}</span>
                            </td>
                            <td>
                                <strong>{{ $s->student_phone }}</strong>
                                @if($s->student_email)
                                    <br><span style="font-size: 8px; color: #64748b;">{{ $s->student_email }}</span>
                                @endif
                            </td>
                            <td>
                                {{ $s->guardian_name ?: '-' }}<br>
                                <span style="font-size: 8px; color: #64748b;">{{ $s->guardian_phone ?: '-' }}</span>
                            </td>
                            <td style="text-align: center;">
                                @if($s->scholarship_interest === 'ya')
                                    <span class="badge-pill beasiswa-ya">Berminat</span>
                                @else
                                    <span class="badge-pill beasiswa-tidak">Reguler</span>
                                @endif
                            </td>
                            <td>
                                @if($s->eventProposal)
                                    <strong style="color: #4338ca;">{{ $s->eventProposal->event_name }}</strong><br>
                                    <span style="font-size: 8px; color: #64748b;">Ref: {{ $s->eventProposal->reference_no }}</span>
                                @else
                                    <span style="color: #64748b;">{{ $s->college_interest_notes ?: 'Support Event' }}</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge-pill {{ $s->followup_status === \App\Models\StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI ? 'status-belum' : 'status-sudah' }}">
                                    {{ $s->followup_status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px; color: #94a3b8;">Tidak ada data siswa hasil event yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <span>SAPA Sekolah Command Center • Universitas Bina Sarana Informatika</span>
            <span>Dokumen Resmi Rekapitulasi Data Siswa Hasil Support Event</span>
        </div>
    </div>
</body>
</html>

