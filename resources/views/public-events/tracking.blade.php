@php
    $appSettings = \App\Services\AppSettingService::all();
    $targetCampus = $proposal ? ($proposal->campus ?: \App\Models\Campus::find($proposal->campus_id)) : null;
    $rawPhone = $targetCampus?->phone ?: ($appSettings['campus_phone'] ?? ($appSettings['admin_phone'] ?? '081220022851'));
    $waNumber = preg_replace('/[^0-9]/', '', $rawPhone);
    if (str_starts_with($waNumber, '0')) {
        $waNumber = '62' . substr($waNumber, 1);
    } elseif (str_starts_with($waNumber, '8')) {
        $waNumber = '62' . $waNumber;
    }
    $campusName = $targetCampus?->name ?: ($appSettings['campus_name'] ?? 'Universitas BSI');
    $waText = $proposal 
        ? urlencode("Halo Tim Kerjasama {$campusName}, saya ingin menanyakan perkembangan proposal kegiatan dengan Kode Referensi: {$proposal->reference_no} ({$proposal->event_name} - {$proposal->school_name}). Terima kasih.")
        : urlencode("Halo Tim Kerjasama {$campusName}, saya ingin menanyakan pengajuan proposal sponsorship kegiatan sekolah kami ke UBSI. Terima kasih.");
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Status Pengajuan Proposal Event | {{ $appSettings['app_name'] }}</title>

    <!-- Favicon / Logo Title SAPA Sekolah -->
    <link rel="icon" type="image/png" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-950 min-h-screen py-10 px-4 sm:px-6 relative overflow-x-hidden selection:bg-brand-500 selection:text-white">

    <!-- Decorative Glows -->
    <div class="fixed top-0 left-1/3 w-96 h-96 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-0 right-1/3 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-3xl mx-auto space-y-6 relative z-10">

        <!-- Top Header & Back -->
        <div class="flex items-center justify-between">
            <a href="{{ route('school-events.create') }}" class="inline-flex items-center space-x-2 text-xs font-bold text-slate-400 hover:text-white transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Formulir Pengajuan</span>
            </a>
            <span class="text-xs font-semibold text-amber-400 bg-amber-400/10 border border-amber-400/20 px-3 py-1 rounded-full">
                Live Status Tracking
            </span>
        </div>

        <!-- Header Card -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-brand-950 text-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-800 text-center relative overflow-hidden">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-amber-500 flex items-center justify-center mx-auto shadow-lg shadow-brand-500/20 mb-4">
                <i data-lucide="search-check" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                Cek Status Pengajuan Proposal
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 mt-2 max-w-lg mx-auto">
                Ketahui proses review, verifikasi anggaran dana sponsor, dan status persetujuan kegiatan sekolah Anda secara transparan dan realtime.
            </p>

            <!-- Search Bar Form -->
            <form action="{{ route('school-events.tracking') }}" method="GET" class="mt-6 max-w-xl mx-auto">
                <div class="flex flex-col sm:flex-row gap-2">
                    <div class="relative flex-1">
                        <input type="text" name="ref" value="{{ $search }}" required
                            placeholder="Ketik Kode Referensi (EVT-...) atau No. WhatsApp"
                            class="w-full bg-slate-950 border border-slate-700 rounded-2xl pl-11 pr-4 py-3.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition shadow-inner">
                        <i data-lucide="file-search" class="w-5 h-5 text-slate-400 absolute left-3.5 top-3.5"></i>
                    </div>
                    <button type="submit"
                        class="px-6 py-3.5 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm shadow-lg shadow-brand-600/30 transition flex items-center justify-center space-x-2 shrink-0">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Lacak Sekarang</span>
                    </button>
                </div>
            </form>
        </div>

        @if($searched && !$proposal)
            <!-- Not Found State -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 shadow-2xl text-center text-slate-300 space-y-3">
                <div class="w-14 h-14 rounded-full bg-rose-500/10 text-rose-400 flex items-center justify-center mx-auto">
                    <i data-lucide="alert-triangle" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-bold text-white">Pengajuan Proposal Tidak Ditemukan</h3>
                <p class="text-xs text-slate-400 max-w-md mx-auto">
                    Tidak ditemukan proposal dengan kata kunci "<strong class="text-white">{{ $search }}</strong>". Pastikan kode referensi (contoh: <code>EVT-20261030-0001</code>) atau nomor WhatsApp yang Anda masukkan sesuai saat pengisian formulir.
                </p>
                <div class="pt-2">
                    <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}" target="_blank"
                        class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>Hubungi WhatsApp Resmi BSI</span>
                    </a>
                </div>
            </div>
        @elseif($proposal)
            <!-- Proposal Found: Details & Timeline Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl text-slate-200 space-y-6">
                <!-- Proposal Header & Badges -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-800 gap-4">
                    <div>
                        <div class="flex items-center space-x-2 mb-1.5">
                            <span class="font-mono text-xs font-bold text-brand-400 bg-brand-500/10 px-2.5 py-0.5 rounded-lg border border-brand-500/20">
                                {{ $proposal->reference_no }}
                            </span>
                            <span class="text-xs text-slate-400">
                                Diajukan {{ $proposal->created_at->format('d M Y, H:i') }} WIB
                            </span>
                        </div>
                        <h2 class="text-xl font-black text-white tracking-tight">
                            {{ $proposal->event_name }}
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Sekolah: <strong class="text-slate-200">{{ $proposal->school_name }}</strong> • Kampus: <span class="text-amber-400 font-bold">{{ $proposal->campus?->name ?? 'UBSI' }}</span>
                        </p>
                    </div>

                    <div class="shrink-0 text-left sm:text-right">
                        @if($proposal->status === \App\Models\SchoolEventProposal::STATUS_MENUNGGU_REVIEW)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                <i data-lucide="clock" class="w-3.5 h-3.5 mr-1.5 text-amber-400"></i> Menunggu Review
                            </span>
                        @elseif($proposal->status === \App\Models\SchoolEventProposal::STATUS_DIAJUKAN_KE_PUSAT)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                <i data-lucide="send" class="w-3.5 h-3.5 mr-1.5 text-blue-400"></i> Diajukan ke Pusat
                            </span>
                        @elseif($proposal->status === \App\Models\SchoolEventProposal::STATUS_MENUNGGU_ACC)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                <i data-lucide="file-check-2" class="w-3.5 h-3.5 mr-1.5 text-purple-400"></i> Menunggu ACC Pimpinan
                            </span>
                        @elseif($proposal->status === \App\Models\SchoolEventProposal::STATUS_DISETUJUI)
                            <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 shadow-lg shadow-emerald-500/10">
                                <i data-lucide="check-circle-2" class="w-4 h-4 mr-1.5 text-emerald-400"></i> Proposal Disetujui
                            </span>
                        @elseif($proposal->status === \App\Models\SchoolEventProposal::STATUS_DITOLAK)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                <i data-lucide="x-circle" class="w-3.5 h-3.5 mr-1.5 text-rose-400"></i> Belum Disetujui
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Timeline Stepper -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tahapan Proses &amp; Perkembangan</h4>
                    
                    @php
                        $statusOrder = [
                            \App\Models\SchoolEventProposal::STATUS_MENUNGGU_REVIEW => 1,
                            \App\Models\SchoolEventProposal::STATUS_DIAJUKAN_KE_PUSAT => 2,
                            \App\Models\SchoolEventProposal::STATUS_MENUNGGU_ACC => 3,
                            \App\Models\SchoolEventProposal::STATUS_DISETUJUI => 4,
                            \App\Models\SchoolEventProposal::STATUS_DITOLAK => 4,
                        ];
                        $currentStep = $statusOrder[$proposal->status] ?? 1;
                        $isRejected = ($proposal->status === \App\Models\SchoolEventProposal::STATUS_DITOLAK);
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-2">
                        <!-- Step 1 -->
                        <div class="p-3.5 rounded-2xl border {{ $currentStep >= 1 ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-slate-950/40 border-slate-800 text-slate-500' }}">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-6 h-6 rounded-full {{ $currentStep >= 1 ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-400' }} font-bold text-xs flex items-center justify-center">1</span>
                                <i data-lucide="inbox" class="w-4 h-4"></i>
                            </div>
                            <p class="text-xs font-bold">Proposal Diterima</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Berkas masuk sistem</p>
                        </div>

                        <!-- Step 2 -->
                        <div class="p-3.5 rounded-2xl border {{ $currentStep >= 2 ? 'bg-blue-500/10 border-blue-500/30 text-blue-300' : ($currentStep == 1 ? 'bg-amber-500/10 border-amber-500/30 text-amber-300 animate-pulse' : 'bg-slate-950/40 border-slate-800 text-slate-500') }}">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-6 h-6 rounded-full {{ $currentStep >= 2 ? 'bg-blue-500 text-slate-950' : ($currentStep == 1 ? 'bg-amber-400 text-slate-950' : 'bg-slate-800 text-slate-400') }} font-bold text-xs flex items-center justify-center">2</span>
                                <i data-lucide="file-search" class="w-4 h-4"></i>
                            </div>
                            <p class="text-xs font-bold">Review Tim PMB</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Pemeriksaan kegiatan</p>
                        </div>

                        <!-- Step 3 -->
                        <div class="p-3.5 rounded-2xl border {{ $currentStep >= 3 ? 'bg-purple-500/10 border-purple-500/30 text-purple-300' : 'bg-slate-950/40 border-slate-800 text-slate-500' }}">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-6 h-6 rounded-full {{ $currentStep >= 3 ? 'bg-purple-500 text-slate-950' : 'bg-slate-800 text-slate-400' }} font-bold text-xs flex items-center justify-center">3</span>
                                <i data-lucide="scale" class="w-4 h-4"></i>
                            </div>
                            <p class="text-xs font-bold">Verifikasi ACC</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Penetapan anggaran</p>
                        </div>

                        <!-- Step 4 -->
                        <div class="p-3.5 rounded-2xl border {{ $isRejected ? 'bg-rose-500/10 border-rose-500/30 text-rose-300' : ($currentStep >= 4 ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-slate-950/40 border-slate-800 text-slate-500') }}">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="w-6 h-6 rounded-full {{ $isRejected ? 'bg-rose-500 text-white' : ($currentStep >= 4 ? 'bg-emerald-500 text-slate-950' : 'bg-slate-800 text-slate-400') }} font-bold text-xs flex items-center justify-center">4</span>
                                <i data-lucide="{{ $isRejected ? 'x-circle' : 'party-popper' }}" class="w-4 h-4"></i>
                            </div>
                            <p class="text-xs font-bold">{{ $isRejected ? 'Keputusan Akhir' : 'Persetujuan & Dana' }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $isRejected ? 'Belum dapat disetujui' : 'Pelaksanaan event' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Approval Highlights (If Approved) -->
                @if($proposal->status === \App\Models\SchoolEventProposal::STATUS_DISETUJUI)
                    <div class="p-5 rounded-2xl bg-gradient-to-r from-emerald-950/50 via-slate-950 to-emerald-950/50 border border-emerald-500/30 space-y-3">
                        <div class="flex items-center space-x-2 text-emerald-400 font-bold text-sm">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            <span>Rincian Dukungan Yang Disetujui</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-3 rounded-xl bg-slate-900/80 border border-emerald-500/20">
                                <span class="text-slate-400 block mb-1">Bantuan Dana Sponsorship:</span>
                                <span class="text-lg font-black text-emerald-300">
                                    Rp {{ number_format($proposal->approved_amount ?: 0, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-900/80 border border-emerald-500/20">
                                <span class="text-slate-400 block mb-1">Fasilitas / Bentuk Dukungan:</span>
                                <span class="text-xs font-bold text-white">
                                    {{ $proposal->approved_support_items ?: 'Sponsorship & Fasilitas Branding PMB UBSI' }}
                                </span>
                            </div>
                        </div>
                        @if($proposal->status_notes)
                            <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800 text-xs text-slate-300">
                                <strong class="text-amber-300">Pesan dari Tim UBSI:</strong>
                                <p class="mt-0.5">{{ $proposal->status_notes }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Event Details Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2 border-t border-slate-800">
                    <div class="space-y-2">
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Tanggal Pelaksanaan:</span>
                            <span class="font-bold text-white">{{ $proposal->event_date ? $proposal->event_date->format('d M Y') : '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Waktu &amp; Lokasi:</span>
                            <span class="font-semibold text-white">{{ $proposal->event_time }} • {{ $proposal->event_location }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Target Peserta:</span>
                            <span class="font-semibold text-white">{{ $proposal->target_participants }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Narahubung (PIC):</span>
                            <span class="font-bold text-white">{{ $proposal->pic_name }} ({{ $proposal->pic_position ?: 'Panitia' }})</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Kontak WhatsApp:</span>
                            <span class="font-mono text-white">{{ $proposal->pic_phone }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-800">
                            <span class="text-slate-400">Dukungan Diminta:</span>
                            <span class="font-semibold text-amber-300">{{ $proposal->support_type_requested }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer WhatsApp Action -->
                <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <span class="text-xs text-slate-400 text-center sm:text-left">
                        Ada pertanyaan atau perubahan jadwal? Konfirmasi langsung ke PIC resmi UBSI.
                    </span>
                    <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}" target="_blank"
                        class="w-full sm:w-auto inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/20 transition">
                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                        <span>WhatsApp PIC Resmi {{ $targetCampus?->name ?? 'UBSI' }}</span>
                    </a>
                </div>
            </div>
        @endif

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

