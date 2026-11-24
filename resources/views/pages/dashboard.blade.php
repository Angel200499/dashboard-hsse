@extends('layouts.app')

@section('title', 'Overview')

@section('content')
@php
    $bulanNamaList = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $tahunList = $tahunList ?? \App\Models\SipekaFinding::getTahunList();

    // Dropdown bulan khusus Reporting Rate — hanya tampilkan bulan yang
    // benar-benar memiliki data SIPEKA pada tahun yang dipilih.
    // $rrMaxBulan dikirim dari controller (null jika tahun belum dipilih atau data tidak ada).
    $rrBulanList = ($rrMaxBulan ?? null)
        ? array_filter($bulanNamaList, fn($num) => $num <= $rrMaxBulan, ARRAY_FILTER_USE_KEY)
        : $bulanNamaList; // fallback: tampilkan semua jika tahun belum dipilih atau tidak ada batas max
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Dashboard Global HSSE</h1>
            <p class="text-sm text-slate-500 mt-1">Monitoring dan analitik data temuan PEKA secara real-time.</p>
        </div>
        
        <!-- Filter Utama & Action Buttons -->
        <div class="flex flex-wrap items-center gap-3">
            {{-- Filter Utama Tahun Global --}}
            <form action="" method="GET" onsubmit="return false;" class="flex items-center gap-2" id="dashboard-global-filter-form">
                @if(request()->has('rr_year'))
                    <input type="hidden" name="rr_year" value="{{ request('rr_year') }}">
                @endif
                @if(request()->has('rr_month'))
                    <input type="hidden" name="rr_month" value="{{ request('rr_month') }}">
                @endif
                @if(request()->has('ko_year'))
                    <input type="hidden" name="ko_year" value="{{ request('ko_year') }}">
                @endif
                @if(request()->has('ko_month'))
                    <input type="hidden" name="ko_month" value="{{ request('ko_month') }}">
                @endif
                @if(request()->has('peka_year'))
                    <input type="hidden" name="peka_year" value="{{ request('peka_year') }}">
                @endif

                <select name="year" id="filter-year"
                    class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $yr)
                        <option value="{{ $yr }}" {{ ($selectedYear ?? null) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>

                <div id="dashboard-loading-indicator" class="hidden flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl animate-pulse">
                    <svg class="animate-spin w-3.5 h-3.5 text-[#9DBF2A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Memuat...</span>
                </div>

                @if($selectedYear)
                    @php
                        $resetGlobalQuery = request()->only(['rr_year', 'rr_month', 'ko_year', 'ko_month', 'peka_year']);
                    @endphp
                    <a href="{{ request()->url() }}{{ !empty($resetGlobalQuery) ? '?' . http_build_query($resetGlobalQuery) : '' }}" class="text-xs text-slate-500 hover:text-slate-700 underline whitespace-nowrap">Reset Filter</a>
                @endif
            </form>

            @if(auth()->user()->isHsseRole())
                @include('components.executive-summary-btn', [
                    'selectedYear'   => $selectedYear,
                    'selectedMonth'  => $selectedMonth,
                    'maxBulanSipeka' => $maxBulanSipeka,
                    'tahunList'      => $tahunList ?? null,
                ])
                <a href="{{ route('findings.export') }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9DBF2A] transition-all shadow-sm">
                    <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export Report
                </a>
                <form action="{{ route('sipeka.upload') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <input type="file" name="sipeka_file" accept=".xlsx,.xls,.csv" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-slate-300 rounded-lg bg-white" required>
                    <button type="submit" class="bg-[#0055FF] hover:bg-[#0044CC] text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm flex items-center gap-2">
                        <span class="text-lg leading-none">+</span>
                        Import PEKA
                    </button>
                </form>
            @endif
        </div>

    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Card 1: Total Temuan -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Total Temuan</p>
                <h3 class="text-3xl font-bold text-slate-800" id="kpi-total">{{ number_format($kpi['total'] ?? 0) }}</h3>
                <div class="mt-2 text-xs font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full w-fit" id="kpi-badge">{{ ($selectedYear ?? null) ? 'Tahun ' . $selectedYear : 'Keseluruhan Data' }}</div>            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
        </div>

        <!-- Card 2: Status Open -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Open</p>
                <h3 class="text-3xl font-bold text-slate-800" id="kpi-open">{{ number_format($kpi['open'] ?? 0) }}</h3>
                <div class="mt-2 text-xs font-medium text-red-600 bg-red-50 px-2.5 py-1 rounded-full w-fit">Butuh Tindak Lanjut</div>            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 3: Status Closed -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Closed</p>
                <h3 class="text-3xl font-bold text-slate-800" id="kpi-closed">{{ number_format($kpi['closed'] ?? 0) }}</h3>
                <div class="mt-2 text-xs font-medium text-green-600 bg-green-50 px-2.5 py-1 rounded-full w-fit">Telah Diselesaikan</div>            </div>
            <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Chart 1: Pelaporan per Fungsi — full width dengan panel info -->
        @php
            $fi = $charts['fungsi_info'] ?? ['total'=>0,'breakdown'=>[],'tertinggi'=>null,'terendah'=>null];
        @endphp
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] overflow-hidden">
            <!-- Header card -->
            <div class="px-6 pt-5 pb-3 border-b border-slate-100">
                <h3 class="text-[22px] font-bold text-slate-800">1. Jumlah Pelaporan per Fungsi</h3>
                <p class="text-xs text-slate-400 mt-0.5">Menampilkan jumlah temuan yang dilaporkan oleh masing-masing fungsi berdasarkan data PEKA.</p>
            </div>
            <!-- Body: chart kiri + panel kanan -->
            <div class="flex flex-col md:flex-row">
                <!-- Pie Chart -->
                <div class="flex-1 relative p-4 min-h-[320px]">
                    <canvas id="chart1"></canvas>
                </div>
                <!-- Panel Info -->
                <div class="md:w-72 border-t md:border-t-0 md:border-l border-slate-100 p-5 flex flex-col gap-4 bg-slate-50/50">
                    <!-- Total -->
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-1">Total Pelaporan</p>
                        <p class="text-3xl font-bold text-slate-800">{{ number_format($fi['total']) }}</p>
                    </div>
                    <!-- Breakdown per fungsi -->
                    <div class="space-y-2">
                        @foreach($fi['breakdown'] as $namaFungsi => $jumlah)
                            @if($jumlah > 0)
                            @php
                                $pct = $fi['total'] > 0 ? number_format(($jumlah / $fi['total'] * 100), 2, ',', '.') : '0,00';
                                $warna = match($namaFungsi) {
                                    'Operation'        => 'bg-[#5AA2D7]',
                                    'Maintenance'      => 'bg-[#ED7D31]',
                                    'HSSE'             => 'bg-[#A5A5A5]',
                                    'Business Support' => 'bg-[#FFC000]',
                                    default            => 'bg-slate-400',
                                };
                            @endphp
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $warna }}"></span>
                                    <span class="text-sm text-slate-600 truncate">{{ $namaFungsi }}</span>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <span class="text-sm font-semibold text-slate-800">{{ number_format($jumlah) }}</span>
                                    <span class="text-xs text-slate-400 ml-1">({{ $pct }}%)</span>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                    <!-- Divider -->
                    <div class="border-t border-slate-200"></div>
                    <!-- Insight -->
                    <div class="space-y-2">
                        @if($fi['tertinggi'])
                        <div class="rounded-xl bg-green-50 border border-green-100 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-green-600 mb-0.5">Pelaporan Tertinggi</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $fi['tertinggi']['fungsi'] }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($fi['tertinggi']['jumlah']) }} pelaporan</p>
                        </div>
                        @endif
                        @if($fi['terendah'])
                        <div class="rounded-xl bg-amber-50 border border-amber-100 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-amber-600 mb-0.5">Pelaporan Terendah</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $fi['terendah']['fungsi'] }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($fi['terendah']['jumlah']) }} pelaporan</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ================================================================
             CHART 2 — REPORTING RATE per FUNGSI
        ================================================================ --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[380px]">
            @php
                $rrMode = $charts['reporting']['mode'] ?? 'distribusi';
                $rrData = $charts['reporting']['data'] ?? [];
                $hasNull = in_array(null, $rrData, true);
                $rrValidCount = count(array_filter($rrData, fn($v) => $v !== null));
                $isRrEmpty = ($rrValidCount === 0) || (($rrStatus ?? 'ok') === 'no_sipeka');

                // Teks periode: jika bulan dipilih → tampilkan nama bulan saja
                // jika hanya tahun → tampilkan label dari backend
                // jika tidak ada filter → tidak ada label periode
                if ($rrMode === 'manpower_rasio') {
                    if ($rrMonth && isset($bulanNamaList[$rrMonth])) {
                        $rrPeriodeLabel = $bulanNamaList[$rrMonth] . ' ' . ($rrYear ?? '');
                    } elseif ($selectedMonth ?? null) {
                        $rrPeriodeLabel = $bulanNamaList[$selectedMonth] . ' ' . ($selectedYear ?? '');
                    } else {
                        $rrPeriodeLabel = $charts['reporting']['periode_label'] ?? '';
                    }
                    $rrLabel = '2. Reporting Rate per Fungsi (' . $rrPeriodeLabel . ')';
                } else {
                    $rrLabel = '2. Reporting Rate per Fungsi';
                }
            @endphp

            {{-- Header: Judul + Filter in-card --}}
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-[22px] font-bold text-slate-800" id="chart2-title">{{ $rrLabel }}</h3>
                    <p class="text-xs font-mono text-slate-400" id="chart2-subtitle">
                        @if($rrMode === 'manpower_rasio')
                            temuan YTD ÷ (manpower × bulan)
                        @else
                            jumlah pelaporan ÷ manpower tahunan
                        @endif
                    </p>
                </div>
                {{-- Filter Tahun & Bulan — khusus Reporting Rate (Card 2) --}}
                <form action="" method="GET" onsubmit="return false;" class="flex flex-wrap items-center gap-2">
                    {{-- Pertahankan filter utama dan filter card lain yang sudah aktif --}}
                    @if($selectedYear)
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                    @endif
                    @if($selectedMonth)
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    @endif
                    @if($selectedPekaYear && $selectedPekaYear != ($selectedYear ?? now()->year))
                        <input type="hidden" name="peka_year" value="{{ $selectedPekaYear }}">
                    @endif
                    <select name="rr_year" id="rr-year-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        @if(!$latestDataYear)
                            <option value="">Semua Tahun</option>
                        @endif
                        @foreach($tahunList as $yr)
                            <option value="{{ $yr }}" {{ ($rrYear ?? null) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                    <select name="rr_month" id="rr-month-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        <option value="">Semua Bulan</option>
                        @foreach($rrBulanList as $num => $nama)
                            <option value="{{ $num }}" {{ ($rrMonth ?? null) == $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                    @if(($rrYear ?? null) != $latestDataYear || ($rrMonth ?? null) != $rrMaxBulan)
                        <a href="{{ route('dashboard') }}" class="text-xs text-slate-400 hover:text-slate-600 underline whitespace-nowrap">Reset</a>
                    @endif
                </form>
            </div>

            @if($rrMode === 'manpower_rasio' && $hasNull && !$isRrEmpty)
                <p id="chart2-warning" class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-1.5 mb-2">
                    ⚠️ Beberapa fungsi tidak memiliki data manpower untuk periode ini. Reporting Rate hanya ditampilkan untuk fungsi yang memiliki data manpower.
                </p>
            @endif
            <div class="flex-1 relative w-full h-full min-h-0 flex items-center justify-center">
                <canvas id="chart2" class="w-full h-full {{ $isRrEmpty ? 'hidden' : '' }}"></canvas>
                <div id="chart2_empty" class="{{ $isRrEmpty ? '' : 'hidden' }} flex-1 flex flex-col items-center justify-center p-6 text-center text-slate-400">
                    <svg class="w-12 h-12 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-sm font-medium text-slate-500">Data tidak tersedia</p>
                    <p class="text-xs text-slate-400 mt-1">Data temuan SIPEKA atau manpower untuk periode ini belum tersedia.</p>
                </div>
            </div>
        </div>

        {{-- ================================================================
             CHART REKAP PEKA BULANAN (Trending Temuan — revisi)
             Full width, hanya mengikuti filter TAHUN
        ================================================================ --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden flex flex-col relative" style="min-height:500px">
            
            {{-- Decorative Background Shapes (Match Image) --}}
            <!-- Top Right Yellow Circle -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-[#fff7d6] rounded-bl-full opacity-60 pointer-events-none"></div>
            <div class="absolute top-6 right-6 w-6 h-6 bg-[#fde047] rounded-full opacity-80 pointer-events-none"></div>
            
            <!-- Bottom Left Blobs -->
            <div class="absolute bottom-0 left-0 w-40 h-24 bg-[#fff7d6] rounded-tr-full opacity-60 pointer-events-none"></div>
            <div class="absolute bottom-0 left-10 w-16 h-16 bg-[#bfdbfe] rounded-t-full opacity-60 pointer-events-none"></div>
            
            <!-- Bottom Right Sparks -->
            <div class="absolute bottom-6 right-8 text-[#fde047] opacity-80 pointer-events-none">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="2" y1="22" x2="6" y2="18"></line>
                    <line x1="8" y1="23" x2="10" y2="17"></line>
                    <line x1="2" y1="15" x2="7" y2="14"></line>
                </svg>
            </div>

            <div class="p-6 flex flex-col flex-1 relative z-10">
                {{-- Header --}}
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M5 9h4v11H5zm6-6h4v17h-4zm6 4h4v13h-4z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800">Rekap PEKA ({{ $selectedPekaYear }})</h3>
                            <p class="text-sm text-slate-500">Jumlah temuan berdasarkan kategori dan tren bulanan</p>
                        </div>
                    </div>
                    {{-- Filter Tahun Mandiri — hanya untuk Rekap PEKA --}}
                    <form action="" method="GET" onsubmit="return false;" class="flex items-center gap-2">
                        {{-- Pertahankan filter chart lain yang sudah aktif --}}
                        @if($selectedYear)
                            <input type="hidden" name="year" value="{{ $selectedYear }}">
                        @endif
                        @if($selectedMonth)
                            <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        @endif
                        <select name="peka_year" id="peka-year-filter"
                            class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                            @foreach($tahunList as $yr)
                                <option value="{{ $yr }}" {{ $selectedPekaYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

            {{-- Body: Chart + Closing Rate Card --}}
            <div class="flex flex-col md:flex-row gap-6 flex-1 min-h-0 relative">

                {{-- Chart Canvas Area --}}
                <div class="flex-1 relative min-h-0" style="min-height:350px">
                    <canvas id="chartTrending"></canvas>
                </div>

                {{-- Closing Rate Card (Absolute/Floating on Desktop, flow on Mobile) --}}
                <div class="md:absolute md:-top-4 md:right-4 flex-shrink-0 flex flex-col gap-3 z-10">
                    <div id="closing-rate-card"
                         class="bg-[#fff0f3] border-2 border-[#ffe0e6] rounded-3xl px-6 py-4 flex items-center gap-5 shadow-sm">
                        <!-- Icon Circle -->
                        <div class="w-12 h-12 bg-[#fb3f6c] bg-opacity-10 text-[#fb3f6c] rounded-full flex items-center justify-center flex-shrink-0 shadow-sm border border-[#fb3f6c]/20 relative">
                            <!-- Inner Solid Circle -->
                            <div class="w-8 h-8 bg-[#fb3f6c] text-white rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-[15px] font-bold text-[#1e293b] mb-0.5">Closing Rate :</p>
                            <div class="flex items-center gap-2 relative">
                                <p id="closing-rate-value" class="text-4xl font-extrabold text-[#fb3f6c] leading-none tracking-tight">—</p>
                                <!-- Sparkles -->
                                <div class="absolute -right-6 top-0 text-[#fde047]">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="2" y1="12" x2="6" y2="12"></line>
                                        <line x1="18" y1="12" x2="22" y2="12"></line>
                                        <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                        <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                        <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>{{-- end Body flex --}}

            {{-- Custom Legend --}}
            <div class="flex flex-wrap items-center justify-center gap-6 mt-4 mb-2 z-10 relative">
                <!-- Safe Action -->
                <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity" onclick="const c = document.getElementById('chartTrending').chartInstance; c.setDatasetVisibility(0, !c.isDatasetVisible(0)); c.update();">
                    <span class="w-4 h-4 rounded-full bg-[#60a5fa]"></span>
                    <span class="text-base font-bold text-slate-800">Safe Action</span>
                </div>
                <!-- Safe Condition -->
                <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity" onclick="const c = document.getElementById('chartTrending').chartInstance; c.setDatasetVisibility(1, !c.isDatasetVisible(1)); c.update();">
                    <span class="w-4 h-4 rounded-full bg-[#fde047]"></span>
                    <span class="text-base font-bold text-slate-800">Safe Condition</span>
                </div>
                <!-- Unsafe Action -->
                <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity" onclick="const c = document.getElementById('chartTrending').chartInstance; c.setDatasetVisibility(2, !c.isDatasetVisible(2)); c.update();">
                    <span class="w-4 h-4 rounded-full bg-[#fdba74]"></span>
                    <span class="text-base font-bold text-slate-800">Unsafe Action</span>
                </div>
                <!-- Unsafe Condition -->
                <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity" onclick="const c = document.getElementById('chartTrending').chartInstance; c.setDatasetVisibility(3, !c.isDatasetVisible(3)); c.update();">
                    <span class="w-4 h-4 rounded-full bg-[#6ee7b7]"></span>
                    <span class="text-base font-bold text-slate-800">Unsafe Condition</span>
                </div>
                <!-- Total -->
                <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity ml-2" onclick="const c = document.getElementById('chartTrending').chartInstance; c.setDatasetVisibility(4, !c.isDatasetVisible(4)); c.update();">
                    <div class="relative w-7 h-7 flex items-center justify-center">
                        <!-- Halo pinggiran -->
                        <div class="absolute inset-0 bg-[#fb3f6c] opacity-15 rounded-full"></div>
                        <!-- Garis horisontal tembus -->
                        <div class="absolute w-full h-[3px] bg-[#fb3f6c]"></div>
                        <!-- Titik tengah padat -->
                        <div class="absolute w-3.5 h-3.5 bg-[#fb3f6c] rounded-full"></div>
                    </div>
                    <span class="text-base font-bold text-slate-800">Total</span>
                </div>
            </div>

            {{-- Summary Text --}}
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                <p id="trending-summary" class="text-base font-bold text-slate-800 leading-relaxed">
                    {{-- diisi via JavaScript --}}
                </p>
            </div>

        </div>{{-- end card --}}


        <!-- Chart 3: Kategori PEKA (Pie) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="min-height:400px">
            <h3 class="text-[22px] font-bold text-slate-800 mb-4">3. Kategori PEKA</h3>
            <div class="flex-1 relative w-full" style="min-height:300px">
                <canvas id="chart3"></canvas>
            </div>
            <!-- Statistik Positif / Negatif -->
            <div id="chart3-stats" class="mt-4 bg-slate-50 border border-slate-200 rounded-xl px-5 py-3.5 text-base font-bold text-slate-800 space-y-2 hidden">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400 inline-block flex-shrink-0"></span>
                    <span class="font-bold">Jumlah laporan positif <strong id="chart3-positif" class="font-bold text-slate-900">-</strong></span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400 inline-block flex-shrink-0"></span>
                    <span class="font-bold">Jumlah laporan negatif <strong id="chart3-negatif" class="font-bold text-slate-900">-</strong></span>
                </div>
            </div>
        </div>

        <!-- Chart 4: Keterlibatan Observasi (Stacked Vertical Bar) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            {{-- Header: Judul + Filter in-card --}}
            <div class="flex flex-wrap items-start justify-between gap-3 mb-1">
                <div>
                    <h3 class="text-[22px] font-bold text-slate-800 flex flex-wrap items-center gap-2">
                        <span>4. Keterlibatan dalam Observasi</span>
                        <span class="inline-flex items-center gap-1 text-[11px] text-blue-600 font-medium bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100" title="Klik bar segmen Belum Melapor untuk melihat daftar personil">
                            <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                            Klik bar untuk daftar personil
                        </span>
                        <button type="button" id="btn-reported-all"
                                onclick="window.openReportedModal && window.openReportedModal('all')"
                                class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-medium bg-emerald-50 hover:bg-emerald-100 px-2.5 py-0.5 rounded-full border border-emerald-200 transition"
                                title="Daftar Sudah Melapor untuk seluruh 4 fungsi utama">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Daftar sudah melapor — semua fungsi
                        </button>
                        <button type="button" id="btn-unreported-all"
                                onclick="window.openUnreportedModal && window.openUnreportedModal('all')"
                                class="inline-flex items-center gap-1 text-[11px] text-slate-600 font-medium bg-slate-50 hover:bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200 transition"
                                title="Daftar Belum Melapor untuk seluruh 4 fungsi utama">
                            Daftar belum melapor — semua fungsi
                        </button>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Rumus: Pelapor unik Jan s/d bulan terpilih &divide; Manpower bulan terpilih &times; 100%
                    </p>
                </div>
                {{-- Filter Tahun & Bulan — khusus Keterlibatan Observasi (Card 4) --}}
                <form action="" method="GET" onsubmit="return false;" class="flex flex-wrap items-center gap-2">
                    {{-- Pertahankan filter utama dan filter card lain yang sudah aktif --}}
                    @if($selectedYear)
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                    @endif
                    @if($selectedMonth)
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    @endif
                    @if($rrYear)
                        <input type="hidden" name="rr_year" value="{{ $rrYear }}">
                    @endif
                    @if($rrMonth)
                        <input type="hidden" name="rr_month" value="{{ $rrMonth }}">
                    @endif
                    @if($selectedPekaYear)
                        <input type="hidden" name="peka_year" value="{{ $selectedPekaYear }}">
                    @endif
                    <select name="ko_year" id="ko-year-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        @if(!$latestDataYear)
                            <option value="">Semua Tahun</option>
                        @endif
                        @foreach($tahunList as $yr)
                            <option value="{{ $yr }}" {{ ($koYear ?? null) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                    <select name="ko_month" id="ko-month-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        <option value="">Semua Bulan</option>
                        @foreach($bulanNamaList as $num => $nama)
                            <option value="{{ $num }}" {{ ($koMonth ?? null) == $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                    @if(($koYear ?? null) != $latestDataYear || ($koMonth ?? null) != $koMaxBulan)
                        <a href="{{ route('dashboard') }}" class="text-xs text-slate-400 hover:text-slate-600 underline whitespace-nowrap">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Notice: belum pilih bulan (Blade — ditampilkan server-side) --}}
            <div id="chart4-blade-notice" class="{{ !($koMonth ?? null) ? '' : 'hidden' }} mb-3 text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                ℹ️ Pilih <strong>Bulan</strong> pada filter di atas untuk menampilkan persentase keterlibatan.
            </div>

            {{-- Notice JS: muncul jika manpower tidak tersedia (diisi via JS) --}}
            <div id="chart4-notice" class="hidden mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2"></div>

            <div class="flex-1 relative w-full h-full">
                <canvas id="chart4"></canvas>
            </div>
        </div>

        <!-- Chart 5: Persentase Temuan per Fungsi (Stacked Vertical Bar) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-[22px] font-bold text-slate-800 mb-4">5. Rekap Persentase Temuan per Fungsi</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart5"></canvas>
            </div>
        </div>

        <!-- Chart 6: Persentase Temuan Ber-SAP (Donut) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-[22px] font-bold text-slate-800 mb-1">6. Rekap Persentase Temuan Ber-SAP Berdasarkan Asset Owner</h3>
            <p class="text-xs text-slate-400 mb-4">Distribusi temuan ber-SAP seluruh fungsi berdasarkan Asset Owner</p>
            <div class="flex-1 relative w-full h-full flex items-center justify-center">
                <canvas id="chart6"></canvas>
                <div id="chart6_empty" class="hidden text-center text-slate-400 text-sm py-8">
                    Belum ada temuan berstatus SAP valid untuk periode ini.
                </div>
            </div>
        </div>

        <!-- Chart 7: Unsafe Action (Horizontal Bar) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="height:340px">
            <h3 class="text-[22px] font-bold text-slate-800 mb-3">7. Unsafe Action Category</h3>
            <div class="flex-1 relative w-full" style="min-height:0">
                <canvas id="chart7"></canvas>
            </div>
        </div>

        <!-- Chart 8: Unsafe Condition (Horizontal Bar) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="height:600px">
            <h3 class="text-[22px] font-bold text-slate-800 mb-3">8. Unsafe Condition Category</h3>
            <div class="flex-1 relative w-full" style="min-height:0">
                <canvas id="chart8"></canvas>
            </div>
        </div>

    </div>

    {{-- AJAX Loading Indicator & Error Toast --}}
    <div id="dashboard-loading-indicator" class="hidden fixed bottom-6 right-6 z-50 flex items-center gap-2 px-4 py-2.5 bg-slate-900/85 text-white text-xs font-semibold rounded-full shadow-2xl backdrop-blur-sm animate-pulse">
        <svg class="animate-spin w-4 h-4 text-[#9DBF2A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
        <span>Memperbarui data dashboard...</span>
    </div>

    <div id="dashboard-error-toast" class="hidden fixed bottom-6 right-6 z-50 max-w-md flex items-center gap-2 px-4 py-3 bg-red-600 text-white text-xs font-semibold rounded-xl shadow-2xl">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span id="dashboard-error-msg"></span>
    </div>

</div>

{{-- Modal Daftar Belum Melapor --}}
@include('components.unreported-modal')
{{-- Modal Daftar Sudah Melapor --}}
@include('components.reported-modal')

@endsection

@push('scripts')

<script>
    window.chartInstances = window.chartInstances || {};

    function getOrCreateChart(canvasId, config) {
        if (window.chartInstances[canvasId]) {
            try {
                window.chartInstances[canvasId].destroy();
            } catch(e) {
                console.warn('Error destroying chart ' + canvasId, e);
            }
            delete window.chartInstances[canvasId];
        }
        const canvas = typeof canvasId === 'string' ? document.getElementById(canvasId) : canvasId;
        if (!canvas) return null;
        const idKey = canvas.id || canvasId;
        const chart = new Chart(canvas, config);
        window.chartInstances[idKey] = chart;
        return chart;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const initialCharts = @json($charts ?? []);

        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748B';
        const commonOptions = { responsive: true, maintainAspectRatio: false };

        const colors = {
            'Operation': '#5AA2D7', 
            'Maintenance': '#ED7D31', 
            'HSSE': '#A5A5A5', 
            'Business Support': '#FFC000',
            'Safe Action': '#5AA2D7',
            'Safe Condition': '#ED7D31',
            'Unsafe Action': '#A5A5A5',
            'Unsafe Condition': '#FFC000',
        };

        // Helper to get array of values and labels from assoc array
        const getLabels = (obj) => Object.keys(obj || {});
        const getValues = (obj) => Object.values(obj || {});
        const getBgColors = (keys) => keys.map(k => colors[k] || '#9CA3AF');

        window.renderAllCharts = function(charts, dataExtra = {}) {
            if (!charts || Object.keys(charts).length === 0) return;

            // 1. Pie Chart - Jumlah Pelaporan per Fungsi
            const chart1Labels = getLabels(charts.fungsi);
            const chart1Values = getValues(charts.fungsi);
            const chart1Total  = chart1Values.reduce((a, b) => a + b, 0);

            getOrCreateChart('chart1', {
            type: 'pie',
            plugins: [ChartDataLabels],
            data: {
                labels: chart1Labels,
                datasets: [{
                    data: chart1Values,
                    backgroundColor: getBgColors(chart1Labels),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 16,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            color: '#1e293b',
                            padding: 16,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    datalabels: {
                        color: '#1e293b',
                        font: {
                            weight: 'bold',
                            size: 16,
                            family: "'Inter', sans-serif"
                        },
                        formatter: (value) => {
                            if (value === 0 || chart1Total === 0) return null;
                            const pct = ((value / chart1Total) * 100).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            return pct + '%';
                        }
                    },
                    tooltip: {
                        titleFont: {
                            size: 15,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw;
                                const pct = chart1Total > 0 ? ((val / chart1Total) * 100).toFixed(1) : 0;
                                return [
                                    ' ' + val.toLocaleString('id-ID') + ' Pelaporan',
                                    ' ' + pct + '%'
                                ];
                            }
                        }
                    }
                }
            }
        });

        // 2. Horizontal Bar - Reporting Rate per Fungsi (+ AREA LHD di paling atas)
        // null = manpower belum tersedia, skip dari chart
        const rrAllData = charts.reporting?.data || {};
        const rrMode    = charts.reporting?.mode || 'distribusi';

        // Tampilkan semua label (termasuk AREA LHD di paling atas)
        // null = manpower belum tersedia, skip dari chart
        const rrLabels = Object.keys(rrAllData).filter(k => rrAllData[k] !== null);
        const rrValues = rrLabels.map(k => rrAllData[k]);
        
        const chart2El      = document.getElementById('chart2');
        const chart2EmptyEl = document.getElementById('chart2_empty');
        const chart2WarnEl  = document.getElementById('chart2-warning');

        const isRrEmpty = rrLabels.length === 0 || (dataExtra && dataExtra.rrStatus === 'no_sipeka');

        if (isRrEmpty) {
            if (chart2El) chart2El.classList.add('hidden');
            if (chart2EmptyEl) chart2EmptyEl.classList.remove('hidden');
            if (chart2WarnEl) chart2WarnEl.classList.add('hidden');
            if (window.chartInstances && window.chartInstances['chart2']) {
                try {
                    window.chartInstances['chart2'].destroy();
                } catch(e) {}
                delete window.chartInstances['chart2'];
            }
        } else {
            if (chart2El) chart2El.classList.remove('hidden');
            if (chart2EmptyEl) chart2EmptyEl.classList.add('hidden');
            if (chart2WarnEl) {
                const hasNull = Object.values(rrAllData).includes(null);
                if (rrMode === 'manpower_rasio' && hasNull) {
                    chart2WarnEl.classList.remove('hidden');
                } else {
                    chart2WarnEl.classList.add('hidden');
                }
            }

            const rrColorMap = {
                'AREA LHD': '#002060',
                'Operation': '#5B9BD5',
                'Maintenance': '#ED7D31',
                'HSSE': '#A5A5A5',
                'Business Support': '#FFC000'
            };
            const rrBgColors = rrLabels.map(l => rrColorMap[l] || '#9CA3AF');

            if (chart2El) {
                getOrCreateChart('chart2', {
                    type: 'bar',
                    data: { 
                        labels: rrLabels, 
                        datasets: [{ 
                            data: rrValues, 
                            backgroundColor: rrBgColors,
                            borderRadius: 4,
                        }] 
                    },
                    options: { 
                        ...commonOptions, 
                        indexAxis: 'y', 
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                titleFont: {
                                    size: 15,
                                    weight: 'bold',
                                    family: "'Inter', sans-serif"
                                },
                                bodyFont: {
                                    size: 14,
                                    weight: 'bold',
                                    family: "'Inter', sans-serif"
                                },
                                padding: 12,
                                callbacks: {
                                    label: (ctx) => {
                                        const val = ctx.raw;
                                        return rrMode === 'manpower_rasio'
                                            ? ` ${val} (temuan YTD ÷ manpower × bulan)`
                                            : ` ${val}`;
                                    }
                                }
                            }
                        },
                        scales: { 
                            x: { 
                                beginAtZero: true, 
                                grid: { display: false },
                                ticks: {
                                    font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                    color: '#1e293b'
                                }
                            },
                            y: { 
                                grid: { display: false },
                                ticks: {
                                    font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                    color: '#1e293b'
                                }
                            }
                        },
                        animation: {
                            onComplete: function() {
                                var chartInstance = this;
                                var ctx = chartInstance.ctx;
                                ctx.font = "bold 14px 'Inter', sans-serif";
                                ctx.textAlign = 'left';
                                ctx.textBaseline = 'middle';
                                ctx.fillStyle = '#1e293b';

                                this.data.datasets.forEach(function (dataset, i) {
                                    var meta = chartInstance.getDatasetMeta(i);
                                    meta.data.forEach(function (bar, index) {
                                        var data = dataset.data[index];
                                        var label = rrMode === 'manpower_rasio' ? data : data;
                                        ctx.fillText(label, bar.x + 5, bar.y);
                                    });
                                });
                            }
                        }
                    }
                });
            }
        }

        // ================================================================
        // CHART REKAP PEKA BULANAN (Trending Temuan — revisi)
        // Stacked Bar (4 kategori) + Total Line
        // Hanya mengikuti filter TAHUN, bukan bulan
        // ================================================================
        (function() {
            const trendingRaw    = charts.trending || {};
            const trendMonths    = trendingRaw.months    || [];
            const annualTotal    = trendingRaw.annual_total || 0;
            const closedTotal    = trendingRaw.closed_total  || 0;
            const closingRate    = trendingRaw.closing_rate  || 0;
            const trendYear      = trendingRaw.year          || null;

            // ---- Label bulan (fallback jika months kosong) ---------------
            const defaultLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                                   'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const bulanLabels = trendMonths.length === 12
                ? trendMonths.map(m => m.month)
                : defaultLabels;

            // ---- Ekstrak data per kategori --------------------------------
            const saData  = trendMonths.map(m => m.safe_action      || 0);
            const scData  = trendMonths.map(m => m.safe_condition    || 0);
            const uaData  = trendMonths.map(m => m.unsafe_action     || 0);
            const ucData  = trendMonths.map(m => m.unsafe_condition  || 0);
            const totData = trendMonths.map(m => m.total             || 0);

            // Isi pad jika months < 12 (misal tahun belum dipilih)
            while (saData.length  < 12) { saData.push(0);  scData.push(0);
                                          uaData.push(0);  ucData.push(0);
                                          totData.push(0); }

            // ---- Custom Plugin: Background Bar untuk Total Line --------
            const totalBgPlugin = {
                id: 'totalBgPlugin',
                beforeDatasetsDraw(chart) {
                    const { ctx, scales: { x, y } } = chart;
                    // Gunakan metadata dataset pertama untuk x-position
                    const meta0 = chart.getDatasetMeta(0);
                    if (!meta0 || !meta0.data.length) return;

                    ctx.save();
                    totData.forEach((total, i) => {
                        if (!total || total === 0) return;
                        const bar = meta0.data[i];
                        if (!bar) return;

                        const bx     = bar.x;
                        const bw     = bar.width * 1.05; // Sedikit lebih lebar dari bar utama
                        const top    = y.getPixelForValue(total);
                        const bottom = y.getPixelForValue(0);
                        const r      = 8; // border-radius

                        // Fill background bar (warna mint transparent)
                        ctx.fillStyle = 'rgba(110, 231, 183, 0.2)'; // Sangat soft mint green
                        
                        ctx.beginPath();
                        ctx.moveTo(bx - bw / 2 + r, top);
                        ctx.lineTo(bx + bw / 2 - r, top);
                        ctx.quadraticCurveTo(bx + bw / 2, top, bx + bw / 2, top + r);
                        ctx.lineTo(bx + bw / 2, bottom);
                        ctx.lineTo(bx - bw / 2, bottom);
                        ctx.lineTo(bx - bw / 2, top + r);
                        ctx.quadraticCurveTo(bx - bw / 2, top, bx - bw / 2 + r, top);
                        ctx.closePath();
                        ctx.fill();
                    });
                    ctx.restore();
                }
            };

            // ---- Render Chart.js (mixed: bar + line) ---------------------
            const trendCanvas = document.getElementById('chartTrending');
            if (trendCanvas) {
                const chartInstance = getOrCreateChart('chartTrending', {
                    type: 'bar',
                    plugins: [totalBgPlugin],
                    data: {
                        labels: bulanLabels,
                        datasets: [
                            // ---- 4 Stacked Bar ----
                            {
                                label: 'Safe Action',
                                type: 'bar',
                                data: saData,
                                backgroundColor: '#60a5fa', // soft blue
                                stack: 'peka',
                                order: 2,
                                barPercentage: 0.8,
                                categoryPercentage: 0.85
                            },
                            {
                                label: 'Safe Condition',
                                type: 'bar',
                                data: scData,
                                backgroundColor: '#fde047', // soft yellow
                                stack: 'peka',
                                order: 2,
                                barPercentage: 0.8,
                                categoryPercentage: 0.85
                            },
                            {
                                label: 'Unsafe Action',
                                type: 'bar',
                                data: uaData,
                                backgroundColor: '#fdba74', // soft orange
                                stack: 'peka',
                                order: 2,
                                barPercentage: 0.8,
                                categoryPercentage: 0.85
                            },
                            {
                                label: 'Unsafe Condition',
                                type: 'bar',
                                data: ucData,
                                backgroundColor: '#6ee7b7', // soft mint green
                                stack: 'peka',
                                borderRadius: { topLeft: 12, topRight: 12 },
                                order: 2,
                                barPercentage: 0.8,
                                categoryPercentage: 0.85
                            },
                            // ---- Total Line ----
                            {
                                label: 'Total',
                                type: 'line',
                                data: totData,
                                borderColor: '#fb3f6c', // pink/red
                                backgroundColor: '#fb3f6c',
                                borderWidth: 3,
                                pointBackgroundColor: '#fb3f6c',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 7,
                                pointHoverRadius: 9,
                                fill: false,
                                tension: 0,
                                order: 1,
                                stack: undefined,
                            },
                        ]
                    },
                    options: {
                        ...commonOptions,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            x: {
                                stacked: true,
                                grid: { display: false, drawBorder: false },
                                ticks: { font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" }, color: '#1e293b' },
                                border: { display: false }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                                border: { display: false },
                                ticks: {
                                    stepSize: 200,
                                    callback: (v) => v.toLocaleString('id-ID'),
                                    font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                    color: '#1e293b',
                                    padding: 10
                                },
                                title: {
                                    display: true,
                                    text: 'Temuan',
                                    font: { size: 16, family: "'Inter', sans-serif", weight: 'bold' },
                                    color: '#1e293b',
                                    padding: { bottom: 15 }
                                }
                            },
                        },
                        plugins: {
                            legend: {
                                display: false, // Disembunyikan, menggunakan custom HTML legend
                            },
                            tooltip: {
                                titleFont: {
                                    size: 15,
                                    weight: 'bold',
                                    family: "'Inter', sans-serif"
                                },
                                bodyFont: {
                                    size: 14,
                                    weight: 'bold',
                                    family: "'Inter', sans-serif"
                                },
                                padding: 12,
                                callbacks: {
                                    label: function(ctx) {
                                        const val = ctx.raw;
                                        const suffix = ctx.dataset.label === 'Total'
                                            ? ' laporan (total)'
                                            : ' laporan';
                                        return ` ${ctx.dataset.label}: ${val.toLocaleString('id-ID')}${suffix}`;
                                    }
                                }
                            },
                        },
                    }
                });
                
                // Simpan instance ke elemen untuk bisa diakses custom legend
                trendCanvas.chartInstance = chartInstance;
            }

            // ---- Update Closing Rate Card ---------------------------------
            const fmtPct = (r) => r.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            const crVal   = document.getElementById('closing-rate-value');

            if (crVal)   crVal.textContent   = annualTotal > 0 ? fmtPct(closingRate) + '%' : '—';

            // ---- Update Summary Text --------------------------------------
            const summaryEl = document.getElementById('trending-summary');
            if (summaryEl && annualTotal > 0) {
                const pctStr   = fmtPct(closingRate).replace('.', ',');
                const totalStr = annualTotal.toLocaleString('id-ID');
                const closStr  = closedTotal.toLocaleString('id-ID');
                summaryEl.innerHTML =
                    `<span class="text-slate-600 font-medium">&#9679;</span> `
                    + `Jumlah Pelaporan PEKA <strong>${totalStr} laporan</strong> `
                    + `dengan closing rate <strong>${pctStr}%</strong> `
                    + `(<strong>${closStr} laporan</strong>).`;
            }
        })();


        const chart3Labels = getLabels(charts.kategori);
        const chart3Values = getValues(charts.kategori);
        const chart3Total = chart3Values.reduce((a, b) => a + b, 0);

        getOrCreateChart('chart3', {
            type: 'pie',
            plugins: [ChartDataLabels],
            data: { 
                labels: chart3Labels, 
                datasets: [{ 
                    data: chart3Values, 
                    backgroundColor: getBgColors(chart3Labels),
                    borderWidth: 2,
                    borderColor: '#fff',
                }] 
            },
            options: { 
                ...commonOptions, 
                plugins: { 
                    legend: {
                        position: 'right',
                        labels: {
                            font: {
                                size: 16,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            color: '#1e293b',
                            padding: 16,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    datalabels: {
                        color: '#1e293b',
                        font: {
                            weight: 'bold',
                            size: 16,
                            family: "'Inter', sans-serif"
                        },
                        formatter: (value) => {
                            if (value === 0 || chart3Total === 0) return null;
                            const pct = ((value / chart3Total) * 100).toFixed(2).replace('.', ',');
                            return pct + '%';
                        }
                    },
                    tooltip: {
                        titleFont: {
                            size: 15,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw;
                                const pct = chart3Total > 0
                                    ? ((val / chart3Total) * 100).toFixed(2).replace('.', ',')
                                    : '0,00';
                                return [
                                    ' ' + val.toLocaleString('id-ID') + ' Pelaporan',
                                    ' ' + pct + '%'
                                ];
                            }
                        }
                    }
                } 
            }
        });

        // Hitung & tampilkan statistik Positif / Negatif (di bawah pie)
        if (chart3Total > 0) {
            const positifKeys = ['Safe Action', 'Safe Condition'];
            const negatifKeys  = ['Unsafe Action', 'Unsafe Condition'];
            const positifTotal = positifKeys.reduce((sum, k) => sum + (charts.kategori[k] || 0), 0);
            const negatifTotal  = negatifKeys.reduce((sum, k) => sum + (charts.kategori[k] || 0), 0);
            const fmt = (n) => (chart3Total > 0 ? (n / chart3Total * 100).toFixed(2) : '0.00').replace('.', ',');
            const statsEl = document.getElementById('chart3-stats');
            if (statsEl) {
                document.getElementById('chart3-positif').textContent = fmt(positifTotal) + '%';
                document.getElementById('chart3-negatif').textContent  = fmt(negatifTotal)  + '%';
                statsEl.classList.remove('hidden');
            }
        }

        // ================================================================
        // 4. Stacked Vertical Bar — Keterlibatan dalam Observasi
        //
        // Data: [fungsi => float|null]
        //   float = persentase keterlibatan (pelapor unik ÷ manpower × 100)
        //   null  = bulan belum dipilih, atau manpower tidak tersedia
        //
        // FIX: Chart SELALU dirender selama ada minimal 1 fungsi.
        //   - Fungsi null → placeholder abu-abu (data belum tersedia)
        //   - Fungsi float → bar normal (Keterlibatan + Sisa 100%)
        //   - allNull → tetap render chart placeholder + notice informatif
        // ================================================================
        (function() {
            const invRaw    = charts.keterlibatan || {};
            const invLabels = Object.keys(invRaw);
            const notice4   = document.getElementById('chart4-notice');

            // Jika tidak ada label sama sekali, tidak ada yang bisa dirender
            if (invLabels.length === 0) return;

            const allNull  = invLabels.every(k => invRaw[k] === null);
            const someNull = invLabels.some(k  => invRaw[k] === null);

            // ---- Tampilkan notice yang sesuai --------------------------------
            const bladeNotice4 = document.getElementById('chart4-blade-notice');
            const activeMonth = (dataExtra && dataExtra.koMonth !== undefined) ? dataExtra.koMonth : @json($koMonth ?? null);

            if (activeMonth === null) {
                if (bladeNotice4) bladeNotice4.classList.remove('hidden');
                if (notice4) notice4.classList.add('hidden');
            } else {
                if (bladeNotice4) bladeNotice4.classList.add('hidden');
                if (notice4) {
                    if (allNull) {
                        notice4.innerHTML = '⚠️ Data manpower belum tersedia untuk periode ini. Pastikan data manpower sudah diinput di menu <strong>Master Manpower</strong>.';
                        notice4.classList.remove('hidden');
                    } else if (someNull) {
                        const missingFungsi = invLabels.filter(k => invRaw[k] === null).join(', ');
                        notice4.innerHTML = `⚠️ Manpower belum tersedia untuk: <strong>${missingFungsi}</strong>. Fungsi lain ditampilkan normal.`;
                        notice4.classList.remove('hidden');
                    } else {
                        notice4.classList.add('hidden');
                    }
                }
            }

            // ---- Siapkan data chart -----------------------------------------
            // Fungsi dengan nilai: gunakan nilai aktual (capped 100 untuk visual)
            // Fungsi null: Keterlibatan=0, Sisa=100 (bar abu-abu penuh)
            const invData = invLabels.map(k => invRaw[k] !== null ? Math.min(invRaw[k], 100) : 0);
            const remData = invLabels.map(k => invRaw[k] !== null ? Math.max(100 - Math.min(invRaw[k], 100), 0) : 100);

            // Warna: fungsi dengan data → normal; fungsi null → abu-abu
            const ketBgColors = invLabels.map(k => invRaw[k] !== null ? '#ED7D31' : 'transparent');
            const sisBgColors = invLabels.map(k => invRaw[k] !== null ? '#5AA2D7'  : '#E2E8F0');

            // ---- Render Chart ------------------------------------------------
            getOrCreateChart('chart4', {
                type: 'bar',
                plugins: [ChartDataLabels],
                data: {
                    labels: invLabels,
                    datasets: [
                        {
                            label: 'Keterlibatan',
                            data: invData,
                            backgroundColor: ketBgColors,
                            datalabels: {
                                display: (ctx) => {
                                    const key = invLabels[ctx.dataIndex];
                                    return invRaw[key] !== null && invData[ctx.dataIndex] > 0;
                                },
                                color: '#fff',
                                anchor: 'center',
                                align: 'center',
                                font: { weight: 'bold', size: 16, family: "'Inter', sans-serif" },
                                formatter: (value, ctx) => {
                                    const key = invLabels[ctx.dataIndex];
                                    if (invRaw[key] === null) return null;
                                    const val = Math.round(invRaw[key]);
                                    return val > 0 ? val + '%' : null;
                                }
                            }
                        },
                        {
                            label: 'Belum Melapor',
                            data: remData,
                            backgroundColor: sisBgColors,
                            datalabels: {
                                display: (ctx) => {
                                    // Tampilkan label "N/A" di tengah bar abu-abu jika null
                                    const key = invLabels[ctx.dataIndex];
                                    return invRaw[key] === null;
                                },
                                color: '#94A3B8',
                                anchor: 'center',
                                align: 'center',
                                font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                formatter: () => 'N/A',
                            }
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    onHover: (event, chartElement) => {
                        const canvas = event.native?.target || document.getElementById('chart4');
                        if (canvas) {
                            canvas.style.cursor = chartElement[0] ? 'pointer' : 'default';
                        }
                    },
                    onClick: (event, elements) => {
                        if (!elements || elements.length === 0) return;
                        const el = elements[0];
                        const key = invLabels[el.index];
                        if (!key) return;

                        if (el.datasetIndex === 0) {
                            if (typeof window.openReportedModal === 'function') {
                                window.openReportedModal(key);
                            }
                        } else {
                            if (typeof window.openUnreportedModal === 'function') {
                                window.openUnreportedModal(key);
                            }
                        }
                    },
                    scales: {
                        x: { 
                            stacked: true, 
                            grid: { display: false },
                            ticks: {
                                font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b'
                            }
                        },
                        y: {
                            stacked: true,
                            max: 100,
                            ticks: { 
                                callback: v => v + '%',
                                font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b'
                            }
                        }
                    },
                    plugins: {
                        legend: { 
                            position: 'bottom',
                            labels: {
                                font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b',
                                padding: 16,
                                boxWidth: 16,
                                boxHeight: 16
                            }
                        },
                        tooltip: {
                            titleFont: {
                                size: 15,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            bodyFont: {
                                size: 14,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            padding: 12,
                            callbacks: {
                                label: function(ctx) {
                                    const key = invLabels[ctx.dataIndex];
                                    if (invRaw[key] === null) {
                                        return ctx.datasetIndex === 1
                                            ? ' Data manpower belum tersedia'
                                            : null;
                                    }
                                    if (ctx.datasetIndex === 1) return null; // sembunyikan baris "Belum Melapor" di tooltip
                                    return ` Keterlibatan: ${invRaw[key].toLocaleString('id-ID', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    })}%`;
                                }
                            }
                        }
                    }
                }
            });
        })();

        // 5. Stacked Vertical Bar - Rekap % Temuan Fungsi
        const ptLabels = getLabels(charts.persentase_fungsi);
        const closedData = ptLabels.map(l => charts.persentase_fungsi[l].closed);
        const openData = ptLabels.map(l => charts.persentase_fungsi[l].open);
        
        getOrCreateChart('chart5', {
            type: 'bar',
            plugins: [ChartDataLabels],
            data: {
                labels: ptLabels,
                datasets: [
                    {
                        label: 'Closed',
                        data: closedData,
                        backgroundColor: '#548235',
                        datalabels: {
                            display: (ctx) => {
                                const val = closedData[ctx.dataIndex];
                                return val !== null && val !== undefined && Math.round(val) > 0;
                            },
                            color: '#fff',
                            anchor: 'center',
                            align: 'center',
                            font: { weight: 'bold', size: 16, family: "'Inter', sans-serif" },
                            formatter: (value) => {
                                const val = Math.round(value);
                                return val > 0 ? val + '%' : null;
                            }
                        }
                    },
                    {
                        label: 'TOTAL (Open)',
                        data: openData,
                        backgroundColor: '#FFC000',
                        datalabels: {
                            display: false
                        }
                    }
                ]
            },
            options: { 
                ...commonOptions, 
                scales: { 
                    x: { 
                        stacked: true, 
                        grid: { display: false },
                        ticks: {
                            font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b'
                        }
                    }, 
                    y: { 
                        stacked: true, 
                        max: 100, 
                        ticks: { 
                            callback: v => v + '%',
                            font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b'
                        } 
                    } 
                },
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                            color: '#1e293b',
                            padding: 16,
                            boxWidth: 16,
                            boxHeight: 16
                        }
                    },
                    tooltip: {
                        titleFont: {
                            size: 15,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        bodyFont: {
                            size: 14,
                            weight: 'bold',
                            family: "'Inter', sans-serif"
                        },
                        padding: 12,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw;
                                return ` ${ctx.dataset.label}: ${val}%`;
                            }
                        }
                    }
                }
            }
        });

        // 6. Donut Chart - Rekap % Temuan Ber-SAP Berdasarkan Asset Owner
        const chart6Values = getValues(charts.tindak_lanjut || {});
        const chart6Total  = chart6Values.reduce((a, b) => a + b, 0);
        const chart6El     = document.getElementById('chart6');
        const chart6EmptyEl= document.getElementById('chart6_empty');

        // Warna teks kontras per warna irisan (gelap untuk warna terang, putih untuk warna gelap)
        const chart6TextColor = (bgColor) => {
            const lightBg = ['#FFC000', '#CBD5E1', '#9CA3AF'];
            return lightBg.includes(bgColor) ? '#1e293b' : '#ffffff';
        };

        if (chart6Total === 0) {
            if (chart6El) chart6El.classList.add('hidden');
            if (chart6EmptyEl) chart6EmptyEl.classList.remove('hidden');
            if (window.chartInstances['chart6']) {
                window.chartInstances['chart6'].destroy();
                delete window.chartInstances['chart6'];
            }
        } else {
            if (chart6El) chart6El.classList.remove('hidden');
            if (chart6EmptyEl) chart6EmptyEl.classList.add('hidden');
            const chart6Labels = getLabels(charts.tindak_lanjut);
            const chart6BgColors = getBgColors(chart6Labels);
            getOrCreateChart('chart6', {
                type: 'doughnut',
                plugins: [ChartDataLabels],
                data: { 
                    labels: chart6Labels, 
                    datasets: [{ 
                        data: chart6Values, 
                        backgroundColor: chart6BgColors
                    }] 
                },
                options: { 
                    ...commonOptions, 
                    plugins: { 
                        legend: { 
                            position: 'right',
                            labels: {
                                font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b',
                                padding: 16,
                                boxWidth: 16,
                                boxHeight: 16
                            }
                        },
                        tooltip: {
                            titleFont: {
                                size: 15,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            bodyFont: {
                                size: 14,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            padding: 12,
                            callbacks: {
                                label: function(ctx) {
                                    return ` ${ctx.label}: ${ctx.raw}%`;
                                }
                            }
                        },
                        datalabels: {
                            color: function(context) {
                                return chart6TextColor(chart6BgColors[context.dataIndex] || '#9CA3AF');
                            },
                            font: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                            formatter: function(value) {
                                return value >= 5 ? value + '%' : '';
                            },
                            anchor: 'center',
                            align: 'center',
                            display: function(context) {
                                return context.dataset.data[context.dataIndex] >= 5;
                            }
                        }
                    } 
                }
            });
        }

        // Helper for Horizontal Bar charts with Data Labels (Percentages & Counts)
        // Urutan kategori mengikuti backend (PHP array order) — TIDAK di-sort by value.
        const renderHorizontalBar = (ctxId, chartData, color) => {
            if (!chartData || !chartData.data) return;
            // Pertahankan urutan dari backend (PHP array order)
            let dataArr = Object.entries(chartData.data)
                .map(([k, v]) => ({ label: k, value: v }));
            // CATATAN: tidak ada .sort() — urutan ditentukan oleh backend service
            
            const total = chartData.total || 1; // Prevent division by zero
            
            getOrCreateChart(ctxId, {
                type: 'bar',
                plugins: [ChartDataLabels],
                data: { 
                    labels: dataArr.map(d => d.label), 
                    datasets: [{ 
                        data: dataArr.map(d => ((d.value / total) * 100).toFixed(1)), 
                        backgroundColor: color,
                        borderWidth: 0,
                        borderRadius: 4,
                        barThickness: 18,  // fixed agar jarak antar label seragam
                        minBarLength: 6    // bar dengan nilai > 0 selalu terlihat & mudah di-hover
                    }] 
                },
                options: { 
                    ...commonOptions, 
                    indexAxis: 'y', 
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            right: 70 // ruang agar data label tidak terpotong di tepi kanan
                        }
                    },
                    interaction: {
                        mode: 'index',
                        axis: 'y',
                        intersect: false // hover di mana saja sepanjang baris akan memunculkan tooltip
                    },
                    plugins: { 
                        legend: { display: false },
                        datalabels: {
                            anchor: 'end',
                            align: 'right',
                            color: '#1e293b',
                            font: {
                                weight: 'bold',
                                size: 12,
                                family: "'Inter', sans-serif"
                            },
                            formatter: function(value, context) {
                                let rawCount = dataArr[context.dataIndex].value;
                                return `${value}% (${rawCount})`;
                            }
                        },
                        tooltip: {
                            titleFont: {
                                size: 15,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            bodyFont: {
                                size: 14,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    let val = context.raw;
                                    let rawCount = dataArr[context.dataIndex].value;
                                    return ` ${val}% (${rawCount} temuan)`;
                                }
                            }
                        }
                    }, 
                    scales: { 
                        x: { 
                            beginAtZero: true, 
                            max: 100,
                            grid: { display: true, color: '#f1f5f9' },
                            ticks: {
                                font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b',
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }, 
                        y: { 
                            grid: { display: false },
                            afterFit: (scale) => { scale.width = 480; }, // ruang cukup agar seluruh label bilingual panjang tidak terpotong
                            ticks: {
                                autoSkip: false,
                                font: { size: 12, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b'
                            }
                        } 
                    }
                }
            });
        };

        // 7. Horizontal Bar - Unsafe Action
        renderHorizontalBar('chart7', charts.unsafe_action, '#ED7D31');

        // 8. Horizontal Bar - Unsafe Condition
        renderHorizontalBar('chart8', charts.unsafe_condition, '#ED7D31');
    };

    // Render initial charts on page load
    window.renderAllCharts(initialCharts);

    // ================================================================
    // AJAX FILTER HANDLING
    // ================================================================
    let activeAbortController = null;
    let currentRequestId = 0;

    function showDashboardLoading(show) {
        const el = document.getElementById('dashboard-loading-indicator');
        if (el) {
            if (show) el.classList.remove('hidden');
            else el.classList.add('hidden');
        }
    }

    function showDashboardError(msg) {
        const toast = document.getElementById('dashboard-error-toast');
        const msgEl = document.getElementById('dashboard-error-msg');
        if (toast && msgEl) {
            msgEl.textContent = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }
    }

    function updateKpi(kpi, selectedYear) {
        if (!kpi) return;
        const totalEl = document.getElementById('kpi-total');
        const openEl = document.getElementById('kpi-open');
        const closedEl = document.getElementById('kpi-closed');
        const badgeEl = document.getElementById('kpi-badge');
        if (totalEl) totalEl.textContent = Number(kpi.total || 0).toLocaleString('id-ID');
        if (openEl) openEl.textContent = Number(kpi.open || 0).toLocaleString('id-ID');
        if (closedEl) closedEl.textContent = Number(kpi.closed || 0).toLocaleString('id-ID');
        if (badgeEl && selectedYear !== undefined) {
            badgeEl.textContent = selectedYear ? ('Tahun ' + selectedYear) : 'Keseluruhan Data';
        }
    }

    function fetchDashboard(params = {}) {
        if (activeAbortController) {
            activeAbortController.abort();
        }
        activeAbortController = new AbortController();
        const reqId = ++currentRequestId;
        showDashboardLoading(true);

        const currentUrl = new URL(window.location.href);
        Object.keys(params).forEach(k => {
            if (params[k] === null || params[k] === '') {
                currentUrl.searchParams.delete(k);
            } else {
                currentUrl.searchParams.set(k, params[k]);
            }
        });

        const fetchUrl = currentUrl.pathname + currentUrl.search;

        fetch(fetchUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: activeAbortController.signal
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            if (reqId !== currentRequestId) return;
            showDashboardLoading(false);
            if (!data.success) throw new Error(data.message || 'Gagal memuat data');

            updateKpi(data.kpi, data.selectedYear);

            if (data.selectedYear !== undefined && filterYear && String(data.selectedYear) !== filterYear.value) {
                filterYear.value = data.selectedYear || '';
            }

            if (data.koYear !== undefined && koYearFilter && String(data.koYear) !== koYearFilter.value) {
                koYearFilter.value = data.koYear || '';
            }
            if (data.koMonth !== undefined && koMonthFilter && String(data.koMonth) !== koMonthFilter.value) {
                koMonthFilter.value = data.koMonth || '';
            }
            if (data.rrYear !== undefined && rrYearFilter && String(data.rrYear) !== rrYearFilter.value) {
                rrYearFilter.value = data.rrYear || '';
            }
            if (data.rrMonth !== undefined && rrMonthFilter && String(data.rrMonth) !== rrMonthFilter.value) {
                rrMonthFilter.value = data.rrMonth || '';
            }
            if (data.selectedPekaYear !== undefined && pekaYearFilter && String(data.selectedPekaYear) !== pekaYearFilter.value) {
                pekaYearFilter.value = data.selectedPekaYear || '';
            }

            // Update Chart 2 Title & Subtitle
            if (data.charts && data.charts.reporting) {
                const rTitle = document.getElementById('chart2-title');
                const rSub   = document.getElementById('chart2-subtitle');
                const rMode  = data.charts.reporting.mode || 'distribusi';
                if (rTitle) {
                    if (rMode === 'manpower_rasio') {
                        const rBulanNama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                        const rMonthName = data.rrMonth ? (rBulanNama[data.rrMonth] || '') : (data.selectedMonth ? (rBulanNama[data.selectedMonth] || '') : '');
                        const rPeriod = rMonthName ? (rMonthName + ' ' + (data.rrYear || data.selectedYear || '')) : (data.charts.reporting.periode_label || '');
                        rTitle.textContent = '2. Reporting Rate per Fungsi (' + rPeriod + ')';
                    } else {
                        rTitle.textContent = '2. Reporting Rate per Fungsi';
                    }
                }
                if (rSub) {
                    rSub.textContent = (rMode === 'manpower_rasio')
                        ? 'temuan YTD ÷ (manpower × bulan)'
                        : 'jumlah pelaporan ÷ manpower tahunan';
                }
            }

            window.renderAllCharts(data.charts, data);

            window.history.replaceState({}, '', fetchUrl);
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            showDashboardLoading(false);
            console.error('AJAX dashboard filter error:', err);
            showDashboardError('Gagal memuat data filter (' + (err.message || 'Error') + '). Data sebelumnya tetap dipertahankan.');
        });
    }

    // Attach event listeners
    const filterYear = document.getElementById('filter-year');
    if (filterYear) {
        filterYear.addEventListener('change', function(e) {
            e.preventDefault();
            fetchDashboard({ year: this.value });
        });
    }

    const rrYearFilter = document.getElementById('rr-year-filter');
    if (rrYearFilter) {
        rrYearFilter.onchange = null;
        rrYearFilter.addEventListener('change', function(e) {
            e.preventDefault();
            fetchDashboard({ rr_year: this.value });
        });
    }

    const rrMonthFilter = document.getElementById('rr-month-filter');
    if (rrMonthFilter) {
        rrMonthFilter.onchange = null;
        rrMonthFilter.addEventListener('change', function(e) {
            e.preventDefault();
            fetchDashboard({ rr_month: this.value });
        });
    }

    const pekaYearFilter = document.getElementById('peka-year-filter');
    if (pekaYearFilter) {
        pekaYearFilter.onchange = null;
        pekaYearFilter.addEventListener('change', function(e) {
            e.preventDefault();
            fetchDashboard({ peka_year: this.value });
        });
    }

    const koYearFilter = document.getElementById('ko-year-filter');
    if (koYearFilter) {
        koYearFilter.onchange = null;
        koYearFilter.addEventListener('change', function(e) {
            e.preventDefault();
            fetchDashboard({ ko_year: this.value });
        });
    }

    const koMonthFilter = document.getElementById('ko-month-filter');
    if (koMonthFilter) {
        koMonthFilter.onchange = null;
        koMonthFilter.addEventListener('change', function(e) {
            e.preventDefault();
            fetchDashboard({ ko_month: this.value });
        });
    }
});
</script>
@endpush