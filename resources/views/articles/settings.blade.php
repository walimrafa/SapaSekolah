<x-app-layout title="Pengaturan AI & Prompt — WordCraft AI">
    <div class="max-w-4xl mx-auto space-y-6">

        <!-- Navigation Breadcrumb & Title -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('articles.index') }}" class="hover:text-amber-600 transition">Artikel AI</a>
                    <span>/</span>
                    <span class="text-slate-700">Pengaturan</span>
                </nav>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center space-x-2.5">
                    <span class="w-8 h-8 rounded-xl bg-sky-500/20 text-sky-600 flex items-center justify-center">
                        <i data-lucide="bot" class="w-4 h-4"></i>
                    </span>
                    <span>Konfigurasi AI & Template Prompt</span>
                </h1>
            </div>

            <a href="{{ route('articles.index') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-600 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl transition shadow-xs flex items-center space-x-1.5 self-start">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Antrean</span>
            </a>
        </div>

        <form action="{{ route('articles.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Card 1: API Keys & AI Model -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-5">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                    <span class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 font-black text-xs flex items-center justify-center">1</span>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Kredensial Layanan AI & Gambar</h2>
                        <p class="text-xs text-slate-500">Kunci API Gemini Google AI dan penyedia gambar eksternal (Pexels)</p>
                    </div>
                </div>

                <!-- Gemini API Keys -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="gemini_api_keys" class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center space-x-2">
                            <span>Google Gemini API Keys</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700">Multi-Key Failover</span>
                        </label>
                        <span class="text-[11px] text-slate-500 font-semibold">
                            Terdeteksi: <strong class="text-sky-700">{{ count($keys) }} Key Aktif</strong>
                        </span>
                    </div>

                    <textarea id="gemini_api_keys" name="gemini_api_keys" rows="3"
                        placeholder="Masukkan satu atau beberapa API key Gemini, pisahkan dengan baris baru (enter), koma, atau titik koma"
                        class="w-full px-3.5 py-2.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition leading-relaxed">{{ old('gemini_api_keys', $settings['gemini_api_keys'] ?? '') }}</textarea>

                    <p class="text-[11px] text-slate-500 leading-normal">
                        💡 <strong>Tips:</strong> Anda dapat memasukkan lebih dari satu API key. Jika salah satu key mencapai batas kuota (rate limit / 429), sistem otomatis beralih ke key berikutnya secara mulus.
                    </p>
                </div>

                <!-- Gemini Model Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <div class="space-y-1.5">
                        <label for="gemini_model" class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Model Gemini AI
                        </label>
                        <select id="gemini_model" name="gemini_model"
                            class="w-full px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition font-medium">
                            <option value="gemini-1.5-flash" {{ ($settings['gemini_model'] ?? '') === 'gemini-1.5-flash' ? 'selected' : '' }}>gemini-1.5-flash (Cepat, Efisien & Direkomendasikan)</option>
                            <option value="gemini-2.0-flash" {{ ($settings['gemini_model'] ?? '') === 'gemini-2.0-flash' ? 'selected' : '' }}>gemini-2.0-flash (Generasi Terbaru)</option>
                            <option value="gemini-1.5-pro" {{ ($settings['gemini_model'] ?? '') === 'gemini-1.5-pro' ? 'selected' : '' }}>gemini-1.5-pro (Analisis Mendalam & Komprehensif)</option>
                        </select>
                    </div>

                    <!-- Pexels API Key -->
                    <div class="space-y-1.5">
                        <label for="pexels_api_key" class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                            <span>Pexels API Key (Gambar Sampul)</span>
                            <span class="text-[10px] text-slate-400 font-normal">Opsional</span>
                        </label>
                        <input type="text" id="pexels_api_key" name="pexels_api_key"
                            value="{{ old('pexels_api_key', $settings['pexels_api_key'] ?? '') }}"
                            placeholder="Kunci API Pexels untuk pengadaan foto sampul otomatis"
                            class="w-full px-3.5 py-2.5 text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition">
                        <p class="text-[11px] text-slate-500">Jika kosong atau pencarian tidak ditemukan, sistem menggunakan gambar sampul grafis bawaan.</p>
                    </div>
                </div>
            </div>

            <!-- Card 2: Prompt Master Template -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                    <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 font-black text-xs flex items-center justify-center">2</span>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Template Prompt Standar</h2>
                        <p class="text-xs text-slate-500">Rumusan instruksi AI dengan dukungan substitusi tag dinamis</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <label for="prompt_template" class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Isi Template Prompt
                        </label>
                        
                        <!-- Dynamic Variable Tags -->
                        <div class="flex items-center space-x-1.5">
                            <span class="text-[11px] text-slate-400">Variabel:</span>
                            <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-mono text-[10px] font-bold">[JUDUL]</span>
                            <span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-mono text-[10px] font-bold">[GAYA]</span>
                            <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 font-mono text-[10px] font-bold">[KAMPUS]</span>
                        </div>
                    </div>

                    <textarea id="prompt_template" name="prompt_template" rows="8" required
                        class="w-full px-4 py-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition font-mono leading-relaxed">{{ old('prompt_template', $settings['prompt_template'] ?? '') }}</textarea>

                    <p class="text-[11px] text-slate-500">
                        Tag <code class="text-amber-700 font-bold">[JUDUL]</code>, <code class="text-sky-700 font-bold">[GAYA]</code>, dan <code class="text-purple-700 font-bold">[KAMPUS]</code> akan digantikan otomatis dengan input form saat proses generasi berlangsung.
                    </p>
                </div>
            </div>

            <!-- Card 3: Call to Action (CTA) Khusus -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-4">
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 font-black text-xs flex items-center justify-center">3</span>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Call to Action (CTA) & Pesan Penutup</h2>
                        <p class="text-xs text-slate-500">Teks ajakan aksi / kontak pendaftaran yang disisipkan di akhir dokumen Word</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="space-y-1">
                        <label for="cta_ubsi_karawang" class="text-xs font-bold text-slate-700">CTA Khusus: UBSI Karawang</label>
                        <textarea id="cta_ubsi_karawang" name="cta_ubsi_karawang" rows="3" required
                            class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition leading-relaxed">{{ old('cta_ubsi_karawang', $settings['cta_ubsi_karawang'] ?? '') }}</textarea>
                    </div>

                    <div class="space-y-1">
                        <label for="cta_ubsi_cikampek" class="text-xs font-bold text-slate-700">CTA Khusus: UBSI Cikampek</label>
                        <textarea id="cta_ubsi_cikampek" name="cta_ubsi_cikampek" rows="3" required
                            class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition leading-relaxed">{{ old('cta_ubsi_cikampek', $settings['cta_ubsi_cikampek'] ?? '') }}</textarea>
                    </div>

                    <div class="space-y-1">
                        <label for="cta_signaltodays" class="text-xs font-bold text-slate-700">CTA Khusus: Signaltodays</label>
                        <textarea id="cta_signaltodays" name="cta_signaltodays" rows="3" required
                            class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition leading-relaxed">{{ old('cta_signaltodays', $settings['cta_signaltodays'] ?? '') }}</textarea>
                    </div>

                    <div class="space-y-1">
                        <label for="cta_general" class="text-xs font-bold text-slate-700">Pesan Penutup: Artikel Umum</label>
                        <textarea id="cta_general" name="cta_general" rows="2" required
                            class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition leading-relaxed">{{ old('cta_general', $settings['cta_general'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
                <button type="submit" 
                    class="inline-flex items-center space-x-2 px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-lg transition duration-150">
                    <i data-lucide="save" class="w-4 h-4 text-emerald-400"></i>
                    <span>Simpan Pengaturan AI</span>
                </button>
            </div>

        </form>

    </div>
</x-app-layout>
