<x-app-layout title="User Management">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">User Management &amp; Hak Akses</h1>
            <p class="text-xs text-slate-500">Kelola akun pengguna, tim marketing, pimpinan &amp; administrator</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- User Table List (8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800">Daftar Pengguna Sistem</h3>
                <span class="px-2.5 py-1 text-xs font-bold bg-brand-50 text-brand-700 rounded-lg">{{ $users->total() }} Pengguna</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900 text-white font-bold uppercase">
                        <tr>
                            <th class="py-3.5 px-4">Nama Pengguna</th>
                            <th class="py-3.5 px-4">Email / Telepon</th>
                            <th class="py-3.5 px-4">Role Akses</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $u)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    {{ $u->name }}
                                    <span class="text-[10px] text-slate-400 block font-normal">
                                        {{ $u->school_visits_count }} Kunjungan &bull; {{ $u->prospects_count }} Prospek
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $u->email }}
                                    <span class="text-[10px] text-slate-400 block">{{ $u->phone ?? '-' }}</span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $roleColors = [
                                            'Admin' => 'bg-purple-100 text-purple-700 border-purple-200',
                                            'Pimpinan' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'Koordinator Marketing' => 'bg-blue-100 text-blue-700 border-blue-200',
                                            'Marketing' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                            'Customer Service' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                        ];
                                        $roleName = $u->role ? $u->role->name : 'No Role';
                                        $badgeColor = $roleColors[$roleName] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $badgeColor }}">
                                        {{ $roleName }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($u->status === 'active')
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded-full">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-bold bg-red-100 text-red-800 rounded-full">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <!-- Tombol Edit Pengguna -->
                                        <button type="button"
                                            data-user="{{ json_encode([
                                                'id' => $u->id,
                                                'name' => $u->name,
                                                'email' => $u->email,
                                                'role_id' => $u->role_id,
                                                'phone' => $u->phone,
                                                'status' => $u->status,
                                                'is_self' => ($u->id === auth()->id())
                                            ]) }}"
                                            onclick="openEditUserModal(JSON.parse(this.dataset.user))"
                                            class="p-1.5 bg-brand-50 hover:bg-brand-100 text-brand-600 rounded-lg transition"
                                            title="Edit Pengguna">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </button>

                                        @if($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $u->id) }}" onsubmit="return confirm('Hapus user {{ $u->name }}?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition" title="Hapus Pengguna">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">Belum ada pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        </div>

        <!-- Add Form (4 Cols) -->
        <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft h-fit">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100">Tambah Pengguna Baru</h3>
            <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Role Hak Akses <span class="text-red-500">*</span></label>
                    <select name="role_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        <option value="">-- Pilih Role --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor HP/WA</label>
                    <input type="text" name="phone" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Akun</label>
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 bg-brand-navy hover:bg-brand-900 text-white font-bold text-xs rounded-xl transition">
                    Simpan Pengguna
                </button>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL EDIT DATA PENGGUNA ==================== -->
    <div id="modalEditUser" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                        <i data-lucide="user-cog" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Edit Data Pengguna &amp; Hak Akses</h3>
                        <p class="text-[11px] text-slate-500">Perbarui profil pengguna, hak akses role, status, atau password</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditUser" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" id="edit_email" name="email" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor HP / WhatsApp</label>
                        <input type="text" id="edit_phone" name="phone" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Password Baru <span class="text-[11px] font-normal text-slate-400">(Kosongkan jika tidak ingin mengubah password)</span>
                    </label>
                    <input type="password" id="edit_password" name="password" placeholder="Minimal 6 karakter..." class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Role Hak Akses <span class="text-red-500">*</span></label>
                        <select id="edit_role_id" name="role_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Akun <span class="text-red-500">*</span></label>
                        <select id="edit_status" name="status" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div id="selfAccountNotice" class="hidden p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-800 flex items-start space-x-2">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-amber-600 shrink-0 mt-0.5"></i>
                    <span>Ini adalah akun Anda sendiri. Role Admin dan status Aktif tidak dapat diubah demi keamanan sistem.</span>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-brand-navy hover:bg-brand-900 text-white rounded-xl shadow-soft hover:shadow-md transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openEditUserModal(user) {
            const form = document.getElementById('formEditUser');
            form.action = `/users/${user.id}`;

            document.getElementById('edit_name').value = user.name || '';
            document.getElementById('edit_email').value = user.email || '';
            document.getElementById('edit_phone').value = user.phone || '';
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_role_id').value = user.role_id || '';
            document.getElementById('edit_status').value = user.status || 'active';

            const notice = document.getElementById('selfAccountNotice');
            const roleSelect = document.getElementById('edit_role_id');
            const statusSelect = document.getElementById('edit_status');

            if (user.is_self) {
                notice.classList.remove('hidden');
                roleSelect.classList.add('bg-slate-100', 'pointer-events-none');
                statusSelect.classList.add('bg-slate-100', 'pointer-events-none');
            } else {
                notice.classList.add('hidden');
                roleSelect.classList.remove('bg-slate-100', 'pointer-events-none');
                statusSelect.classList.remove('bg-slate-100', 'pointer-events-none');
            }

            document.getElementById('modalEditUser').classList.remove('hidden');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        function closeEditUserModal() {
            document.getElementById('modalEditUser').classList.add('hidden');
        }

        // Close on backdrop click
        document.getElementById('modalEditUser')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditUserModal();
            }
        });
    </script>
    @endpush
</x-app-layout>
