<x-app-layout title="Master Download QR Code">
    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Master Pusat Unduh QR Code</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola &amp; unduh QR Code resmi untuk akses login, kuisioner siswa, dan portal input kunjungan lapangan</p>
        </div>
    </div>

    <!-- Grid 2 QR Code Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Card 1: QR Code Untuk Login Sistem -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden flex flex-col justify-between" x-data="{ copied: false }">
            <div class="p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-brand-50 text-brand-600 rounded-2xl flex items-center justify-center font-bold">
                            <i data-lucide="key-round" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">1. QR Code Akses Login</h3>
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-brand-100 text-brand-800 rounded-full">Diperlukan Login</span>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">#01-LOGIN</span>
                </div>

                <p class="text-xs text-slate-600 mb-6">
                    QR Code ini digunakan oleh staf marketing, koordinator, dan pimpinan untuk masuk (*login*) ke dashboard SAPA Sekolah langsung via perangkat seluler.
                </p>

                <!-- Big QR Display -->
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 text-center shadow-inner mb-4">
                    <div class="inline-block p-3 bg-white rounded-2xl shadow-md border border-slate-200">
                        <img src="{{ $loginQrUrl }}" alt="QR Code Login SAPA Sekolah" class="w-52 h-52 mx-auto rounded-xl">
                    </div>
                    <p class="text-xs font-mono font-bold text-brand-700 mt-3 truncate px-2">
                        {{ $loginUrl }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons Footer -->
            <div class="p-6 bg-slate-50 border-t border-slate-100 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('qr-codes.download', 'login') }}" 
                        class="py-2.5 px-4 bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Unduh PNG HD</span>
                    </a>
                    
                    <button onclick="printQrCode('{{ $loginQrUrl }}', 'Akses Login Sistem SAPA Sekolah', '{{ $loginUrl }}', 'Scan QR Code ini untuk membuka halaman Login SAPA Sekolah.')" type="button"
                        class="py-2.5 px-4 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak Poster A4</span>
                    </button>
                </div>

                <button @click="navigator.clipboard.writeText('{{ $loginUrl }}'); copied = true; setTimeout(() => copied = false, 2000)" type="button"
                    class="w-full py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span x-text="copied ? '✓ Link Login Berhasil Disalin!' : 'Salin URL Link Login'"></span>
                </button>
            </div>
        </div>

        <!-- Card 2: QR Code Untuk Input Kunjungan Lapangan (Tanpa Login) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden flex flex-col justify-between" x-data="{ copied: false }">
            <div class="p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center font-bold">
                            <i data-lucide="qr-code" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">2. QR Code Input Kunjungan</h3>
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded-full">Public (Tanpa Login)</span>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">#02-PUBLIC</span>
                </div>

                <p class="text-xs text-slate-600 mb-6">
                    QR Code ini dapat ditempel atau dibagikan ke petugas di lapangan untuk membuka form pencatatan hasil kunjungan sosialisasi sekolah **secara langsung tanpa perlu login**.
                </p>

                <!-- Big QR Display -->
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 text-center shadow-inner mb-4">
                    <div class="inline-block p-3 bg-white rounded-2xl shadow-md border border-slate-200">
                        <img src="{{ $publicVisitQrUrl }}" alt="QR Code Input Kunjungan SAPA Sekolah" class="w-52 h-52 mx-auto rounded-xl">
                    </div>
                    <p class="text-xs font-mono font-bold text-amber-700 mt-3 truncate px-2">
                        {{ $publicVisitUrl }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons Footer -->
            <div class="p-6 bg-slate-50 border-t border-slate-100 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('qr-codes.download', 'public-visit') }}" 
                        class="py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-slate-900 font-extrabold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Unduh PNG HD</span>
                    </a>
                    
                    <button onclick="printQrCode('{{ $publicVisitQrUrl }}', 'Portal Input Hasil Kunjungan Lapangan', '{{ $publicVisitUrl }}', 'Scan QR Code ini dari Smartphone Anda untuk membuka formulir hasil sosialisasi sekolah tanpa perlu login.')" type="button"
                        class="py-2.5 px-4 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak Poster A4</span>
                    </button>
                </div>

                <button @click="navigator.clipboard.writeText('{{ $publicVisitUrl }}'); copied = true; setTimeout(() => copied = false, 2000)" type="button"
                    class="w-full py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span x-text="copied ? '✓ Link Portal Lapangan Berhasil Disalin!' : 'Salin URL Link Portal Lapangan'"></span>
                </button>
            </div>
        </div>

        <!-- Card 3: QR Code Kuisioner Siswa -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden flex flex-col justify-between" x-data="{ copied: false }">
            <div class="p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center font-bold">
                            <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">3. QR Code Kuisioner Siswa</h3>
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-purple-100 text-purple-800 rounded-full">Public (Tanpa Login)</span>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">#03-SISWA</span>
                </div>

                <p class="text-xs text-slate-600 mb-6">
                    Bagikan atau cetak QR Code ini agar siswa dapat langsung membuka dan mengisi kuisioner minat kuliah UBSI melalui ponsel tanpa perlu login.
                </p>

                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 text-center shadow-inner mb-4">
                    <div class="inline-block p-3 bg-white rounded-2xl shadow-md border border-slate-200">
                        <img src="{{ $studentQuestionnaireQrUrl }}" alt="QR Code Kuisioner Siswa SAPA Sekolah" class="w-52 h-52 mx-auto rounded-xl">
                    </div>
                    <p class="text-xs font-mono font-bold text-purple-700 mt-3 truncate px-2">
                        {{ $studentQuestionnaireUrl }}
                    </p>
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-100 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('qr-codes.download', 'student-questionnaire') }}"
                        class="py-2.5 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Unduh PNG HD</span>
                    </a>
                    <button onclick="printQrCode('{{ $studentQuestionnaireQrUrl }}', 'Kuisioner Minat Kuliah Siswa UBSI', '{{ $studentQuestionnaireUrl }}', 'Scan QR Code ini untuk membuka dan mengisi kuisioner minat kuliah UBSI tanpa perlu login.')" type="button"
                        class="py-2.5 px-4 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak Poster A4</span>
                    </button>
                </div>

                <button @click="navigator.clipboard.writeText('{{ $studentQuestionnaireUrl }}'); copied = true; setTimeout(() => copied = false, 2000)" type="button"
                    class="w-full py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span x-text="copied ? '✓ Link Kuisioner Berhasil Disalin!' : 'Salin URL Link Kuisioner'"></span>
                </button>
            </div>
        <!-- Card 4: QR Code Pengajuan Proposal Event Sekolah (Public / Tanpa Login) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden flex flex-col justify-between" x-data="{ copied: false }">
            <div class="p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center font-bold">
                            <i data-lucide="file-plus" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">4. QR Code Pengajuan Event</h3>
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-800 rounded-full">Public Sekolah (Tanpa Login)</span>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">#04-PROPOSAL</span>
                </div>

                <p class="text-xs text-slate-600 mb-6">
                    Bagikan QR Code ini kepada panitia OSIS, Guru, atau Kepala Sekolah agar pihak sekolah dapat mengajukan permohonan sponsorship / support event ke UBSI langsung secara online.
                </p>

                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 text-center shadow-inner mb-4">
                    <div class="inline-block p-3 bg-white rounded-2xl shadow-md border border-slate-200">
                        <img src="{{ $schoolEventQrUrl }}" alt="QR Code Pengajuan Event Sekolah" class="w-52 h-52 mx-auto rounded-xl">
                    </div>
                    <p class="text-xs font-mono font-bold text-blue-700 mt-3 truncate px-2">
                        {{ $schoolEventUrl }}
                    </p>
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-100 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('qr-codes.download', 'school-events') }}"
                        class="py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Unduh PNG HD</span>
                    </a>
                    <button onclick="printQrCode('{{ $schoolEventQrUrl }}', 'Pengajuan Proposal Event Sekolah', '{{ $schoolEventUrl }}', 'Scan QR Code ini untuk mengajukan permohonan sponsor dan support kegiatan sekolah dari Universitas BSI secara online tanpa login.')" type="button"
                        class="py-2.5 px-4 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak Poster A4</span>
                    </button>
                </div>

                <button @click="navigator.clipboard.writeText('{{ $schoolEventUrl }}'); copied = true; setTimeout(() => copied = false, 2000)" type="button"
                    class="w-full py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span x-text="copied ? '✓ Link Pengajuan Event Berhasil Disalin!' : 'Salin URL Pengajuan Event'"></span>
                </button>
            </div>
        </div>

        <!-- Card 5: QR Code Hasil Support Event Siswa (Public / Tanpa Login) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden flex flex-col justify-between" x-data="{ copied: false }">
            <div class="p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center font-bold">
                            <i data-lucide="sparkles" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">5. QR Code Form Siswa Event</h3>
                            <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded-full">Public Siswa (Tanpa Kuisioner)</span>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">#05-EVENT-STUDENT</span>
                </div>

                <p class="text-xs text-slate-600 mb-6">
                    Pajang atau cetak QR Code ini di stand / booth kegiatan support event sekolah. Siswa cukup scan untuk mendaftar data diri &amp; minat beasiswa UBSI, langsung tersinkron ke Follow Up Siswa.
                </p>

                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 text-center shadow-inner mb-4">
                    <div class="inline-block p-3 bg-white rounded-2xl shadow-md border border-slate-200">
                        <img src="{{ $eventStudentQrUrl }}" alt="QR Code Hasil Support Event Siswa" class="w-52 h-52 mx-auto rounded-xl">
                    </div>
                    <p class="text-xs font-mono font-bold text-emerald-700 mt-3 truncate px-2">
                        {{ $eventStudentUrl }}
                    </p>
                </div>
            </div>

            <div class="p-6 bg-slate-50 border-t border-slate-100 space-y-2">
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('qr-codes.download', 'event-students') }}"
                        class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Unduh PNG HD</span>
                    </a>
                    <button onclick="printQrCode('{{ $eventStudentQrUrl }}', 'Pendaftaran Siswa Booth Support Event UBSI', '{{ $eventStudentUrl }}', 'Scan QR Code ini dari Smartphone Anda untuk mengisi formulir pendaftaran siswa & minat beasiswa UBSI di booth event sekolah.')" type="button"
                        class="py-2.5 px-4 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak Poster A4</span>
                    </button>
                </div>

                <button @click="navigator.clipboard.writeText('{{ $eventStudentUrl }}'); copied = true; setTimeout(() => copied = false, 2000)" type="button"
                    class="w-full py-2 bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 cursor-pointer">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span x-text="copied ? '✓ Link Form Siswa Berhasil Disalin!' : 'Salin URL Form Siswa Event'"></span>
                </button>
            </div>
        </div>

    </div>

    <!-- JS Helper for Printing Poster -->
    <script>
        function printQrCode(qrImgUrl, title, targetUrl, description) {
            const printWindow = window.open('', '_blank', 'width=800,height=900');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print QR Code - ${title}</title>
                    <style>
                        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; text-align: center; padding: 40px; color: #0f172a; }
                        .container { border: 4px solid #0f172a; border-radius: 24px; padding: 40px; max-w: 600px; margin: auto; }
                        h1 { font-size: 26px; font-weight: 800; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
                        h2 { font-size: 16px; color: #d97706; margin-top: 0; text-transform: uppercase; font-weight: 700; }
                        p { font-size: 14px; color: #475569; margin: 15px 0; line-height: 1.5; }
                        .qr-box { background: #f8fafc; padding: 20px; border-radius: 20px; border: 2px border-style: dashed; border-color: #cbd5e1; display: inline-block; margin: 20px 0; }
                        .qr-box img { width: 300px; height: 300px; }
                        .url { font-family: monospace; font-size: 14px; font-weight: bold; color: #1e3a8a; background: #e0e7ff; padding: 8px 16px; border-radius: 10px; display: inline-block; word-break: break-all; }
                        .footer { margin-top: 30px; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>SAPA SEKOLAH KARAWANG</h1>
                        <h2>${title}</h2>
                        <p>${description}</p>
                        
                        <div class="qr-box">
                            <img src="${qrImgUrl}" alt="QR Code">
                        </div>

                        <div>
                            <span class="url">${targetUrl}</span>
                        </div>

                        <div class="footer">
                            Perguruan Tinggi Marketing Mapping System &bull; Universitas Bina Sarana Informatika (UBSI) Kampus Karawang
                        </div>
                    </div>
                    <script>
                        window.onload = function() {
                            window.print();
                        }
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
</x-app-layout>
