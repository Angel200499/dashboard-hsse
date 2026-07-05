@extends('layouts.app')

@section('title', 'Manajemen Akun')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Roles List</h1>
        <p class="text-sm text-slate-500 mt-1">Sistem RBAC mendefinisikan menu dan fitur apa saja yang dapat diakses oleh sebuah role.</p>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-200 mb-6">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 mb-6">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        // Filter users that are "Admin Function"
        $adminFunctions = $users->where('role', 'Admin Function');
        
        // Group by 'fungsi'
        $fungsiCount = $adminFunctions->groupBy('fungsi')->map->count();
        $fungsiUsers = $adminFunctions->groupBy('fungsi')->map->take(4);
        
        $rolesData = [
            'Admin HSSE' => ['icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>', 'color' => 'text-red-500'],
            'Manager HSSE' => ['icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>', 'color' => 'text-purple-500'],
            'Admin Function' => ['icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>', 'color' => 'text-amber-500'],
            'Manager Function' => ['icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>', 'color' => 'text-green-500'],
        ];
        
        $avatarColors = ['bg-blue-100 text-blue-600', 'bg-green-100 text-green-600', 'bg-red-100 text-red-600', 'bg-amber-100 text-amber-600', 'bg-purple-100 text-purple-600'];
    @endphp

    <!-- Roles Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @foreach(['Operation', 'Maintenance', 'HSSE', 'Business Support'] as $fName)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-center mb-4">
                <span class="text-sm text-slate-500">Total {{ $fungsiCount[$fName] ?? 0 }} admin</span>
                <div class="flex -space-x-2 overflow-hidden">
                    @if(isset($fungsiUsers[$fName]))
                        @foreach($fungsiUsers[$fName] as $u)
                            @php 
                                $ini = strtoupper(substr($u->name, 0, 1));
                                $clr = $avatarColors[crc32($u->username) % count($avatarColors)];
                            @endphp
                            <div class="inline-block h-8 w-8 rounded-full ring-2 ring-white {{ $clr }} flex items-center justify-center text-xs font-bold">{{ $ini }}</div>
                        @endforeach
                    @endif
                </div>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-1">Admin {{ $fName }}</h3>
            <a href="#" class="text-blue-600 text-sm hover:underline">Lihat Admin</a>
        </div>
        @endforeach
    </div>

    <div class="mb-4 mt-8">
        <h2 class="text-xl font-bold text-slate-800 tracking-tight">Total users with their roles</h2>
        <p class="text-sm text-slate-500 mt-1">Find all of your company's administrator accounts and their associate roles.</p>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white rounded-t-2xl border border-slate-200 border-b-0 p-5 flex flex-col sm:flex-row justify-between items-center gap-4">
        <button class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-200 transition-all shadow-sm">
            <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            EXPORT
        </button>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" class="block w-full p-2.5 pl-10 text-sm text-slate-900 border border-slate-300 rounded-lg bg-slate-50 focus:ring-blue-500 focus:border-blue-500" placeholder="Search User">
            </div>
            <button onclick="document.getElementById('modal-create').classList.remove('hidden')" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-md shadow-blue-500/20 whitespace-nowrap">
                ADD USER
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-b-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-600">
                <thead class="text-xs text-slate-400 uppercase bg-white border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold w-10">
                            <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-4 font-semibold tracking-wider">User</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Username</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Role</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Fungsi</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Status</th>
                        <th class="px-6 py-4 font-semibold tracking-wider text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-50/70 transition-colors group">
                        <td class="px-6 py-4">
                            <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 flex items-center gap-3">
                            @php 
                                $initial = strtoupper(substr($user->name, 0, 1));
                                $color = $avatarColors[crc32($user->username) % count($avatarColors)];
                            @endphp
                            <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold shadow-sm {{ $color }}">
                                {{ $initial }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                                <p class="text-xs text-slate-400">@{{ $user->username }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $user->username }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                @if(isset($rolesData[$user->role]))
                                    <span class="{{ $rolesData[$user->role]['color'] }}">{!! $rolesData[$user->role]['icon'] !!}</span>
                                @endif
                                <span class="font-medium text-slate-700">{{ $user->role }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-600">{{ $user->fungsi }}</td>
                        <td class="px-6 py-4">
                            @if($user->is_active)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-green-100 text-green-700 border border-green-200">Active</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
                                </button>
                                <!-- Dropdown -->
                                <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 z-10 py-1" onmouseleave="this.classList.add('hidden')">
                                    <button onclick="openEditModal({{ $user->toJson() }})" class="w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        Edit User
                                    </button>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Yakin ingin merubah status user ini?')" class="w-full text-left px-4 py-2 text-sm {{ $user->is_active ? 'text-red-600 hover:bg-red-50' : 'text-green-600 hover:bg-green-50' }} flex items-center gap-2">
                                            @if($user->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                Suspend
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Activate
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 bg-white flex justify-end">
            <!-- Simulated Pagination -->
            <div class="flex items-center gap-4 text-sm text-slate-500">
                <span>Rows per page: <select class="border-none bg-transparent font-medium focus:ring-0 cursor-pointer"><option>10</option></select></span>
                <span>1-{{ count($users) }} of {{ count($users) }}</span>
                <div class="flex gap-1">
                    <button class="p-1 text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg></button>
                    <button class="p-1 text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div id="modal-create" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-slate-800">Tambah User Baru</h3>
                <button onclick="document.getElementById('modal-create').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" name="username" required class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                    <select name="role" required class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="Admin HSSE">Admin HSSE</option>
                        <option value="Admin Function">Admin Function</option>
                        <option value="Manager HSSE">Manager HSSE</option>
                        <option value="Manager Function">Manager Function</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Fungsi</label>
                    <select name="fungsi" required class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="Operation">Operation</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="HSSE">HSSE</option>
                        <option value="Business Support">Business Support</option>
                    </select>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-slate-500 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-700 rounded-lg transition-colors">CANCEL</button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-[#9DBF2A] hover:bg-[#8ca825] rounded-lg transition-colors shadow-sm uppercase tracking-wide">SAVE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-slate-800">Edit User</h3>
                <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="form-edit" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" id="edit-name" required class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" name="username" id="edit-username" required class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Reset Password <span class="text-xs text-slate-400 font-normal">(Kosongkan jika tidak ingin mengubah)</span></label>
                    <input type="password" name="password" class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                    <select name="role" id="edit-role" required class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="Admin HSSE">Admin HSSE</option>
                        <option value="Admin Function">Admin Function</option>
                        <option value="Manager HSSE">Manager HSSE</option>
                        <option value="Manager Function">Manager Function</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Fungsi</label>
                    <select name="fungsi" id="edit-fungsi" required class="w-full border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="Operation">Operation</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="HSSE">HSSE</option>
                        <option value="Business Support">Business Support</option>
                    </select>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')" class="px-4 py-2.5 text-sm font-medium text-slate-500 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-700 rounded-lg transition-colors">CANCEL</button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-[#9DBF2A] hover:bg-[#8ca825] rounded-lg transition-colors shadow-sm uppercase tracking-wide">UPDATE</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(user) {
        document.getElementById('edit-name').value = user.name;
        document.getElementById('edit-username').value = user.username;
        document.getElementById('edit-role').value = user.role;
        document.getElementById('edit-fungsi').value = user.fungsi;
        
        let form = document.getElementById('form-edit');
        form.action = `/users/${user.id}`;
        
        document.getElementById('modal-edit').classList.remove('hidden');
    }
</script>
@endsection
