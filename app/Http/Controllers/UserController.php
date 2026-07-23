<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Daftar semua user dengan search dan filter fungsi.
     * $allUsers dibutuhkan blade untuk menampilkan ringkasan per fungsi.
     */
    public function index(Request $request)
    {
        // Semua user tanpa filter — untuk cards ringkasan per fungsi di blade
        $allUsers = User::all();

        $query = User::query();

        if ($request->filled('fungsi')) {
            $query->where('fungsi', $request->fungsi);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('pages.users.index', compact('users', 'allUsers'));
    }

    /**
     * Tambah user baru.
     * Validasi via StoreUserRequest.
     */
    public function store(StoreUserRequest $request)
    {
        User::create([
            'name'      => $request->name,
            'username'  => $request->username,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'fungsi'    => $request->fungsi,
            'is_active' => true,
        ]);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Update data user.
     * Validasi via UpdateUserRequest.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->name     = $request->name;
        $user->username = $request->username;
        $user->role     = $request->role;
        $user->fungsi   = $request->fungsi;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Data user berhasil diubah.');
    }

    /**
     * Toggle aktif/nonaktif user.
     *
     * Business Rule #5 Fix: Admin HSSE tidak dapat menonaktifkan akunnya sendiri.
     * Mencegah situasi tidak ada Admin HSSE yang aktif.
     */
    public function destroy(User $user)
    {
        // Guard: tidak boleh nonaktifkan akun sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun berhasil {$status}.");
    }
}
