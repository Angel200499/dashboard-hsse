@extends('layouts.app')

@section('title', 'Master Data Manpower')

@section('content')
<div class="space-y-6">

    {{-- ================================================================
         HEADER
    ================================================================ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Master Data Manpower</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola jumlah manpower per fungsi, tahun, dan bulan sebagai dasar perhitungan Reporting Rate bulanan.
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
            Tambah Manpower
        </button>
    </div>

    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
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
         SUMMARY CARDS — tampilkan data per fungsi untuk filter aktif
    ================================================================ --}}
    @php
        $fungsiColors = [
            'Operation'       => 'bg-blue-50 border-blue-200 text-blue-700',
            'Maintenance'     => 'bg-amber-50 border-amber-200 text-amber-700',
            'HSSE'            => 'bg-green-50 border-green-200 text-green-700',
            'Business Support'=> 'bg-purple-50 border-purple-200 text-purple-700',
        ];
        $fungsiIcons = [
            'Operation'       => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
            'Maintenance'     => 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z',
            'HSSE'            => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
            'Business Support'=> 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        ];
        // Label periode untuk summary cards
        $periodeLabel = $selectedYear
            ? ($selectedMonth
                ? ($bulanLabels[$selectedMonth] . ' ' . $selectedYear)
                : 'Tahun ' . $selectedYear)
            : 'Semua Periode';
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($fungsiList as $fn)
        @php
            $fnData = $manpowers->where('fungsi', $fn)->first();
            $colorClass = $fungsiColors[$fn] ?? 'bg-slate-50 border-slate-200 text-slate-700';
            $iconPath   = $fungsiIcons[$fn] ?? '';
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl {{ $colorClass }} border flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $iconPath }}"/>
                    </svg>
                </div>
                @if($fnData)
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">Tersedia</span>
                @else
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Belum ada</span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mb-0.5">{{ $fn }}</p>
            <p class="text-xl font-bold text-slate-800">
                {{ $fnData ? number_format($fnData->jumlah_manpower) : '—' }}
            </p>
            @if($fnData)
                <p class="text-[10px] text-slate-400 mt-0.5">
                    {{ $bulanLabels[$fnData->bulan] ?? '—' }} {{ $fnData->tahun }}
                </p>
            @else
                <p class="text-[10px] text-slate-400 mt-0.5">{{ $periodeLabel }}</p>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ================================================================
         FILTER & TABLE SECTION
    ================================================================ --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Filter Bar --}}
        <div class="px-5 py-4 border-b border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-800">Daftar Data Manpower</h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Total {{ $manpowers->count() }} record
                        @if($selectedYear && $selectedMonth)
                            untuk {{ $bulanLabels[$selectedMonth] }} {{ $selectedYear }}
                        @elseif($selectedYear)
                            untuk tahun {{ $selectedYear }}
                        @endif
                    </p>
                </div>

                {{-- Filter Tahun + Bulan --}}
                <form method="GET" action="{{ route('master.manpower.index') }}" class="flex flex-wrap items-center gap-2" id="filter-form">
                    <label class="text-sm text-slate-600 font-medium whitespace-nowrap">Filter:</label>

                    {{-- Filter Tahun --}}
                    <select
                        id="filter-tahun"
                        name="tahun"
                        onchange="document.getElementById('filter-bulan').value=''; this.form.submit()"
                        class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                    >
                        <option value="">Semua Tahun</option>
                        @foreach($availYears as $yr)
                            <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>

                    {{-- Filter Bulan (hanya aktif jika tahun dipilih) --}}
                    <select
                        id="filter-bulan"
                        name="bulan"
                        onchange="this.form.submit()"
                        class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition {{ !$selectedYear ? 'opacity-50' : '' }}"
                        {{ !$selectedYear ? 'disabled' : '' }}
                    >
                        <option value="">Semua Bulan</option>
                        @foreach($bulanLabels as $num => $nama)
                            <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>

                    @if($selectedYear || $selectedMonth)
                        <a href="{{ route('master.manpower.index') }}" class="text-xs text-slate-500 hover:text-slate-700 underline whitespace-nowrap">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- TABLE --}}
        @if($manpowers->isEmpty())
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-20 px-6 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-700 mb-1">Belum ada data manpower</h3>
                <p class="text-sm text-slate-400 mb-6 max-w-sm">
                    Tambahkan data manpower per fungsi, tahun, dan bulan untuk menyediakan denominator perhitungan Reporting Rate.
                </p>
                <button
                    id="btn-open-create-empty"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#9DBF2A] hover:bg-[#8aaa22] text-white text-sm font-semibold rounded-lg shadow-md shadow-[#9DBF2A]/30 transition-all duration-200 active:scale-95"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Manpower
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fungsi</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Tahun</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Bulan</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Jumlah Manpower</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Terakhir Diperbarui</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($manpowers as $mp)
                        @php
                            $badge = $fungsiColors[$mp->fungsi] ?? 'bg-slate-100 border-slate-200 text-slate-700';
                            $namaBulan = $mp->bulan ? ($bulanLabels[$mp->bulan] ?? "Bulan {$mp->bulan}") : '—';
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badge }}">
                                    {{ $mp->fungsi }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-sm font-semibold text-slate-700">{{ $mp->tahun }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($mp->bulan)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        {{ $namaBulan }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 italic">Data lama</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-bold text-slate-800">{{ number_format($mp->jumlah_manpower) }}</span>
                                <span class="text-xs text-slate-400 ml-1">orang</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm text-slate-500">{{ $mp->updated_at->translatedFormat('d M Y') }}</span>
                                <span class="text-xs text-slate-400 block">{{ $mp->updated_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    {{-- Edit Button --}}
                                    <button
                                        type="button"
                                        class="btn-edit inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                                        data-id="{{ $mp->id }}"
                                        data-fungsi="{{ $mp->fungsi }}"
                                        data-tahun="{{ $mp->tahun }}"
                                        data-bulan="{{ $mp->bulan }}"
                                        data-jumlah="{{ $mp->jumlah_manpower }}"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>

                                    {{-- Delete Form --}}
                                    <form
                                        method="POST"
                                        action="{{ route('master.manpower.destroy', $mp->id) }}"
                                        class="form-delete"
                                        data-label="{{ $mp->fungsi }} {{ $namaBulan }} {{ $mp->tahun }}"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

{{-- ================================================================
     MODAL TAMBAH MANPOWER
================================================================ --}}
<div id="modal-create" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="modal-create-backdrop"></div>

    {{-- Modal Panel --}}
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Tambah Data Manpower</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Masukkan data manpower per fungsi, tahun, dan bulan</p>
                </div>
                <button type="button" id="btn-close-create" class="p-2 hover:bg-slate-100 rounded-lg text-slate-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form method="POST" action="{{ route('master.manpower.store') }}" id="form-create">
                @csrf
                <div class="px-6 py-5 space-y-5">

                    {{-- Fungsi --}}
                    <div>
                        <label for="create-fungsi" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Fungsi <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="create-fungsi"
                            name="fungsi"
                            required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                            <option value="" disabled selected>— Pilih Fungsi —</option>
                            @foreach($fungsiList as $fn)
                                <option value="{{ $fn }}" {{ old('fungsi') == $fn ? 'selected' : '' }}>{{ $fn }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tahun --}}
                    <div>
                        <label for="create-tahun" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Tahun <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="create-tahun"
                            name="tahun"
                            min="2020"
                            max="2050"
                            placeholder="Contoh: {{ now()->year }}"
                            value="{{ old('tahun') }}"
                            required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                        <p class="text-xs text-slate-400 mt-1">Rentang tahun: 2020 – 2050</p>
                    </div>

                    {{-- Bulan --}}
                    <div>
                        <label for="create-bulan" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Bulan <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="create-bulan"
                            name="bulan"
                            required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                            <option value="" disabled selected>— Pilih Bulan —</option>
                            @foreach($bulanLabels as $num => $nama)
                                <option value="{{ $num }}" {{ old('bulan') == $num ? 'selected' : '' }}>{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jumlah Manpower --}}
                    <div>
                        <label for="create-jumlah" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Jumlah Manpower <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="create-jumlah"
                            name="jumlah_manpower"
                            min="1"
                            step="1"
                            placeholder="Contoh: 100"
                            value="{{ old('jumlah_manpower') }}"
                            required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                        <p class="text-xs text-slate-400 mt-1">Masukkan bilangan bulat positif (minimal 1)</p>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" id="btn-cancel-create" class="px-4 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-white bg-[#9DBF2A] hover:bg-[#8aaa22] rounded-lg shadow-md shadow-[#9DBF2A]/30 transition-all active:scale-95">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL EDIT MANPOWER
================================================================ --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="modal-edit-backdrop"></div>

    {{-- Modal Panel --}}
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Edit Data Manpower</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Perbarui data manpower yang sudah tersedia</p>
                </div>
                <button type="button" id="btn-close-edit" class="p-2 hover:bg-slate-100 rounded-lg text-slate-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form method="POST" id="form-edit" action="">
                @csrf
                @method('PUT')
                <div class="px-6 py-5 space-y-5">

                    {{-- Fungsi --}}
                    <div>
                        <label for="edit-fungsi" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Fungsi <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="edit-fungsi"
                            name="fungsi"
                            required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                            <option value="" disabled>— Pilih Fungsi —</option>
                            @foreach($fungsiList as $fn)
                                <option value="{{ $fn }}">{{ $fn }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tahun --}}
                    <div>
                        <label for="edit-tahun" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Tahun <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="edit-tahun"
                            name="tahun"
                            min="2020"
                            max="2050"
                            required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                        <p class="text-xs text-slate-400 mt-1">Rentang tahun: 2020 – 2050</p>
                    </div>

                    {{-- Bulan --}}
                    <div>
                        <label for="edit-bulan" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Bulan <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="edit-bulan"
                            name="bulan"
                            required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                            <option value="" disabled>— Pilih Bulan —</option>
                            @foreach($bulanLabels as $num => $nama)
                                <option value="{{ $num }}">{{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jumlah Manpower --}}
                    <div>
                        <label for="edit-jumlah" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Jumlah Manpower <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="edit-jumlah"
                            name="jumlah_manpower"
                            min="1"
                            step="1"
                            required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                        <p class="text-xs text-slate-400 mt-1">Masukkan bilangan bulat positif (minimal 1)</p>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" id="btn-cancel-edit" class="px-4 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-white bg-[#9DBF2A] hover:bg-[#8aaa22] rounded-lg shadow-md shadow-[#9DBF2A]/30 transition-all active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL KONFIRMASI DELETE
================================================================ --}}
<div id="modal-delete" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
            <div class="px-6 py-5 text-center">
                <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800 mb-1">Hapus Data Manpower</h3>
                <p class="text-sm text-slate-500 mb-6" id="delete-confirm-text">
                    Apakah Anda yakin ingin menghapus data ini?
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" id="btn-cancel-delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="button" id="btn-confirm-delete" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // UTILS
    // =====================================================
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.style.overflow = '';
    }

    // =====================================================
    // MODAL TAMBAH (Create)
    // =====================================================
    const btnOpenCreate      = document.getElementById('btn-open-create');
    const btnOpenCreateEmpty = document.getElementById('btn-open-create-empty');
    const btnCloseCreate     = document.getElementById('btn-close-create');
    const btnCancelCreate    = document.getElementById('btn-cancel-create');
    const backdropCreate     = document.getElementById('modal-create-backdrop');

    function openCreateModal() { openModal('modal-create'); }
    function closeCreateModal() { closeModal('modal-create'); }

    if (btnOpenCreate)      btnOpenCreate.addEventListener('click', openCreateModal);
    if (btnOpenCreateEmpty) btnOpenCreateEmpty.addEventListener('click', openCreateModal);
    if (btnCloseCreate)     btnCloseCreate.addEventListener('click', closeCreateModal);
    if (btnCancelCreate)    btnCancelCreate.addEventListener('click', closeCreateModal);
    if (backdropCreate)     backdropCreate.addEventListener('click', closeCreateModal);

    // =====================================================
    // MODAL EDIT
    // =====================================================
    const btnCloseEdit  = document.getElementById('btn-close-edit');
    const btnCancelEdit = document.getElementById('btn-cancel-edit');
    const backdropEdit  = document.getElementById('modal-edit-backdrop');
    const formEdit      = document.getElementById('form-edit');
    const editFungsi    = document.getElementById('edit-fungsi');
    const editTahun     = document.getElementById('edit-tahun');
    const editBulan     = document.getElementById('edit-bulan');
    const editJumlah    = document.getElementById('edit-jumlah');

    function closeEditModal() { closeModal('modal-edit'); }

    if (btnCloseEdit)  btnCloseEdit.addEventListener('click', closeEditModal);
    if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEditModal);
    if (backdropEdit)  backdropEdit.addEventListener('click', closeEditModal);

    // Isi data ke form edit saat tombol Edit diklik
    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;
            const fungsi = this.dataset.fungsi;
            const tahun  = this.dataset.tahun;
            const bulan  = this.dataset.bulan;
            const jumlah = this.dataset.jumlah;

            // Set action URL dengan ID record yang benar
            const baseUrl = '{{ url("/master/manpower") }}';
            formEdit.action = baseUrl + '/' + id;

            // Isi field
            editFungsi.value = fungsi;
            editTahun.value  = tahun;
            editBulan.value  = bulan || '';
            editJumlah.value = jumlah;

            openModal('modal-edit');
        });
    });

    // =====================================================
    // MODAL DELETE CONFIRMATION
    // =====================================================
    const btnCancelDelete  = document.getElementById('btn-cancel-delete');
    const btnConfirmDelete = document.getElementById('btn-confirm-delete');
    const deleteText       = document.getElementById('delete-confirm-text');
    let   pendingDeleteForm = null;

    function closeDeleteModal() {
        closeModal('modal-delete');
        pendingDeleteForm = null;
    }

    if (btnCancelDelete) btnCancelDelete.addEventListener('click', closeDeleteModal);

    document.querySelectorAll('.form-delete').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const label = this.dataset.label;
            deleteText.textContent = 'Apakah Anda yakin ingin menghapus data manpower ' + label + '?';
            pendingDeleteForm = this;
            openModal('modal-delete');
        });
    });

    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', function () {
            if (pendingDeleteForm) {
                pendingDeleteForm.submit();
            }
        });
    }

    // =====================================================
    // BUKA MODAL CREATE OTOMATIS JIKA ADA VALIDATION ERROR
    // DAN BUKAN DARI FORM EDIT
    // =====================================================
    @if($errors->any() && old('_method') !== 'PUT')
        openCreateModal();
    @endif

    @if($errors->any() && old('_method') === 'PUT')
        // Re-open edit modal jika ada error dari update
        const oldFungsi = "{{ old('fungsi') }}";
        const oldTahun  = "{{ old('tahun') }}";
        const oldBulan  = "{{ old('bulan') }}";
        const oldJumlah = "{{ old('jumlah_manpower') }}";

        // Cari tombol edit yang cocok berdasarkan fungsi + tahun + bulan
        document.querySelectorAll('.btn-edit').forEach(function (btn) {
            if (btn.dataset.fungsi === oldFungsi
                && btn.dataset.tahun === oldTahun
                && btn.dataset.bulan === oldBulan) {
                btn.click();
                editJumlah.value = oldJumlah;
            }
        });
    @endif

    // =====================================================
    // KEYBOARD: ESC untuk tutup modal
    // =====================================================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });

});
</script>
@endpush
