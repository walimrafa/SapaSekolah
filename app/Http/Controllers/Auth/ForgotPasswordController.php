<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\AppSettingService;
use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Tampilkan halaman permintaan lupa kata sandi.
     */
    public function showLinkRequestForm(): View
    {
        $captcha = LoginController::generateCaptcha();
        $pending = session('password_reset_pending');

        if ($pending && isset($pending['expires_at']) && now()->timestamp > $pending['expires_at']) {
            session()->forget('password_reset_pending');
            $pending = null;
        }

        return view('auth.forgot-password', [
            'captchaSvg' => $captcha['svg'],
            'pendingReset' => $pending,
        ]);
    }

    /**
     * Proses permintaan reset password (kirim lewat Email / WhatsApp Gateway / Verifikasi Nomor HP).
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string', 'max:150'],
            'captcha' => [app()->environment('testing') ? 'nullable' : 'required', 'string'],
        ], [
            'login.required' => 'Silakan masukkan Email, Username, atau NISN yang terdaftar.',
            'captcha.required' => 'Kode keamanan wajib diisi.',
        ]);

        // 1. Verifikasi Captcha (kecuali automated testing)
        if (!app()->environment('testing')) {
            $inputCaptcha = strtoupper(trim((string) $request->input('captcha')));
            $sessionCaptcha = strtoupper((string) session('login_captcha'));
            if (empty($inputCaptcha) || empty($sessionCaptcha) || $inputCaptcha !== $sessionCaptcha) {
                throw ValidationException::withMessages([
                    'captcha' => 'Kode keamanan (captcha) tidak sesuai. Silakan coba lagi.',
                ]);
            }
        }

        // 2. Cari akun pengguna aktif berdasarkan email / username / nisn
        $inputLogin = trim((string) $request->input('login'));
        $lowerInput = strtolower($inputLogin);

        $emailAliases = [
            'admin@sapasekolah.com' => 'walim.wam@bsi.ac.id',
            'pimpinan@sapasekolah.com' => 'mohammad.myz@bsi.ac.id',
            'koordinator@sapasekolah.com' => 'walimsaja@gmail.com',
            'marketing@sapasekolah.com' => 'karawangmarkom@gmail.com',
            'cs2@sapasekolah.com' => 'deviwinda@gmail.com',
            'admin' => 'walim.wam@bsi.ac.id',
            'pimpinan' => 'mohammad.myz@bsi.ac.id',
            'koordinator' => 'walimsaja@gmail.com',
        ];
        $resolvedEmail = $emailAliases[$lowerInput] ?? $lowerInput;

        $user = User::where('status', 'active')
            ->where(function ($q) use ($resolvedEmail, $inputLogin) {
                $q->whereRaw('LOWER(email) = ?', [$resolvedEmail])
                  ->orWhere('username', $inputLogin)
                  ->orWhere('nisn', $inputLogin);
            })
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'login' => 'Akun aktif dengan Email / Username / NISN tersebut tidak ditemukan.',
            ]);
        }

        // 3. Buat token reset aman (berlaku 60 menit)
        $plainToken = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', [
            'token' => $plainToken,
            'email' => $user->email,
        ]);

        $appName = AppSettingService::get('app_name', 'SAPA Sekolah');
        $sentChannels = [];

        // 4. Kirim melalui Email SMTP jika mailer produksi aktif
        $mailer = config('mail.default');
        if (!in_array($mailer, ['log', 'array', null, ''], true)) {
            try {
                $htmlBody = "
                    <div style='font-family: Arial, sans-serif; max-width: 560px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 16px;'>
                        <h2 style='color: #0B023E; margin-top: 0;'>Reset Kata Sandi — {$appName}</h2>
                        <p>Halo <strong>" . e($user->name) . "</strong>,</p>
                        <p>Kami menerima permintaan pengaturan ulang kata sandi untuk akun Anda (<strong>" . e($user->email) . "</strong>).</p>
                        <p style='margin: 24px 0;'>
                            <a href='{$resetUrl}' style='display: inline-block; background: #0B023E; color: #ffffff; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: bold;'>
                                Buat Kata Sandi Baru
                            </a>
                        </p>
                        <p style='font-size: 12px; color: #64748b;'>Tautan ini berlaku selama 60 menit. Abaikan email ini jika Anda tidak merasa mengajukan permintaan reset kata sandi.</p>
                    </div>
                ";

                Mail::html($htmlBody, function ($msg) use ($user, $appName) {
                    $msg->to($user->email, $user->name)
                        ->subject("Reset Kata Sandi Akun {$appName}");
                });

                $sentChannels[] = 'Email (' . $this->maskEmail($user->email) . ')';
            } catch (\Throwable $e) {
                Log::warning('Password reset email failed: ' . $e->getMessage());
            }
        }

        // 5. Kirim melalui WhatsApp Gateway jika token WA aktif & user memiliki nomor HP
        $waToken = trim((string) (AppSettingService::get('wa_api_token') ?? env('FONNTE_TOKEN', '')));
        if (!empty($user->phone) && !empty($waToken)) {
            try {
                $waService = app(WhatsAppNotificationService::class);
                $waMsg = "*Reset Kata Sandi {$appName}*\n\n"
                    . "Halo *{$user->name}*,\n"
                    . "Klik tautan berikut untuk membuat kata sandi baru akun Anda (berlaku 60 menit):\n"
                    . "{$resetUrl}\n\n"
                    . "_Abaikan pesan ini jika Anda tidak mengajukan reset kata sandi._";

                $waRes = $waService->sendMessage($user->phone, $waMsg, 'password_reset', $user->name);
                if (!empty($waRes['status'])) {
                    $sentChannels[] = 'WhatsApp (' . $this->maskPhone($user->phone) . ')';
                }
            } catch (\Throwable $e) {
                Log::warning('Password reset WhatsApp failed: ' . $e->getMessage());
            }
        }

        // 6. Jika berhasil terkirim via Email atau WhatsApp Gateway
        if (!empty($sentChannels)) {
            session()->forget('password_reset_pending');
            return redirect()->route('password.request')->with('status', 'Tautan reset kata sandi berhasil dikirim ke ' . implode(' dan ', $sentChannels) . '. Silakan periksa pesan masuk Anda.');
        }

        // 7. Mode Fallback Cerdas: Verifikasi Nomor HP Terdaftar / Hubungi Admin
        session([
            'password_reset_pending' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'masked_email' => $this->maskEmail($user->email),
                'has_phone' => !empty($user->phone),
                'masked_phone' => !empty($user->phone) ? $this->maskPhone($user->phone) : null,
                'token' => $plainToken,
                'expires_at' => now()->addMinutes(15)->timestamp,
            ],
        ]);

        return redirect()->route('password.request')->with('info_verify', 'Akun ditemukan! Karena pengiriman Email/WA otomatis belum diaktifkan di server ini, silakan verifikasi Nomor HP terdaftar Anda di bawah ini untuk langsung membuat kata sandi baru.');
    }

    /**
     * Verifikasi Nomor HP terdaftar untuk melanjutkan langsung ke formulir reset kata sandi.
     */
    public function verifyPhoneAndProceed(Request $request): RedirectResponse
    {
        $pending = session('password_reset_pending');
        if (!$pending || empty($pending['email']) || empty($pending['token']) || now()->timestamp > ($pending['expires_at'] ?? 0)) {
            session()->forget('password_reset_pending');
            return redirect()->route('password.request')->withErrors([
                'login' => 'Sesi verifikasi telah berakhir. Silakan ulangi permintaan lupa kata sandi.',
            ]);
        }

        $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ], [
            'phone.required' => 'Nomor HP terdaftar wajib diisi untuk verifikasi identitas.',
        ]);

        $user = User::where('email', $pending['email'])->where('status', 'active')->first();
        if (!$user || empty($user->phone)) {
            return redirect()->route('password.request')->withErrors([
                'phone' => 'Akun Anda belum memiliki Nomor HP terdaftar. Silakan hubungi Administrator melalui tombol WhatsApp di bawah.',
            ]);
        }

        $inputDigits = $this->normalizePhone($request->input('phone'));
        $storedDigits = $this->normalizePhone($user->phone);

        if ($inputDigits === '' || $inputDigits !== $storedDigits) {
            return redirect()->route('password.request')->withErrors([
                'phone' => 'Nomor HP yang Anda masukkan tidak sesuai dengan Nomor HP yang terdaftar pada akun ini.',
            ]);
        }

        $resetUrl = route('password.reset', [
            'token' => $pending['token'],
            'email' => $pending['email'],
        ]);

        session()->forget('password_reset_pending');

        return redirect($resetUrl);
    }

    /**
     * Tampilkan form pembuatan kata sandi baru berdasarkan token yang valid.
     */
    public function showResetForm(Request $request, string $token): View|RedirectResponse
    {
        $email = trim((string) $request->query('email', ''));
        if (!$this->isValidToken($email, $token)) {
            return redirect()->route('password.request')->withErrors([
                'login' => 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa (lebih dari 60 menit). Silakan ajukan permintaan ulang.',
            ]);
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Simpan kata sandi baru setelah validasi token.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $email = trim((string) $request->input('email'));
        $token = (string) $request->input('token');

        if (!$this->isValidToken($email, $token)) {
            return redirect()->route('password.request')->withErrors([
                'login' => 'Tautan reset kata sandi tidak valid atau sudah kedaluwarsa. Silakan ajukan permintaan baru.',
            ]);
        }

        $user = User::where('email', $email)->where('status', 'active')->first();
        if (!$user) {
            return redirect()->route('password.request')->withErrors([
                'login' => 'Akun tidak ditemukan atau tidak aktif.',
            ]);
        }

        $user->password = Hash::make($request->input('password'));
        $user->setRememberToken(Str::random(60));
        $user->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        session()->forget('password_reset_pending');

        try {
            ActivityLogService::log(
                'RESET_PASSWORD',
                'user',
                $user->id,
                "Reset kata sandi mandiri melalui fitur Lupa Password: {$user->name} ({$user->email})"
            );
        } catch (\Throwable) {}

        return redirect()->route('login')->with('status', 'Kata sandi Anda berhasil diperbarui! Silakan masuk menggunakan kata sandi baru.');
    }

    /**
     * Cek validitas token reset di tabel password_reset_tokens (maksimal 60 menit).
     */
    protected function isValidToken(string $email, string $plainToken): bool
    {
        if ($email === '' || $plainToken === '') {
            return false;
        }

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();
        if (!$record) {
            return false;
        }

        $createdAt = Carbon::parse($record->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            return false;
        }

        return Hash::check($plainToken, $record->token);
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        }
        return $digits;
    }

    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***';
        }
        $name = $parts[0];
        $visible = substr($name, 0, min(2, strlen($name)));
        return $visible . str_repeat('*', max(3, strlen($name) - 2)) . '@' . $parts[1];
    }

    protected function maskPhone(string $phone): string
    {
        $digits = $this->normalizePhone($phone);
        if (strlen($digits) <= 6) {
            return str_repeat('*', strlen($digits));
        }
        return substr($digits, 0, 4) . str_repeat('*', strlen($digits) - 7) . substr($digits, -3);
    }
}
