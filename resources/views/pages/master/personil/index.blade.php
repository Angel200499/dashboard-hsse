@extends('layouts.app')

@section('title', 'Master Data Personil')

@section('content')
<div class="space-y-6">

    {{-- ================================================================
         HEADER
    ================================================================ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Master Data Personil</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola data individu pekerja organik dan Tenaga Alih Daya (TAD) per fungsi sebagai fondasi monitoring pelaporan HSSE.
            </p>
        </div>
        <button
            id="btn-open-create"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#9DBF2A] hover:bg-[#8aaa22] text-white text-sm font-semibold rounded-lg shadow-md shadow-[#9DBF2A]/30 transition-all duration-200 active:scale-95"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Personil
        </button>
    </div>

    {{-- ================================================================
         ALERTS
    ================================================================ --}}
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-emerald-800 text-sm">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-amber-50 border border-amber-300 rounded-xl text-amber-900 text-sm flex items-start justify-between">
            <div class="flex items-start gap-2.5">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-amber-500 hover:text-amber-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-xl">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- ================================================================
         SUMMARY CARDS
    ================================================================ --}}
    @php
        $fungsiColors = [
            'Operation'        => 'bg-blue-50 border-blue-200 text-blue-700',
            'Maintenance'      => 'bg-amber-50 border-amber-200 text-amber-700',
            'HSSE'             => 'bg-emerald-50 border-emerald-200 text-emerald-700',
            'Business Support' => 'bg-purple-50 border-purple-200 text-purple-700',
        ];
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Personil --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-slate-500">Total Personil</span>
                <span class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($totalPersonil) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">{{ number_format($totalAktif) }} personil berstatus aktif</p>
        </div>

        {{-- Pekerja Organik --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-slate-500">Pekerja Organik</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-blue-700">{{ number_format($totalPekerja) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Personil dengan NOPEK</p>
        </div>

        {{-- Tenaga Alih Daya (TAD) --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-slate-500">Tenaga Alih Daya (TAD)</span>
                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-indigo-700">{{ number_format($totalTad) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">NOPEK bersifat opsional</p>
        </div>

        {{-- Status Aktif Rate --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-slate-500">Status Keaktifan</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-emerald-700">
                {{ $totalPersonil > 0 ? round(($totalAktif / $totalPersonil) * 100, 1) : 0 }}%
            </p>
            <p class="text-[11px] text-slate-400 mt-1">{{ number_format($totalPersonil - $totalAktif) }} non-aktif</p>
        </div>
    </div>

    {{-- Breakdown per Fungsi --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <div class="text-xs font-semibold text-slate-600 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Personil Aktif per Fungsi:
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($fungsiList as $fn)
                @php
                    $badgeStyle = $fungsiColors[$fn] ?? 'bg-slate-50 border-slate-200 text-slate-700';
                @endphp
                <div class="p-2.5 rounded-xl border {{ $badgeStyle }} flex items-center justify-between">
                    <span class="text-xs font-semibold">{{ $fn }}</span>
                    <span class="text-sm font-bold">{{ number_format($fungsiCounts[$fn] ?? 0) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ================================================================
         FILTER & TABLE SECTION
    ================================================================ --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Filter Bar --}}
        <div class="px-5 py-4 border-b border-slate-100">
            <form method="GET" action="{{ route('master.personil.index') }}" class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2.5 flex-1 min-w-[280px]">
                    {{-- Search Input --}}
                    <div class="relative flex-1 min-w-[180px] max-w-sm">
                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari nama, NOPEK, jabatan..."
                            class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-lg text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    {{-- Filter Fungsi --}}
                    <select
                        name="fungsi"
                        onchange="this.form.submit()"
                        class="text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                    >
                        <option value="">Semua Fungsi</option>
                        @foreach($fungsiList as $fn)
                            <option value="{{ $fn }}" {{ request('fungsi') === $fn ? 'selected' : '' }}>{{ $fn }}</option>
                        @endforeach
                    </select>

                    {{-- Filter Tipe --}}
                    <select
                        name="tipe"
                        onchange="this.form.submit()"
                        class="text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                    >
                        <option value="">Semua Tipe</option>
                        @foreach($tipeList as $tp)
                            <option value="{{ $tp }}" {{ request('tipe') === $tp ? 'selected' : '' }}>{{ $tp }}</option>
                        @endforeach
                    </select>

                    {{-- Filter Status --}}
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                    >
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition">
                        Terapkan
                    </button>
                    @if(request()->hasAny(['q', 'fungsi', 'tipe', 'status']))
                        <a href="{{ route('master.personil.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700 underline whitespace-nowrap">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table Content --}}
        @if($personils->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800">Tidak ada data personil ditemukan</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm">
                    @if(request()->hasAny(['q', 'fungsi', 'tipe', 'status']))
                        Coba sesuaikan filter pencarian Anda atau klik reset.
                    @else
                        Belum ada data master personil. Klik tombol "Tambah Personil" di atas untuk menambahkan data.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="px-5 py-3 w-12 text-center">No</th>
                            <th class="px-5 py-3">Nama Personil</th>
                            <th class="px-5 py-3">NOPEK</th>
                            <th class="px-5 py-3">Tipe</th>
                            <th class="px-5 py-3">Fungsi</th>
                            <th class="px-5 py-3">Jabatan</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-center w-36">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach($personils as $index => $p)
                        @php
                            $badgeFungsi = $fungsiColors[$p->fungsi] ?? 'bg-slate-100 border-slate-200 text-slate-700';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors group">
                            <td class="px-5 py-3.5 text-center text-slate-400">
                                {{ $personils->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-3.5 font-semibold text-slate-800">
                                {{ $p->nama }}
                            </td>
                            <td class="px-5 py-3.5 font-mono">
                                @if($p->nopek)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $p->nopek }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">— (TAD)</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($p->tipe_manpower === 'Pekerja')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700">
                                        Pekerja
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-700">
                                        TAD
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $badgeFungsi }}">
                                    {{ $p->fungsi }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $p->jabatan ?: '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($p->status_aktif)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Non-Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Edit Button --}}
                                    <button
                                        type="button"
                                        class="btn-edit p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                        title="Edit Personil"
                                        data-id="{{ $p->id }}"
                                        data-nama="{{ $p->nama }}"
                                        data-nopek="{{ $p->nopek }}"
                                        data-fungsi="{{ $p->fungsi }}"
                                        data-jabatan="{{ $p->jabatan }}"
                                        data-tipe="{{ $p->tipe_manpower }}"
                                        data-status="{{ $p->status_aktif ? '1' : '0' }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    {{-- Toggle Status Button --}}
                                    <form method="POST" action="{{ route('master.personil.toggle-status', $p->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="p-1.5 {{ $p->status_aktif ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }} rounded-lg transition"
                                            title="{{ $p->status_aktif ? 'Non-aktifkan Personil' : 'Aktifkan Personil' }}"
                                        >
                                            @if($p->status_aktif)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Delete Button --}}
                                    <form
                                        method="POST"
                                        action="{{ route('master.personil.destroy', $p->id) }}"
                                        class="form-delete inline"
                                        data-nama="{{ $p->nama }}"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition"
                                            title="Hapus Personil"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Footer --}}
            <div class="px-5 py-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <div>
                    Menampilkan <span class="font-semibold text-slate-700">{{ $personils->firstItem() }}</span> s/d <span class="font-semibold text-slate-700">{{ $personils->lastItem() }}</span> dari <span class="font-semibold text-slate-700">{{ $personils->total() }}</span> personil
                </div>
                <div>
                    {{ $personils->links() }}
                </div>
            </div>
        @endif

    </div>

</div>

{{-- ================================================================
     MODAL TAMBAH PERSONIL
================================================================ --}}
<div id="modal-create" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="modal-create-backdrop"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all overflow-hidden">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Tambah Personil Baru</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Daftarkan data pekerja organik atau Tenaga Alih Daya (TAD)</p>
                </div>
                <button type="button" id="btn-close-create" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Form Create --}}
            <form method="POST" action="{{ route('master.personil.store') }}" id="form-create">
                @csrf
                <div class="px-6 py-5 space-y-4 text-xs">
                    {{-- Nama --}}
                    <div>
                        <label for="create-nama" class="block font-medium text-slate-700 mb-1">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="create-nama"
                            name="nama"
                            value="{{ old('nama') }}"
                            placeholder="Contoh: Barry Mangundap"
                            required
                            class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Tipe Manpower --}}
                        <div>
                            <label for="create-tipe" class="block font-medium text-slate-700 mb-1">
                                Tipe Manpower <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="create-tipe"
                                name="tipe_manpower"
                                required
                                class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                            >
                                <option value="Pekerja" {{ old('tipe_manpower') === 'Pekerja' ? 'selected' : '' }}>Pekerja (Organik)</option>
                                <option value="TAD" {{ old('tipe_manpower') === 'TAD' ? 'selected' : '' }}>Tenaga Alih Daya (TAD)</option>
                            </select>
                        </div>

                        {{-- NOPEK --}}
                        <div>
                            <label for="create-nopek" class="block font-medium text-slate-700 mb-1">
                                NOPEK <span id="label-nopek-optional" class="text-slate-400 font-normal">(Opsional untuk TAD)</span>
                            </label>
                            <input
                                type="text"
                                id="create-nopek"
                                name="nopek"
                                value="{{ old('nopek') }}"
                                placeholder="Contoh: 765432"
                                class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Fungsi --}}
                        <div>
                            <label for="create-fungsi" class="block font-medium text-slate-700 mb-1">
                                Fungsi <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="create-fungsi"
                                name="fungsi"
                                required
                                class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                            >
                                <option value="" disabled selected>— Pilih Fungsi —</option>
                                @foreach($fungsiList as $fn)
                                    <option value="{{ $fn }}" {{ old('fungsi') === $fn ? 'selected' : '' }}>{{ $fn }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jabatan --}}
                        <div>
                            <label for="create-jabatan" class="block font-medium text-slate-700 mb-1">
                                Jabatan / Posisi
                            </label>
                            <input
                                type="text"
                                id="create-jabatan"
                                name="jabatan"
                                value="{{ old('jabatan') }}"
                                placeholder="Contoh: Senior Operator"
                                class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                            >
                        </div>
                    </div>

                    {{-- Status Aktif Checkbox --}}
                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="status_aktif"
                                value="1"
                                checked
                                class="rounded border-slate-300 text-[#9DBF2A] focus:ring-[#9DBF2A]"
                            >
                            <span class="text-xs text-slate-700 font-medium">Status Aktif (dihitung dalam populasi pelaporan)</span>
                        </label>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
                    <button type="button" id="btn-cancel-create" class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#9DBF2A] hover:bg-[#8aaa22] rounded-lg shadow-sm transition active:scale-95">
                        Simpan Personil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL EDIT PERSONIL
================================================================ --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="modal-edit-backdrop"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all overflow-hidden">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Edit Data Personil</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Perbarui profil dan data penempatan personil</p>
                </div>
                <button type="button" id="btn-close-edit" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Form Edit --}}
            <form method="POST" action="" id="form-edit">
                @csrf
                @method('PUT')
                <div class="px-6 py-5 space-y-4 text-xs">
                    {{-- Nama --}}
                    <div>
                        <label for="edit-nama" class="block font-medium text-slate-700 mb-1">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="edit-nama"
                            name="nama"
                            required
                            class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Tipe Manpower --}}
                        <div>
                            <label for="edit-tipe" class="block font-medium text-slate-700 mb-1">
                                Tipe Manpower <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="edit-tipe"
                                name="tipe_manpower"
                                required
                                class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                            >
                                <option value="Pekerja">Pekerja (Organik)</option>
                                <option value="TAD">Tenaga Alih Daya (TAD)</option>
                            </select>
                        </div>

                        {{-- NOPEK --}}
                        <div>
                            <label for="edit-nopek" class="block font-medium text-slate-700 mb-1">
                                NOPEK <span class="text-slate-400 font-normal">(Opsional untuk TAD)</span>
                            </label>
                            <input
                                type="text"
                                id="edit-nopek"
                                name="nopek"
                                class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Fungsi --}}
                        <div>
                            <label for="edit-fungsi" class="block font-medium text-slate-700 mb-1">
                                Fungsi <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="edit-fungsi"
                                name="fungsi"
                                required
                                class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                            >
                                @foreach($fungsiList as $fn)
                                    <option value="{{ $fn }}">{{ $fn }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jabatan --}}
                        <div>
                            <label for="edit-jabatan" class="block font-medium text-slate-700 mb-1">
                                Jabatan / Posisi
                            </label>
                            <input
                                type="text"
                                id="edit-jabatan"
                                name="jabatan"
                                class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                            >
                        </div>
                    </div>

                    {{-- Status Aktif Checkbox --}}
                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                id="edit-status"
                                name="status_aktif"
                                value="1"
                                class="rounded border-slate-300 text-[#9DBF2A] focus:ring-[#9DBF2A]"
                            >
                            <span class="text-xs text-slate-700 font-medium">Status Aktif (dihitung dalam populasi pelaporan)</span>
                        </label>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
                    <button type="button" id="btn-cancel-edit" class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-[#9DBF2A] hover:bg-[#8aaa22] rounded-lg shadow-sm transition active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modal Create Elements
        const modalCreate = document.getElementById('modal-create');
        const btnOpenCreate = document.getElementById('btn-open-create');
        const btnCloseCreate = document.getElementById('btn-close-create');
        const btnCancelCreate = document.getElementById('btn-cancel-create');
        const backdropCreate = document.getElementById('modal-create-backdrop');

        function openCreate() {
            modalCreate.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            document.getElementById('create-nama').focus();
        }

        function closeCreate() {
            modalCreate.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        if (btnOpenCreate) btnOpenCreate.addEventListener('click', openCreate);
        if (btnCloseCreate) btnCloseCreate.addEventListener('click', closeCreate);
        if (btnCancelCreate) btnCancelCreate.addEventListener('click', closeCreate);
        if (backdropCreate) backdropCreate.addEventListener('click', closeCreate);

        // Modal Edit Elements
        const modalEdit = document.getElementById('modal-edit');
        const formEdit = document.getElementById('form-edit');
        const btnCloseEdit = document.getElementById('btn-close-edit');
        const btnCancelEdit = document.getElementById('btn-cancel-edit');
        const backdropEdit = document.getElementById('modal-edit-backdrop');

        const editNama = document.getElementById('edit-nama');
        const editNopek = document.getElementById('edit-nopek');
        const editTipe = document.getElementById('edit-tipe');
        const editFungsi = document.getElementById('edit-fungsi');
        const editJabatan = document.getElementById('edit-jabatan');
        const editStatus = document.getElementById('edit-status');

        function openEdit(data) {
            formEdit.action = `/master/personil/${data.id}`;
            editNama.value = data.nama || '';
            editNopek.value = data.nopek || '';
            editTipe.value = data.tipe || 'Pekerja';
            editFungsi.value = data.fungsi || 'Operation';
            editJabatan.value = data.jabatan || '';
            editStatus.checked = data.status === '1';

            modalEdit.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            editNama.focus();
        }

        function closeEdit() {
            modalEdit.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', function () {
                openEdit({
                    id: this.dataset.id,
                    nama: this.dataset.nama,
                    nopek: this.dataset.nopek,
                    tipe: this.dataset.tipe,
                    fungsi: this.dataset.fungsi,
                    jabatan: this.dataset.jabatan,
                    status: this.dataset.status,
                });
            });
        });

        if (btnCloseEdit) btnCloseEdit.addEventListener('click', closeEdit);
        if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEdit);
        if (backdropEdit) backdropEdit.addEventListener('click', closeEdit);

        // Escape Key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeCreate();
                closeEdit();
            }
        });

        // Delete Confirm
        document.querySelectorAll('.form-delete').forEach(form => {
            form.addEventListener('submit', function (e) {
                const nama = this.dataset.nama || 'personil ini';
                if (!confirm(`Apakah Anda yakin ingin menghapus data personil "${nama}"?\n\nCatatan: Jika personil ini memiliki riwayat laporan di SIPEKA, penghapusan permanen akan ditolak demi integritas data.`)) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush
@endsection
