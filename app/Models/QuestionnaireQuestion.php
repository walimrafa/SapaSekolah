<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireQuestion extends Model
{
    use HasFactory;

    public const DEFAULT_SECTIONS = [
        'A. Motivasi' => [
            'motivation_job' => 'Saya ingin kuliah untuk meningkatkan peluang memperoleh pekerjaan yang baik.',
            'motivation_interest' => 'Saya ingin memilih program studi yang sesuai dengan minat dan bakat saya.',
            'motivation_parent' => 'Harapan orang tua menjadi alasan penting bagi saya untuk kuliah.',
        ],
        'B. Pencarian informasi' => [
            'information_social_media' => 'Saya mencari informasi kampus melalui media sosial sebelum menentukan pilihan.',
            'information_testimonials' => 'Testimoni mahasiswa atau alumni memengaruhi ketertarikan saya terhadap kampus.',
            'information_comparison' => 'Saya membandingkan informasi dari beberapa kampus sebelum mengambil keputusan.',
        ],
        'C. Pengaruh sosial' => [
            'social_parent' => 'Pendapat orang tua sangat memengaruhi pilihan kampus saya.',
            'social_friends' => 'Pilihan teman memengaruhi pertimbangan saya dalam memilih kampus.',
            'social_teacher' => 'Rekomendasi guru atau alumni meningkatkan kepercayaan saya terhadap kampus.',
        ],
        'D. Preferensi kampus' => [
            'campus_cost' => 'Biaya kuliah yang terjangkau merupakan faktor utama bagi saya.',
            'campus_reputation' => 'Akreditasi dan reputasi kampus sangat penting dalam keputusan saya.',
            'campus_career' => 'Peluang kerja, magang, dan keterampilan praktis menjadi pertimbangan utama saya.',
            'campus_location' => 'Lokasi kampus dan kemudahan transportasi memengaruhi pilihan saya.',
        ],
        'E. Niat dan hambatan' => [
            'intention_ubsi' => 'Saya mempertimbangkan UBSI sebagai salah satu pilihan kuliah.',
            'intention_information' => 'Saya berniat mencari informasi PMB UBSI lebih lanjut.',
            'barrier_cost' => 'Kendala biaya dapat membuat saya menunda atau membatalkan rencana kuliah.',
        ],
    ];

    protected $fillable = [
        'section_title',
        'question_key',
        'question_text',
        'order_index',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * Pastikan tabel terisi default jika belum ada data sama sekali.
     */
    public static function seedDefaultsIfEmpty(): void
    {
        if (static::query()->count() === 0) {
            static::resetToDefaults();
        }
    }

    /**
     * Dapatkan daftar bagian (sections) dan pertanyaan aktif terkelompok.
     */
    public static function getActiveSections(): array
    {
        static::seedDefaultsIfEmpty();

        $questions = static::query()
            ->where('is_active', true)
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $sections = [];
        foreach ($questions as $question) {
            $sections[$question->section_title][$question->question_key] = $question->question_text;
        }

        return !empty($sections) ? $sections : self::DEFAULT_SECTIONS;
    }

    /**
     * Dapatkan seluruh kamus pertanyaan aktif dalam format [question_key => question_text].
     */
    public static function allQuestions(): array
    {
        static::seedDefaultsIfEmpty();

        $questions = static::query()
            ->where('is_active', true)
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('question_text', 'question_key')
            ->toArray();

        if (empty($questions)) {
            $flat = [];
            foreach (self::DEFAULT_SECTIONS as $section) {
                foreach ($section as $key => $text) {
                    $flat[$key] = $text;
                }
            }
            return $flat;
        }

        return $questions;
    }

    /**
     * Reset seluruh pertanyaan ke template default bawaan UBSI.
     */
    public static function resetToDefaults(): void
    {
        static::query()->delete();

        $order = 1;
        foreach (self::DEFAULT_SECTIONS as $sectionTitle => $questions) {
            foreach ($questions as $key => $text) {
                static::create([
                    'section_title' => $sectionTitle,
                    'question_key' => $key,
                    'question_text' => $text,
                    'order_index' => $order++,
                    'is_active' => true,
                ]);
            }
        }
    }
}

