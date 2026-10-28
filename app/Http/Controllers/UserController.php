<?php

namespace App\Http\Controllers;

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
        $query = User::with('role')->withCount(['schoolVisits', 'followUps', 'prospects']);

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
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('toast', ['type' => 'success', 'message' => 'Pengguna baru berhasil ditambahkan.']);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

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
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('toast', ['type' => 'danger', 'message' => 'Anda tidak bisa menghapus akun Anda sendiri.']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('toast', ['type' => 'success', 'message' => "Pengguna {$name} telah dihapus."]);
    }
}
