<?php

namespace App\Http\Controllers;

use App\Services\SchoolImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolImportController extends Controller
{
    public function __construct(
        protected SchoolImportService $importService
    ) {}

    /**
     * Handle Excel/CSV file upload import for School Data.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ]);

        $result = $this->importService->import($request->file('excel_file'));

        $toastType = $result['status'] ? 'success' : 'danger';

        return redirect()->route('schools.index')
            ->with('toast', [
                'type' => $toastType,
                'message' => $result['message'],
            ])
            ->with('school_import_errors', $result['errors'] ?? []);
    }

    /**
     * Download Excel/CSV Sample Template for School Data Import.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_sekolah_sapa_sekolah.csv"',
        ];

        $columns = [
            'NPSN',
            'Nama Sekolah',
            'Jenjang',
            'Status',
            'Kecamatan',
            'Alamat',
            'Latitude',
            'Longitude',
            'Jumlah Siswa',
            'Siswa Kelas 12',
            'Guru BK',
            'No HP BK'
        ];

        $samples = [
            [
                '20237453', 'SMAN 1 CIAMPEL', 'SMA', 'NEGERI', 'Ciampel',
                'Jl. Raya Kutapohaci No.1', '-6.4325', '107.3181', '850', '280', 'Bu Ficky', '085776111078'
            ],
            [
                '70008409', 'SMK SURYACIPTA', 'SMK', 'SWASTA', 'Ciampel',
                'Kawasan Industri Suryacipta', '-6.4150', '107.3290', '600', '190', 'Pak Haris', '081234567890'
            ],
            [
                '20237454', 'MA NIHAYATUL AMAL RAWAMERTA', 'MA', 'SWASTA', 'Rawamerta',
                'Jl. Raya Rawamerta', '-6.2510', '107.3510', '920', '310', 'Pak Endang', '085280497173'
            ],
        ];

        $callback = function () use ($columns, $samples) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($samples as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export complete Master School Data to Excel (.xls) file.
     */
    public function exportData(Request $request): StreamedResponse
    {
        $filename = 'data_master_sekolah_sapa_sekolah_' . date('Y-m-d_H-i-s') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ];

        $query = \App\Models\School::with(['schoolType', 'accreditation', 'district', 'village', 'primaryContact']);

        // Apply filters if passed from front-end
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('npsn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('school_type_id')) {
            $query->where('school_type_id', $request->school_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', strtolower($request->status));
        }

        if ($request->filled('priority_level')) {
            $query->where('priority_level', strtoupper($request->priority_level));
        }

        if ($request->filled('accreditation_id')) {
            $query->where('accreditation_id', $request->accreditation_id);
        }

        $schools = $query->orderBy('name', 'asc')->get();

        $columns = [
            'No',
            'NPSN',
            'Nama Sekolah',
            'Jenjang / Type',
            'Status Sekolah',
            'Kecamatan',
            'Desa / Kelurahan',
            'Alamat',
            'Kode Pos',
            'No Telepon',
            'Email',
            'Website',
            'Instagram',
            'Akreditasi',
            'Latitude',
            'Longitude',
            'Link Google Maps',
            'Link Navigasi Waze',
            'Jarak dari Kampus (KM)',
            'Total Siswa',
            'Siswa Kelas 10',
            'Siswa Kelas 11',
            'Siswa Kelas 12',
            'Skor Potensi',
            'Prioritas',
            'Kontak Utama',
            'No HP Kontak Utama',
            'Status Sistem',
        ];

        $callback = function () use ($columns, $schools) {
            $file = fopen('php://output', 'w');
            
            fputs($file, "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><style>th{background-color:#1e3a5f;color:#ffffff;font-weight:bold;text-align:center;} td{vertical-align:top;}</style></head><body>");
            fputs($file, "<table border=\"1\">");
            fputs($file, "<thead><tr>");

            foreach ($columns as $col) {
                fputs($file, "<th>" . htmlspecialchars($col) . "</th>");
            }
            fputs($file, "</tr></thead><tbody>");

            foreach ($schools as $index => $s) {
                fputs($file, "<tr>");
                fputs($file, "<td align=\"center\">" . ($index + 1) . "</td>");
                fputs($file, "<td style=\"mso-number-format:'\@';\">" . htmlspecialchars($s->npsn ?? '-') . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->name) . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->schoolType ? $s->schoolType->name : '-') . "</td>");
                fputs($file, "<td>" . htmlspecialchars(strtoupper($s->status ?? '-')) . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->district ? $s->district->name : '-') . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->village ? $s->village->name : '-') . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->address ?? '-') . "</td>");
                fputs($file, "<td style=\"mso-number-format:'\@';\">" . htmlspecialchars($s->postal_code ?? '-') . "</td>");
                fputs($file, "<td style=\"mso-number-format:'\@';\">" . htmlspecialchars($s->phone ?? '-') . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->email ?? '-') . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->website ?? '-') . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->instagram ?? '-') . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->accreditation ? $s->accreditation->code : '-') . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->latitude ?? '-') . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->longitude ?? '-') . "</td>");
                fputs($file, "<td><a href=\"" . htmlspecialchars($s->gmaps_search_url) . "\">Google Maps</a></td>");
                fputs($file, "<td><a href=\"" . htmlspecialchars($s->waze_url) . "\">Navigasi Waze</a></td>");
                fputs($file, "<td align=\"right\">" . htmlspecialchars($s->distance_from_campus ?? '0') . "</td>");
                fputs($file, "<td align=\"right\">" . htmlspecialchars($s->student_count ?? 0) . "</td>");
                fputs($file, "<td align=\"right\">" . htmlspecialchars($s->grade_10_count ?? 0) . "</td>");
                fputs($file, "<td align=\"right\">" . htmlspecialchars($s->grade_11_count ?? 0) . "</td>");
                fputs($file, "<td align=\"right\">" . htmlspecialchars($s->grade_12_count ?? 0) . "</td>");
                fputs($file, "<td align=\"right\">" . htmlspecialchars($s->potential_score ?? '0') . "</td>");
                fputs($file, "<td align=\"center\">" . htmlspecialchars($s->priority_level ?? 'C') . "</td>");
                fputs($file, "<td>" . htmlspecialchars($s->primaryContact ? $s->primaryContact->name . ($s->primaryContact->position ? ' (' . $s->primaryContact->position . ')' : '') : '-') . "</td>");
                fputs($file, "<td style=\"mso-number-format:'\@';\">" . htmlspecialchars($s->primaryContact ? $s->primaryContact->phone : '-') . "</td>");
                fputs($file, "<td>" . htmlspecialchars(strtoupper($s->status_school ?? 'AKTIF')) . "</td>");
                fputs($file, "</tr>");
            }

            fputs($file, "</tbody></table></body></html>");
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
