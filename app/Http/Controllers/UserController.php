<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $currentUser = auth()->user();
        $query = User::with(['role', 'campus'])->withCount(['schoolVisits', 'followUps', 'prospects']);

        // Isolasi Ketat: Pengguna non-admin (misal Pimpinan Kampus) HANYA melihat user kampusnya sendiri
        if (!$currentUser->isAdmin()) {
            $campusId = $currentUser->campus_id ?? \App\Services\AppContextService::getActiveCampus()?->id;
            $query->where('campus_id', $campusId);
        } elseif ($request->filled('campus_id')) {
            if ($request->campus_id === 'all') {
                $query->whereNull('campus_id');
            } else {
                $query->where('campus_id', $request->campus_id);
            }
        } elseif (\App\Services\AppContextService::getActiveCampus() && session('selected_campus_id') !== 'ALL') {
            $query->where('campus_id', \App\Services\AppContextService::getActiveCampus()->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        $users = $query->paginate(15)->withQueryString();

        // Roles yang dapat dipilih (Pengguna non-admin tidak dapat membuat akun Admin)
        $rolesQuery = Role::query();
        if (!$currentUser->isAdmin()) {
            $rolesQuery->where('name', '!=', 'Admin');
        }
        $roles = $rolesQuery->get();

        // Kampus yang tersedia
        if (!$currentUser->isAdmin()) {
            $campuses = Campus::where('id', $currentUser->campus_id)->get();
        } else {
            $campuses = Campus::where('is_active', true)->orderBy('name')->get();
        }

        return view('users.index', compact('users', 'roles', 'campuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        if (!$currentUser->isAdmin()) {
            // Otomatis kunci ke kampus akun yang sedang menginput
            $validated['campus_id'] = $currentUser->campus_id ?? \App\Services\AppContextService::getActiveCampus()?->id;

            $adminRole = Role::where('name', 'Admin')->first();
            if ($adminRole && (int) $validated['role_id'] === $adminRole->id) {
                return back()->with('toast', [
                    'type' => 'danger',
                    'message' => 'Anda tidak memiliki wewenang untuk membuat akun Administrator.',
                ]);
            }
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('toast', ['type' => 'success', 'message' => 'Pengguna baru berhasil ditambahkan.']);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'campus_id' => ['nullable', 'exists:campuses,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        if (!$currentUser->isAdmin()) {
            if ($user->campus_id !== $currentUser->campus_id) {
                abort(403, 'Akses ditolak. Anda hanya dapat mengelola staf dari kampus Anda sendiri.');
            }
            $validated['campus_id'] = $currentUser->campus_id;

            $adminRole = Role::where('name', 'Admin')->first();
            if ($adminRole && (int) $validated['role_id'] === $adminRole->id) {
                return back()->with('toast', [
                    'type' => 'danger',
                    'message' => 'Anda tidak memiliki wewenang untuk menetapkan role Administrator.',
                ]);
            }
        }

        if ($user->id === auth()->id()) {
            if ($validated['status'] === 'inactive') {
                return redirect()->route('users.index')
                    ->with('toast', ['type' => 'danger', 'message' => 'Anda tidak bisa menonaktifkan akun Anda sendiri.']);
            }
            $adminRole = Role::where('name', 'Admin')->first();
            if ($adminRole && (int) $validated['role_id'] !== $adminRole->id) {
                return redirect()->route('users.index')
                    ->with('toast', ['type' => 'danger', 'message' => 'Anda tidak bisa mencopot role Admin dari akun Anda sendiri.']);
            }
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('toast', ['type' => 'success', 'message' => "Data pengguna {$user->name} diperbarui."]);
    }

    public function destroy(User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('toast', ['type' => 'danger', 'message' => 'Anda tidak bisa menghapus akun Anda sendiri.']);
        }

        if (!$currentUser->isAdmin()) {
            if ($user->campus_id !== $currentUser->campus_id) {
                abort(403, 'Akses ditolak. Anda hanya dapat menghapus staf dari kampus Anda sendiri.');
            }
            if ($user->isAdmin()) {
                abort(403, 'Akses ditolak. Akun Administrator tidak dapat dihapus.');
            }
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('toast', ['type' => 'success', 'message' => "Pengguna {$name} telah dihapus."]);
    }
}
