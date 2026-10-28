<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolCoordinatesSeeder extends Seeder
{
    /**
     * Seed realistic GPS coordinates for Karawang schools per Kecamatan.
     */
    public function run(): void
    {
        // Kecamatan center coordinates in Kabupaten Karawang
        $districtCoords = [
            'Ciampel' => ['lat' => -6.4025, 'lng' => 107.3481],
            'Telukjambe Timur' => ['lat' => -6.3421, 'lng' => 107.2915],
            'Telukjambe Barat' => ['lat' => -6.3688, 'lng' => 107.2412],
            'Klari' => ['lat' => -6.3712, 'lng' => 107.3855],
            'Lemahabang' => ['lat' => -6.2954, 'lng' => 107.4412],
            'Telagasari' => ['lat' => -6.2641, 'lng' => 107.4089],
            'Majalaya' => ['lat' => -6.3211, 'lng' => 107.3711],
            'Karawang Timur' => ['lat' => -6.3155, 'lng' => 107.3325],
            'Karawang Barat' => ['lat' => -6.3012, 'lng' => 107.2988],
            'Pangkalan' => ['lat' => -6.4821, 'lng' => 107.2155],
            'Tegalwaru' => ['lat' => -6.5211, 'lng' => 107.2488],
            'Cikampek' => ['lat' => -6.4112, 'lng' => 107.4522],
            'Purwasari' => ['lat' => -6.3855, 'lng' => 107.4112],
            'Tirtamulya' => ['lat' => -6.3512, 'lng' => 107.4788],
            'Jatisari' => ['lat' => -6.3788, 'lng' => 107.5112],
            'Banyusari' => ['lat' => -6.3255, 'lng' => 107.5412],
            'Kotabaru' => ['lat' => -6.3812, 'lng' => 107.4855],
            'Cilamaya Wetan' => ['lat' => -6.2612, 'lng' => 107.5612],
            'Cilamaya Kulon' => ['lat' => -6.2312, 'lng' => 107.5112],
            'Rawamerta' => ['lat' => -6.2212, 'lng' => 107.3412],
            'Tempuran' => ['lat' => -6.1855, 'lng' => 107.4212],
            'Kutawaluya' => ['lat' => -6.1955, 'lng' => 107.3112],
            'Rengasdengklok' => ['lat' => -6.1588, 'lng' => 107.2988],
            'Jayakerta' => ['lat' => -6.1212, 'lng' => 107.2712],
            'Pedes' => ['lat' => -6.0788, 'lng' => 107.3412],
            'Cilebar' => ['lat' => -6.1211, 'lng' => 107.3912],
            'Cibuaya' => ['lat' => -6.0412, 'lng' => 107.3712],
            'Tirtajaya' => ['lat' => -6.0312, 'lng' => 107.2412],
            'Batujaya' => ['lat' => -6.0712, 'lng' => 107.1712],
            'Pakisjaya' => ['lat' => -5.9912, 'lng' => 107.1312],
        ];

        $schools = School::with('district')->get();

        foreach ($schools as $index => $school) {
            $dName = $school->district ? $school->district->name : 'Karawang Barat';
            $base = $districtCoords[$dName] ?? ['lat' => -6.3012, 'lng' => 107.2988];

            // Add small jitter so markers don't overlap completely
            $latJitter = ((($index * 17) % 50) - 25) * 0.0012;
            $lngJitter = ((($index * 23) % 50) - 25) * 0.0012;

            // Generate Priority Level & Potential Score for demonstration if empty
            $score = 50 + (($index * 7) % 48);
            $priority = match(true) {
                $score >= 80 => 'A',
                $score >= 65 => 'B',
                default => 'C',
            };

            $school->update([
                'latitude' => $base['lat'] + $latJitter,
                'longitude' => $base['lng'] + $lngJitter,
                'potential_score' => $school->potential_score > 0 ? $school->potential_score : $score,
                'priority_level' => $school->priority_level ?? $priority,
                'student_count' => $school->student_count > 0 ? $school->student_count : (300 + (($index * 37) % 700)),
                'grade_12_count' => $school->grade_12_count > 0 ? $school->grade_12_count : (100 + (($index * 13) % 250)),
            ]);
        }
    }
}
