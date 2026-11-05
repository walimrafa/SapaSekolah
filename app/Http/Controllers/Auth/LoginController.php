<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Display login page.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login attempt.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $inputEmail = strtolower(trim($request->input('email')));
        $emailAliases = [
            'admin@sapasekolah.com' => 'walim.wam@bsi.ac.id',
            'pimpinan@sapasekolah.com' => 'mohammad.myz@bsi.ac.id',
            'koordinator@sapasekolah.com' => 'walimsaja@gmail.com',
            'marketing@sapasekolah.com' => 'karawangmarkom@gmail.com',
            'cs2@sapasekolah.com' => 'deviwinda@gmail.com',
        ];
        $email = $emailAliases[$inputEmail] ?? $inputEmail;
        $inputPassword = (string) $request->input('password');
        $credentials = ['email' => $email, 'password' => $inputPassword];
        $remember = $request->boolean('remember');

        $authenticated = false;
        if (Auth::attempt($credentials, $remember)) {
            $authenticated = true;
        } else {
            // Fleksibilitas login: izinkan fallback password standar 'password' atau 'adminpassword'
            $candidateUser = \App\Models\User::where('email', $email)->first();
            if ($candidateUser && in_array($inputPassword, ['password', 'adminpassword'], true)) {
                Auth::login($candidateUser, $remember);
                $authenticated = true;
            }
        }

        if ($authenticated) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Akun Anda nonaktif. Silakan hubungi administrator.',
                ]);
            }

            \App\Services\ActivityLogService::log(
                'LOGIN',
                'auth',
                $user->id,
                "Pengguna {$user->name} ({$user->role?->name}) berhasil masuk ke sistem.",
                null,
                ['email' => $user->email]
            );

            return redirect()->intended(route('dashboard'))
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Selamat datang kembali, ' . $user->name . '!',
                ]);
        }

        \App\Services\ActivityLogService::log(
            'LOGIN_FAILED',
            'auth',
            null,
            "Percobaan login gagal untuk email: {$credentials['email']} dari IP: {$request->ip()}.",
            null,
            ['email' => $credentials['email'], 'ip' => $request->ip(), 'user_agent' => substr((string)$request->userAgent(), 0, 255)]
        );

        throw ValidationException::withMessages([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }
}
