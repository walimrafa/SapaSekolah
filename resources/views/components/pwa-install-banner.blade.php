@php
    $appSettings = \App\Services\AppSettingService::all();
    $pwaLogoUrl = !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('logo-sapa-sekolah.png');
@endphp
<div x-data="pwaInstallManager()" x-show="showBanner" x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-8 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 translate-y-8 scale-95"
    class="fixed bottom-20 md:bottom-6 right-4 left-4 md:left-auto md:max-w-md z-50 pointer-events-auto">

    <div class="bg-slate-900/95 backdrop-blur-xl border border-slate-700/80 rounded-2xl p-4 shadow-2xl text-white relative overflow-hidden">
        <!-- Glow accents -->
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-brand-crimson/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-brand-navy/40 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex items-start gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-slate-800/80 border border-slate-700 p-1 shrink-0 flex items-center justify-center shadow-md">
                <img src="{{ $pwaLogoUrl }}" alt="Logo SAPA Sekolah" class="w-full h-full object-contain" onerror="this.src='{{ asset('logo-sapa-sekolah.png') }}'">
            </div>

            <div class="flex-1 min-w-0 pr-6">
                <div class="flex items-center gap-2 mb-0.5">
                    <h4 class="text-xs font-extrabold text-white tracking-tight">Pasang Aplikasi SAPA Sekolah</h4>
                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-brand-crimson/30 text-rose-300 border border-brand-crimson/40 uppercase">PWA</span>
                </div>
                <p class="text-[11px] text-slate-300 leading-snug">
                    Akses cepat operasional lapangan langsung dari layar utama HP/laptop Anda tanpa perlu browser.
                </p>

                <!-- Action Buttons -->
                <div class="mt-3 flex items-center gap-2">
                    <button type="button" @click="installApp()"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold bg-gradient-to-r from-brand-crimson to-rose-600 hover:from-rose-500 hover:to-brand-crimson text-white shadow-md shadow-brand-crimson/30 transition flex items-center gap-1.5">
                        <i data-lucide="download-cloud" class="w-3.5 h-3.5"></i>
                        <span>Install Sekarang</span>
                    </button>

                    <button type="button" @click="dismissBanner()"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                        Nanti Saja
                    </button>
                </div>

                <!-- iOS Hint (if on Safari iOS) -->
                <div x-show="isIos" class="mt-2 text-[10px] text-amber-300 flex items-center gap-1">
                    <i data-lucide="info" class="w-3 h-3 shrink-0"></i>
                    <span>Di iOS Safari: Tekan tombol <strong>Bagikan (Share)</strong> lalu pilih <strong>Tambah ke Layar Utama</strong>.</span>
                </div>
            </div>

            <!-- Close icon button -->
            <button type="button" @click="dismissBanner()"
                class="absolute top-2.5 right-2.5 text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition"
                title="Tutup Notifikasi">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>

<script>
    function pwaInstallManager() {
        return {
            deferredPrompt: null,
            showBanner: false,
            isIos: false,
            async init() {
                // 1. Cek apakah aplikasi sudah terinstal di perangkat ini sebelumnya
                if (this.isAlreadyInstalled()) {
                    localStorage.setItem('sapa_pwa_installed', 'true');
                    this.showBanner = false;
                    return;
                }

                // 2. Cek API getInstalledRelatedApps jika browser mendukung (Chromium/Android)
                if ('getInstalledRelatedApps' in navigator) {
                    try {
                        const relatedApps = await navigator.getInstalledRelatedApps();
                        if (relatedApps && relatedApps.length > 0) {
                            localStorage.setItem('sapa_pwa_installed', 'true');
                            this.showBanner = false;
                            return;
                        }
                    } catch (e) {
                        // Abaikan jika ditolak / tidak didukung
                    }
                }

                // 3. Cek apakah user pernah menolak/menutup notif (cooldown agar tidak mengganggu)
                const dismissedAt = localStorage.getItem('sapa_pwa_dismissed_at');
                if (dismissedAt) {
                    const dismissCount = parseInt(localStorage.getItem('sapa_pwa_dismiss_count') || '1', 10);
                    const cooldownDays = dismissCount >= 2 ? 30 : 14;
                    const diffDays = (Date.now() - parseInt(dismissedAt, 10)) / (1000 * 60 * 60 * 24);
                    if (diffDays < cooldownDays) {
                        return;
                    }
                }

                // 4. Deteksi iOS Safari
                const userAgent = window.navigator.userAgent.toLowerCase();
                const isIosDevice = /iphone|ipad|ipod/.test(userAgent);
                const isSafari = isIosDevice && !/crios|fxios|opios/.test(userAgent) && /safari/.test(userAgent);
                this.isIos = isSafari;

                // 5. Tangkap event bawaan instalasi selesai (appinstalled)
                window.addEventListener('appinstalled', () => {
                    localStorage.setItem('sapa_pwa_installed', 'true');
                    this.showBanner = false;
                    this.deferredPrompt = null;
                });

                // 6. Tangkap event prompt instalasi Chromium (sebelum aplikasi diinstal)
                window.addEventListener('beforeinstallprompt', (e) => {
                    // Jika sudah pernah terinstal pada device, jangan munculkan lagi banner
                    if (this.isAlreadyInstalled()) {
                        return;
                    }

                    e.preventDefault();
                    this.deferredPrompt = e;
                    this.showBanner = true;
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                });

                // 7. Di iOS Safari, tampilkan petunjuk instalasi hanya jika belum terinstal
                if (this.isIos && !this.isAlreadyInstalled()) {
                    setTimeout(() => {
                        if (!this.isAlreadyInstalled()) {
                            this.showBanner = true;
                            this.$nextTick(() => {
                                if (window.lucide) window.lucide.createIcons();
                            });
                        }
                    }, 3500);
                }
            },

            // Cek kondisi apakah perangkat sudah menginstal PWA
            isAlreadyInstalled() {
                // Mode standalone (PWA window terbuka)
                const isStandalone = window.matchMedia('(display-mode: standalone)').matches
                    || window.navigator.standalone === true
                    || document.referrer.includes('android-app://');

                // Flag lokal bahwa device ini sudah pernah menginstal
                const wasInstalledOnDevice = localStorage.getItem('sapa_pwa_installed') === 'true';

                return isStandalone || wasInstalledOnDevice;
            },

            async installApp() {
                if (this.deferredPrompt) {
                    this.deferredPrompt.prompt();
                    const { outcome } = await this.deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        // Tandai permanen bahwa perangkat telah menginstal aplikasi
                        localStorage.setItem('sapa_pwa_installed', 'true');
                        this.showBanner = false;
                    }
                    this.deferredPrompt = null;
                } else if (this.isIos) {
                    alert("Untuk menginstall di iPhone/iPad:\n1. Tekan tombol Bagikan (ikon kotak panah ke atas di Safari).\n2. Pilih 'Tambah ke Layar Utama' (Add to Home Screen).\n\nSetelah dipasang, notifikasi ini tidak akan muncul lagi.");
                    // Tandai agar tidak terus muncul berulang kali di iOS setelah panduan dilihat
                    localStorage.setItem('sapa_pwa_dismissed_at', Date.now().toString());
                    this.showBanner = false;
                }
            },

            dismissBanner() {
                this.showBanner = false;
                localStorage.setItem('sapa_pwa_dismissed_at', Date.now().toString());
                const count = parseInt(localStorage.getItem('sapa_pwa_dismiss_count') || '0', 10) + 1;
                localStorage.setItem('sapa_pwa_dismiss_count', count.toString());
            }
        };
    }
</script>
