<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $allUsers = User::all();
        
        $query = User::query();
        
        if ($request->filled('fungsi')) {
            $query->where('fungsi', $request->fungsi);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
        
        $users = $query->get();
        return view('pages.users.index', compact('users', 'allUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(['Admin HSSE', 'Admin Function', 'Manager HSSE', 'Manager Function'])],
            'fungsi' => ['required', Rule::in(['Operation', 'Maintenance', 'HSSE', 'Business Support'])],
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'fungsi' => $request->fungsi,
            'is_active' => true,
        ]);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['Admin HSSE', 'Admin Function', 'Manager HSSE', 'Manager Function'])],
            'fungsi' => ['required', Rule::in(['Operation', 'Maintenance', 'HSSE', 'Business Support'])],
        ];

        // Only validate password if it is being changed
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6';
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->role = $request->role;
        $user->fungsi = $request->fungsi;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();

        return back()->with('success', 'Data user berhasil diubah.');
    }

    public function destroy(User $user)
    {
        // For non-active toggle
        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun berhasil $status.");
    }
}
