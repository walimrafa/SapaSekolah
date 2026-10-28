@php
    $appSettings = \App\Services\AppSettingService::all();
    $waNumber = preg_replace('/[^0-9]/', '', $appSettings['admin_phone'] ?? '6281211112222');
    if (str_starts_with($waNumber, '0')) {
        $waNumber = '62' . substr($waNumber, 1);
    }
    $eventDateFormatted = $proposal->event_date ? \Carbon\Carbon::parse($proposal->event_date)->format('d/m/Y') : '-';
    $waText = urlencode("Halo Tim Kerjasama {$appSettings['campus_name']}, kami telah mengajukan proposal kegiatan dengan Kode Referensi: {$proposal->reference_no}\n\nSekolah: {$proposal->school_name}\nEvent: {$proposal->event_name}\nTanggal: {$eventDateFormatted}\n\nMohon konfirmasi dan info tindak lanjutnya. Terima kasih!");
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Berhasil - {{ $proposal->reference_no }} | {{ $appSettings['app_name'] }}</title>

    <!-- Favicon / Logo Title SAPA Sekolah -->
    <link rel="icon" type="image/png" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-950 min-h-screen py-10 px-4 sm:px-6 relative overflow-x-hidden">

    <!-- Decorative Glows -->
    <div class="fixed top-0 left-1/3 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-0 right-1/3 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-2xl mx-auto space-y-6 relative z-10" x-data="{
        copied: false,
        copyRef() {
            navigator.clipboard.writeText('{{ $proposal->reference_no }}');
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        }
    }">

        <!-- Success Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl text-slate-200 text-center relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-40 h-40 bg-emerald-500/15 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Success Icon Animation -->
            <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-emerald-600 to-emerald-400 text-slate-950 flex items-center justify-center mx-auto shadow-xl shadow-emerald-500/20 mb-6 animate-bounce">
                <i data-lucide="check" class="w-10 h-10 stroke-[3]"></i>
            </div>

            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-3">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 mr-1 text-emerald-400"></i> Pengajuan Berhasil Diterima
            </span>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Terima Kasih atas Pengajuan Anda!
            </h1>
            <p class="text-sm text-slate-300 mt-2 max-w-md mx-auto leading-relaxed">
                Proposal kegiatan dari <strong class="text-white">{{ $proposal->school_name }}</strong> telah masuk ke dalam antrean review tim promosi & manajemen kampus kami.
            </p>

            <!-- Reference Number Highlight Box -->
            <div class="mt-6 p-5 rounded-2xl bg-slate-950/80 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 max-w-lg mx-auto">
                <div class="text-left">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kode Referensi Pengajuan</span>
                    <p class="text-2xl font-black text-amber-400 tracking-wider">{{ $proposal->reference_no }}</p>
                </div>
                <button type="button" @click="copyRef()"
                    class="w-full sm:w-auto inline-flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold transition duration-150"
                    :class="copied ? 'bg-emerald-500 text-slate-950 shadow-md' : 'bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700'">
                    <i data-lucide="copy" class="w-4 h-4" x-show="!copied"></i>
                    <i data-lucide="check" class="w-4 h-4" x-show="copied"></i>
                    <span x-text="copied ? 'Tersalin!' : 'Salin Kode'"></span>
                </button>
            </div>

            <!-- Summary Details -->
            <div class="mt-8 text-left bg-slate-950/40 rounded-2xl p-5 border border-slate-800/80 space-y-3 text-xs sm:text-sm">
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Nama Event:</span>
                    <span class="font-bold text-white text-right">{{ $proposal->event_name }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Jadwal Pelaksanaan:</span>
                    <span class="font-semibold text-slate-200 text-right">
                        {{ $proposal->event_date ? \Carbon\Carbon::parse($proposal->event_date)->translatedFormat('d F Y') : '-' }}
                        @if($proposal->event_end_date && $proposal->event_end_date != $proposal->event_date)
                            s.d {{ \Carbon\Carbon::parse($proposal->event_end_date)->translatedFormat('d F Y') }}
                        @endif
                        ({{ $proposal->event_time }})
                    </span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Lokasi Acara:</span>
                    <span class="font-semibold text-slate-200 text-right">{{ $proposal->event_location }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Target Peserta:</span>
                    <span class="font-semibold text-slate-200 text-right">{{ $proposal->target_participants }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/80">
                    <span class="text-slate-400">Dukungan Diminta:</span>
                    <span class="font-semibold text-amber-300 text-right">{{ $proposal->support_type_requested }}</span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Berkas Proposal:</span>
                    <span class="font-semibold text-emerald-400 flex items-center gap-1.5 text-right">
                        <i data-lucide="file-check" class="w-4 h-4"></i>
                        {{ $proposal->proposal_file_name }}
                    </span>
                </div>
            </div>

            <!-- Workflow Progress Stepper -->
            <div class="mt-8 pt-6 border-t border-slate-800 text-left">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 text-center">Tahapan Proses Pengajuan</h4>
                <div class="grid grid-cols-4 gap-2 text-center">
                    <div class="space-y-1.5">
                        <div class="w-8 h-8 rounded-full bg-emerald-500 text-slate-950 font-black text-xs flex items-center justify-center mx-auto">
                            <i data-lucide="check" class="w-4 h-4 stroke-[3]"></i>
                        </div>
                        <p class="text-[11px] font-bold text-emerald-400">Diterima</p>
                    </div>
                    <div class="space-y-1.5">
                        <div class="w-8 h-8 rounded-full bg-amber-400 text-slate-900 font-black text-xs flex items-center justify-center mx-auto animate-pulse">
                            2
                        </div>
                        <p class="text-[11px] font-bold text-amber-400">Review Tim</p>
                    </div>
                    <div class="space-y-1.5 opacity-40">
                        <div class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 font-bold text-xs flex items-center justify-center mx-auto">
                            3
                        </div>
                        <p class="text-[11px] text-slate-400">Persetujuan</p>
                    </div>
                    <div class="space-y-1.5 opacity-40">
                        <div class="w-8 h-8 rounded-full bg-slate-800 text-slate-400 font-bold text-xs flex items-center justify-center mx-auto">
                            4
                        </div>
                        <p class="text-[11px] text-slate-400">Pelaksanaan</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}" target="_blank"
                    class="w-full sm:w-auto inline-flex items-center justify-center space-x-2 px-6 py-3 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/20 transition">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Konfirmasi via WhatsApp</span>
                </a>

                <a href="{{ route('school-events.create') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center space-x-2 px-5 py-3 rounded-xl text-sm font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Ajukan Event Lainnya</span>
                </a>
            </div>
        </div>

        <div class="text-center text-xs text-slate-500">
            © {{ date('Y') }} {{ $appSettings['app_name'] }} • {{ $appSettings['campus_name'] ?? 'Universitas Bina Sarana Informatika' }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
