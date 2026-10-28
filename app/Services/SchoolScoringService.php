<?php

namespace App\Services;

use App\Models\School;

class SchoolScoringService
{
    // Koordinat Kampus UBSI Karawang: Jl. Banten No.1, Karangpawitan, Karawang Barat
    public const UBSI_LATITUDE = -6.3054;
    public const UBSI_LONGITUDE = 107.2995;
    public const UBSI_ADDRESS = 'Jalan Banten No.1, Kelurahan Karangpawitan, Kecamatan Karawang Barat, Kabupaten Karawang, Jawa Barat 41351';

    /**
     * Hitung Jarak dalam Kilometer dari Kampus UBSI Karawang menggunakan Rumus Haversine.
     */
    public function calculateDistanceFromCampus(float $latitude, float $longitude): float
    {
        $earthRadius = 6371; // Radius bumi dalam kilometer

        $dLat = deg2rad($latitude - self::UBSI_LATITUDE);
        $dLng = deg2rad($longitude - self::UBSI_LONGITUDE);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad(self::UBSI_LATITUDE)) * cos(deg2rad($latitude)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(min(9999.99, max(0.0, $earthRadius * $c)), 2);
    }

    /**
     * Recalculate and update priority scores for all schools based on UBSI Karawang criteria:
     * 1. Jarak Terdekat dengan Kampus UBSI Karawang (25%)
     * 2. Sekolah Negeri (20%)
     * 3. Siswa Kelas XII / Rumbel Terbanyak (30%)
     * 4. Jumlah Mahasiswa Baru dari Sekolah (25%)
     *
     * TOP 30 SEKOLAH TERATAS SECARA PRESISI DIJADIKAN PRIORITAS A (One Campus Thirty School)
     */
    public function recalculateAllSchoolScores(): void
    {
        $schools = School::with([
            'prospects:id,school_id,status',
            'studentIntakes:id,school_id,student_count',
        ])->get();
        $maxGrade12 = max($schools->max('grade_12_count') ?: 1, 1);
        
        // Use the larger of individual student records and imported school totals to avoid double-counting.
        $studentCounts = [];
        $maxUBSIStudents = 1;
        foreach ($schools as $school) {
            $individualCount = $school->prospects->where('status', 'mahasiswa')->count();
            $reportedCount = $school->studentIntakes->sum('student_count');
            $ubsiCount = max($individualCount, $reportedCount);
            $studentCounts[$school->id] = $ubsiCount;
            if ($ubsiCount > $maxUBSIStudents) {
                $maxUBSIStudents = $ubsiCount;
            }
        }

        // Calculate weighted score for each school
        $scoresData = [];

        foreach ($schools as $school) {
            $distanceKm = 5.0; // default
            if ($school->latitude && $school->longitude) {
                $distanceKm = $this->calculateDistanceFromCampus((float)$school->latitude, (float)$school->longitude);
            }

            // 1. Score Jarak (Makin dekat = skor makin tinggi 100)
            $distanceScore = max(20, 100 - ($distanceKm * 2.8));

            // 2. Score Status (Negeri = 100, Swasta = 65)
            $statusScore = strtolower($school->status) === 'negeri' ? 100 : 65;

            // 3. Score Siswa Kelas XII / Rumbel (Normalized 0 - 100)
            $rumbelScore = min(100, ($school->grade_12_count / $maxGrade12) * 100);

            // 4. Score Riwayat Masuk UBSI Karawang
            $ubsiStudentsCount = $studentCounts[$school->id] ?? 0;
            $historyScore = min(100, ($ubsiStudentsCount / $maxUBSIStudents) * 100);

            // Total Weighted Potential Score
            $totalScore = ($distanceScore * 0.25) + ($statusScore * 0.20) + ($rumbelScore * 0.30) + ($historyScore * 0.25);
            $totalScore = round(min(100, $totalScore), 2);

            $scoresData[] = [
                'school' => $school,
                'distance_km' => $distanceKm,
                'score' => $totalScore,
            ];
        }

        // Sort schools by total score descending
        usort($scoresData, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Top 30 schools get Priority A, next 30 Priority B, rest Priority C
        foreach ($scoresData as $rank => $item) {
            $priority = match(true) {
                $rank < 30 => 'A', // Exactly Top 30 Priority A Schools
                $rank < 60 => 'B',
                default => 'C',
            };

            $item['school']->update([
                'distance_from_campus' => $item['distance_km'],
                'potential_score' => $item['score'],
                'priority_level' => $priority,
            ]);
        }
    }
}
