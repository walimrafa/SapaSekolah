<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    public $timestamps = false;

    public const TIME_REGULER_PAGI = 'reguler_pagi';
    public const TIME_REGULER_MALAM = 'reguler_malam';
    public const TIME_KARYAWAN = 'karyawan';

    protected $fillable = [
        'name',
        'code',
        'faculty',
        'study_times',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'study_times' => 'array',
    ];

    /**
     * Daftar pilihan waktu kuliah yang dapat dibuka untuk setiap Program Studi.
     */
    public static function getStudyTimeOptions(): array
    {
        return [
            self::TIME_REGULER_PAGI => [
                'key' => self::TIME_REGULER_PAGI,
                'label' => 'Reguler (Pagi - Siang)',
                'short_label' => 'Reguler Pagi',
                'category' => 'Reguler',
                'badge_class' => 'bg-amber-50 text-amber-800 border-amber-300',
                'icon' => 'sun',
                'description' => 'Waktu kuliah reguler sesi pagi hingga siang (Senin - Jumat)',
            ],
            self::TIME_REGULER_MALAM => [
                'key' => self::TIME_REGULER_MALAM,
                'label' => 'Reguler (Sore - Malam)',
                'short_label' => 'Reguler Sore/Malam',
                'category' => 'Reguler',
                'badge_class' => 'bg-indigo-50 text-indigo-800 border-indigo-300',
                'icon' => 'moon',
                'description' => 'Waktu kuliah reguler sesi sore hingga malam (Senin - Jumat)',
            ],
            self::TIME_KARYAWAN => [
                'key' => self::TIME_KARYAWAN,
                'label' => 'Waktu Kuliah Karyawan (Jumat - Sabtu)',
                'short_label' => 'Karyawan (Jumat - Sabtu)',
                'category' => 'Karyawan',
                'badge_class' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                'icon' => 'briefcase',
                'description' => 'Waktu kuliah kelas karyawan/pekerja di akhir pekan (Jumat - Sabtu)',
            ],
        ];
    }

    /**
     * Memeriksa apakah waktu kuliah tertentu dibuka pada prodi ini.
     */
    public function hasStudyTime(string $key): bool
    {
        if (empty($this->study_times) || !is_array($this->study_times)) {
            return false;
        }

        return in_array($key, $this->study_times, true);
    }

    /**
     * Dapatkan detail waktu kuliah yang aktif untuk prodi ini.
     */
    public function getActiveStudyTimesDetails(): array
    {
        $all = self::getStudyTimeOptions();
        $active = [];

        if (is_array($this->study_times)) {
            foreach ($this->study_times as $key) {
                if (isset($all[$key])) {
                    $active[$key] = $all[$key];
                }
            }
        }

        return $active;
    }
}
