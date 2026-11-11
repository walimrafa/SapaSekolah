<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Eksekutif - {{ $campusName }} - SAPA Sekolah</title>
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
            font-size: 12px;
            line-height: 1.5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            padding: 32px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0B023E;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header-title h1 {
            font-size: 20px;
            font-weight: 900;
            color: #0B023E;
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .header-title p {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            margin-top: 4px;
        }
        .badge-campus {
            background: #0B023E;
            color: #ffffff;
            padding: 6px 14px;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 11px;
            text-align: right;
        }
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }
        .card.navy::before { background: #0B023E; }
        .card.green::before { background: #10b981; }
        .card.amber::before { background: #f59e0b; }
        .card.red::before { background: #DF0B2B; }
        .card-label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
        }
        .card-value {
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
            margin: 4px 0;
        }
        .card-sub {
            font-size: 10px;
            font-weight: 700;
            color: #10b981;
        }
        .section-title {
            font-size: 13px;
            font-weight: 900;
            color: #0B023E;
            border-left: 4px solid #DF0B2B;
            padding-left: 10px;
            margin: 20px 0 12px 0;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th, td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        th {
            background-color: #0B023E;
            color: #ffffff;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .action-bar {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-print {
            background: #0B023E;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 12px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(11,2,62,0.3);
        }
        .btn-print:hover {
            background: #1e1b4b;
        }
        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px dashed #cbd5e1;
            display: flex;
            justify-content: space-between;
            color: #94a3b8;
            font-size: 10px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .container {
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .action-bar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan sebagai PDF</button>
    </div>

    <div class="container">
        <div class="header">
            <div class="header-title">
                <h1>Laporan Eksekutif Marketing &amp; PMB</h1>
                <p>{{ $campusName }} • Tahun Akademik {{ $periodName }}</p>
            </div>
            <div class="badge-campus">
                SAPA SEKOLAH COMMAND CENTER<br>
                <span style="font-size: 9px; font-weight: normal; color: #f59e0b;">{{ $exportDate }}</span>
            </div>
        </div>

        <div class="grid-4">
            <div class="card navy">
                <div class="card-label">Database Sekolah</div>
                <div class="card-value">{{ number_format($funnelData['total_schools']) }}</div>
                <div class="card-sub">{{ $funnelData['visited_schools'] }} Terkunjungi ({{ $funnelData['visited_percentage'] }}%)</div>
            </div>
            <div class="card green">
                <div class="card-label">Jadwal Sosialisasi FIX</div>
                <div class="card-value">{{ number_format($funnelData['fixed_schedules']) }}</div>
                <div class="card-sub">Sekolah Terkunci Jadwal</div>
            </div>
            <div class="card amber">
                <div class="card-label">Prospek CRM PMB</div>
                <div class="card-value">{{ number_format($funnelData['total_prospects']) }}</div>
                <div class="card-sub">{{ number_format($funnelData['registered']) }} Bayar Registrasi</div>
            </div>
            <div class="card red">
                <div class="card-label">Mahasiswa Baru (Valid)</div>
                <div class="card-value">{{ number_format($funnelData['mahasiswa']) }}</div>
                <div class="card-sub">Konversi: {{ $funnelData['conversion_rate'] }}%</div>
            </div>
        </div>

        <div class="section-title">Histori Kunjungan Sekolah Terbaru (10 Terakhir)</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th>Nama Sekolah</th>
                    <th>Kecamatan</th>
                    <th>Staf Marketing</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentVisits as $idx => $v)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td><strong>{{ $v->school->name ?? '-' }}</strong></td>
                        <td>{{ $v->school->district->name ?? '-' }}</td>
                        <td>{{ $v->user->name ?? '-' }}</td>
                        <td>{{ $v->visit_date ? \Carbon\Carbon::parse($v->visit_date)->translatedFormat('d M Y') : '-' }}</td>
                        <td>
                            <span style="font-weight: 800; text-transform: uppercase; color: {{ $v->status === 'berhasil' ? '#10b981' : '#f59e0b' }};">
                                {{ $v->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8;">Belum ada histori kunjungan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Agenda Follow Up &amp; Jadwal Sosialisasi Terkunci (10 Terakhir)</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th>Sekolah Sasaran</th>
                    <th>PIC Sekolah</th>
                    <th>Staf Marketing</th>
                    <th>Status Follow Up</th>
                    <th>Jadwal FIX</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentFollowups as $idx => $f)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td><strong>{{ $f->school->name ?? '-' }}</strong></td>
                        <td>{{ $f->pic_name ?? '-' }} ({{ $f->pic_phone ?? '-' }})</td>
                        <td>{{ $f->user->name ?? '-' }}</td>
                        <td>
                            <span style="font-weight: 800; text-transform: uppercase;">
                                {{ $f->status }}
                            </span>
                        </td>
                        <td>
                            <strong style="color: #10b981;">
                                {{ $f->fixed_schedule_date ? \Carbon\Carbon::parse($f->fixed_schedule_date)->translatedFormat('d M Y') : '-' }}
                            </strong>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8;">Belum ada jadwal sosialisasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <div>SAPA Sekolah — Universitas Bina Sarana Informatika</div>
            <div>Dokumen Resmi Sistem Informasi Manajemen Pemasaran Kampus</div>
        </div>
    </div>
</body>
</html>

