<?php

namespace App\Services;

use App\Models\StudentQuestionnaire;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstagramVerificationService
{
    /**
     * Parse text follower (e.g., '12.5k', '1.2M', '4,500', '1500') to integer.
     */
    public static function parseFollowerCount(string|int|float|null $raw): int
    {
        if (is_numeric($raw)) {
            return (int) $raw;
        }

        if (empty($raw)) {
            return 0;
        }

        $clean = strtolower(trim((string) $raw));
        $clean = str_replace([' ', ','], ['', ''], $clean);

        if (str_ends_with($clean, 'm')) {
            $num = (float) substr($clean, 0, -1);
            return (int) round($num * 1000000);
        }

        if (str_ends_with($clean, 'k')) {
            $num = (float) substr($clean, 0, -1);
            return (int) round($num * 1000);
        }

        return (int) preg_replace('/[^0-9]/', '', $clean);
    }

    /**
     * Verifikasi akun Instagram siswa secara otomatis.
     */
    public static function verifyCandidate(StudentQuestionnaire $candidate, bool $persist = true): array
    {
        $handle = $candidate->clean_instagram_handle;
        $claimedFollowers = (int) $candidate->instagram_followers;
        $minFollowers = StudentQuestionnaire::getKolMinFollowers();

        if (empty($handle)) {
            $result = [
                'status' => StudentQuestionnaire::KOL_VERIFY_REJECTED,
                'verified_followers' => 0,
                'is_kol' => false,
                'notes' => 'Ditolak: Siswa belum mencantumkan username Instagram yang valid.',
            ];
            if ($persist) {
                $candidate->update([
                    'kol_verification_status' => $result['status'],
                    'kol_verification_notes' => $result['notes'],
                    'is_kol' => false,
                    'kol_status' => StudentQuestionnaire::KOL_STATUS_DITOLAK,
                ]);
            }
            return $result;
        }

        // Coba periksa profil publik Instagram
        $realFollowers = null;
        $fetchError = null;

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ])->timeout(8)->get("https://www.instagram.com/{$handle}/");

            if ($response->status() === 404) {
                $result = [
                    'status' => StudentQuestionnaire::KOL_VERIFY_REJECTED,
                    'verified_followers' => 0,
                    'is_kol' => false,
                    'notes' => "Ditolak otomatis: Akun @{$handle} tidak ditemukan di Instagram (404 Not Found).",
                ];
                if ($persist) {
                    $candidate->update([
                        'kol_verification_status' => $result['status'],
                        'kol_verification_notes' => $result['notes'],
                        'is_kol' => false,
                        'kol_status' => StudentQuestionnaire::KOL_STATUS_DITOLAK,
                    ]);
                }
                return $result;
            }

            $html = $response->body();

            // Ekstrak meta og:description: "X Followers, Y Following, Z Posts - ..."
            if (preg_match('/<meta\s+property=["\']og:description["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches) ||
                preg_match('/<meta\s+name=["\']description["\']\s+content=["\']([^"\']+)["\']/i', $html, $matches)) {
                $desc = $matches[1];
                if (preg_match('/([0-9.,kKmM]+)\s+Followers/i', $desc, $fMatch)) {
                    $realFollowers = self::parseFollowerCount($fMatch[1]);
                }
            }
        } catch (\Throwable $e) {
            $fetchError = $e->getMessage();
            Log::info("Instagram verification notice for @{$handle}: " . $e->getMessage());
        }

        // Evaluasi Hasil Verifikasi
        if ($realFollowers !== null && $realFollowers > 0) {
            // Evaluasi kesesuaian data: jika real jauh di bawah klaim (misal < 60% dari klaim atau < min syarat)
            $isBelowMin = ($realFollowers < $minFollowers);
            $discrepancy = ($claimedFollowers > 0) ? ($claimedFollowers - $realFollowers) / $claimedFollowers : 0;
            $isOverClaimed = ($claimedFollowers > 1000 && $discrepancy > 0.40); // klaim > 40% lebih tinggi dari aslinya

            if ($isBelowMin || $isOverClaimed) {
                $notes = "Ditolak otomatis: Follower riil Instagram (@{$handle}) hanya " . number_format($realFollowers) . " (klaim: " . number_format($claimedFollowers) . "). Syarat min: " . number_format($minFollowers) . ".";
                $result = [
                    'status' => StudentQuestionnaire::KOL_VERIFY_REJECTED,
                    'verified_followers' => $realFollowers,
                    'is_kol' => false,
                    'notes' => $notes,
                ];
                if ($persist) {
                    $candidate->update([
                        'instagram_followers' => $realFollowers,
                        'instagram_followers_verified' => $realFollowers,
                        'kol_verification_status' => StudentQuestionnaire::KOL_VERIFY_REJECTED,
                        'kol_verification_notes' => $notes,
                        'is_kol' => false,
                        'kol_status' => StudentQuestionnaire::KOL_STATUS_DITOLAK,
                    ]);
                }
                return $result;
            }

            // Lolos Verifikasi
            $notes = "Terverifikasi riil Instagram: " . number_format($realFollowers) . " followers. Sesuai kriteria KOL.";
            $result = [
                'status' => StudentQuestionnaire::KOL_VERIFY_VERIFIED,
                'verified_followers' => $realFollowers,
                'is_kol' => true,
                'notes' => $notes,
            ];
            if ($persist) {
                $candidate->update([
                    'instagram_followers' => $realFollowers,
                    'instagram_followers_verified' => $realFollowers,
                    'kol_verification_status' => StudentQuestionnaire::KOL_VERIFY_VERIFIED,
                    'kol_verification_notes' => $notes,
                    'is_kol' => true,
                ]);
            }
            return $result;
        }

        // Bila Instagram membatasi crawling (rate limit/login wall Meta)
        // Jika klaim sudah memenuhi syarat minimal, tandai unverified dan beri catatan untuk verifikasi manual
        $notes = "Instagram membatasi pengecekan langsung (akun privat/login checkpoint). Klaim terinput: " . number_format($claimedFollowers) . " followers.";
        $result = [
            'status' => StudentQuestionnaire::KOL_VERIFY_UNVERIFIED,
            'verified_followers' => $claimedFollowers,
            'is_kol' => ($claimedFollowers >= $minFollowers),
            'notes' => $notes,
        ];

        if ($persist) {
            $candidate->update([
                'kol_verification_status' => StudentQuestionnaire::KOL_VERIFY_UNVERIFIED,
                'kol_verification_notes' => $notes,
            ]);
        }

        return $result;
    }
}
