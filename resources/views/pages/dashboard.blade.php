@extends('layouts.app')

@section('title', 'Overview')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Dashboard Global HSSE</h1>
            <p class="text-sm text-slate-500 mt-1">Monitoring dan analitik data temuan PEKA secara real-time.</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
            @if(auth()->user()->role === 'Admin HSSE')
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

    {{-- ================================================================
         $bulanNamaList & $tahunList — dipakai oleh filter in-card
    ================================================================ --}}
    @php
        $bulanNamaList = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
        ];
        $tahunList = range(now()->year + 1, 2020);
    @endphp

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Card 1: Total Temuan -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Total Temuan</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['total'] ?? 0) }}</h3>
                <div class="mt-2 text-xs font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full w-fit">Keseluruhan Data</div>            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
        </div>

        <!-- Card 2: Status Open -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Open</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['open'] ?? 0) }}</h3>
                <div class="mt-2 text-xs font-medium text-red-600 bg-red-50 px-2.5 py-1 rounded-full w-fit">Butuh Tindak Lanjut</div>            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 3: Status Closed -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Closed</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['closed'] ?? 0) }}</h3>
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
                <h3 class="text-lg font-semibold text-slate-800">1. Jumlah Pelaporan per Fungsi</h3>
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

                // Teks periode: jika bulan dipilih → tampilkan nama bulan saja
                // jika hanya tahun → tampilkan label dari backend
                // jika tidak ada filter → tidak ada label periode
                if ($rrMode === 'manpower_rasio') {
                    if ($selectedMonth ?? null) {
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
                    <h3 class="text-base font-bold text-slate-800">{{ $rrLabel }}</h3>
                    @if($rrMode === 'manpower_rasio')
                        <span class="text-xs font-mono text-slate-400">temuan YTD ÷ (manpower × bulan)</span>
                    @endif
                </div>
                {{-- Filter Tahun & Bulan — khusus untuk card ini --}}
                <form action="" method="GET" class="flex flex-wrap items-center gap-2">
                    <select name="year" id="rr-year-filter"
                        onchange="document.getElementById('rr-month-filter').value=''; this.form.submit()"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        <option value="">Semua Tahun</option>
                        @foreach($tahunList as $yr)
                            <option value="{{ $yr }}" {{ ($selectedYear ?? null) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                    <select name="month" id="rr-month-filter"
                        onchange="this.form.submit()"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm {{ !($selectedYear ?? null) ? 'opacity-50' : '' }}"
                        {{ !($selectedYear ?? null) ? 'disabled' : '' }}>
                        <option value="">Semua Bulan</option>
                        @foreach($bulanNamaList as $num => $nama)
                            <option value="{{ $num }}" {{ ($selectedMonth ?? null) == $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                    @if(($selectedYear ?? null) || ($selectedMonth ?? null))
                        <a href="{{ route('dashboard') }}" class="text-xs text-slate-400 hover:text-slate-600 underline whitespace-nowrap">Reset</a>
                    @endif
                </form>
            </div>

            @if($rrMode === 'manpower_rasio' && $hasNull)
                <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-1.5 mb-2">
                    ⚠️ Beberapa fungsi tidak memiliki data manpower untuk periode ini. Reporting Rate hanya ditampilkan untuk fungsi yang memiliki data manpower.
                </p>
            @endif
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart2"></canvas>
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
                    <form action="" method="GET" class="flex items-center gap-2">
                        {{-- Pertahankan filter chart lain yang sudah aktif --}}
                        @if($selectedYear)
                            <input type="hidden" name="year" value="{{ $selectedYear }}">
                        @endif
                        @if($selectedMonth)
                            <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        @endif
                        <select name="peka_year" id="peka-year-filter"
                            onchange="this.form.submit()"
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
                    <span class="text-[15px] font-medium text-[#475569]">Safe Action</span>
                </div>
                <!-- Safe Condition -->
                <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity" onclick="const c = document.getElementById('chartTrending').chartInstance; c.setDatasetVisibility(1, !c.isDatasetVisible(1)); c.update();">
                    <span class="w-4 h-4 rounded-full bg-[#fde047]"></span>
                    <span class="text-[15px] font-medium text-[#475569]">Safe Condition</span>
                </div>
                <!-- Unsafe Action -->
                <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity" onclick="const c = document.getElementById('chartTrending').chartInstance; c.setDatasetVisibility(2, !c.isDatasetVisible(2)); c.update();">
                    <span class="w-4 h-4 rounded-full bg-[#fdba74]"></span>
                    <span class="text-[15px] font-medium text-[#475569]">Unsafe Action</span>
                </div>
                <!-- Unsafe Condition -->
                <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity" onclick="const c = document.getElementById('chartTrending').chartInstance; c.setDatasetVisibility(3, !c.isDatasetVisible(3)); c.update();">
                    <span class="w-4 h-4 rounded-full bg-[#6ee7b7]"></span>
                    <span class="text-[15px] font-medium text-[#475569]">Unsafe Condition</span>
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
                    <span class="text-[15px] font-medium text-[#475569]">Total</span>
                </div>
            </div>

            {{-- Summary Text --}}
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                <p id="trending-summary" class="text-xs text-slate-500 leading-relaxed">
                    {{-- diisi via JavaScript --}}
                </p>
            </div>

        </div>{{-- end card --}}


        <!-- Chart 3: Kategori PEKA (Pie) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="min-height:400px">
            <h3 class="text-base font-bold text-slate-800 mb-4">3. Kategori PEKA</h3>
            <div class="flex-1 relative w-full" style="min-height:300px">
                <canvas id="chart3"></canvas>
            </div>
            <!-- Statistik Positif / Negatif -->
            <div id="chart3-stats" class="mt-4 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 space-y-1 hidden">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>
                    <span>Jumlah laporan positif <strong id="chart3-positif">-</strong></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>
                    <span>Jumlah laporan negatif <strong id="chart3-negatif">-</strong></span>
                </div>
            </div>
        </div>

        <!-- Chart 4: Keterlibatan Observasi (Stacked Vertical Bar) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            {{-- Header: Judul + Filter in-card --}}
            <div class="flex flex-wrap items-start justify-between gap-3 mb-1">
                <div>
                    <h3 class="text-base font-bold text-slate-800">4. Keterlibatan dalam Observasi</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Rumus: Pelapor unik Jan s/d bulan terpilih &divide; Manpower bulan terpilih &times; 100%
                    </p>
                </div>
                {{-- Filter Tahun & Bulan — khusus untuk card ini --}}
                <form action="" method="GET" class="flex flex-wrap items-center gap-2">
                    <select name="year" id="ko-year-filter"
                        onchange="document.getElementById('ko-month-filter').value=''; this.form.submit()"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        <option value="">Semua Tahun</option>
                        @foreach($tahunList as $yr)
                            <option value="{{ $yr }}" {{ ($selectedYear ?? null) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                    <select name="month" id="ko-month-filter"
                        onchange="this.form.submit()"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm {{ !($selectedYear ?? null) ? 'opacity-50' : '' }}"
                        {{ !($selectedYear ?? null) ? 'disabled' : '' }}>
                        <option value="">Semua Bulan</option>
                        @foreach($bulanNamaList as $num => $nama)
                            <option value="{{ $num }}" {{ ($selectedMonth ?? null) == $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                    @if(($selectedYear ?? null) || ($selectedMonth ?? null))
                        <a href="{{ route('dashboard') }}" class="text-xs text-slate-400 hover:text-slate-600 underline whitespace-nowrap">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Notice: belum pilih bulan (Blade — ditampilkan server-side) --}}
            @if(!($selectedMonth ?? null))
                <div class="mb-3 text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                    ℹ️ Pilih <strong>Bulan</strong> pada filter di atas untuk menampilkan persentase keterlibatan.
                </div>
            @endif

            {{-- Notice JS: muncul jika manpower tidak tersedia (diisi via JS) --}}
            <div id="chart4-notice" class="hidden mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2"></div>

            <div class="flex-1 relative w-full h-full">
                <canvas id="chart4"></canvas>
            </div>
        </div>

        <!-- Chart 5: Persentase Temuan per Fungsi (Stacked Vertical Bar) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-base font-bold text-slate-800 mb-4">5. Rekap Persentase Temuan per Fungsi</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart5"></canvas>
            </div>
        </div>

        <!-- Chart 6: Persentase Penindak Lanjut (Donut) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-base font-bold text-slate-800 mb-4">6. Rekap Persentase Penindak Lanjut (By SAP)</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart6"></canvas>
            </div>
        </div>

        <!-- Chart 7: Unsafe Action (Horizontal Bar) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[500px]">
            <h3 class="text-base font-bold text-slate-800 mb-4">7. Unsafe Action Category</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart7"></canvas>
            </div>
        </div>

        <!-- Chart 8: Unsafe Condition (Horizontal Bar) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[500px]">
            <h3 class="text-base font-bold text-slate-800 mb-4">8. Unsafe Condition Category</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart8"></canvas>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const charts = @json($charts ?? []);
        if(Object.keys(charts).length === 0) return;

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
        const getLabels = (obj) => Object.keys(obj);
        const getValues = (obj) => Object.values(obj);
        const getBgColors = (keys) => keys.map(k => colors[k] || '#9CA3AF');

        // 1. Pie Chart - Jumlah Pelaporan per Fungsi
        const chart1Labels = getLabels(charts.fungsi);
        const chart1Values = getValues(charts.fungsi);
        const chart1Total  = chart1Values.reduce((a, b) => a + b, 0);

        new Chart(document.getElementById('chart1'), {
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
                    legend: { position: 'bottom' },
                    datalabels: {
                        color: '#333',
                        font: {
                            weight: 'normal',
                            size: 11
                        },
                        formatter: (value) => {
                            if (value === 0 || chart1Total === 0) return null;
                            const pct = ((value / chart1Total) * 100).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            return pct + '%';
                        }
                    },
                    tooltip: {
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
        const rrAllData = charts.reporting.data || {};
        const rrMode    = charts.reporting.mode || 'distribusi';

        // Tampilkan semua label (termasuk AREA LHD di paling atas)
        // null = manpower belum tersedia, skip dari chart
        const rrLabels = Object.keys(rrAllData).filter(k => rrAllData[k] !== null);
        const rrValues = rrLabels.map(k => rrAllData[k]);
        
        const rrColorMap = {
            'AREA LHD': '#002060',
            'Operation': '#5B9BD5',
            'Maintenance': '#ED7D31',
            'HSSE': '#A5A5A5',
            'Business Support': '#FFC000'
        };
        const rrBgColors = rrLabels.map(l => rrColorMap[l] || '#9CA3AF');

        if (rrLabels.length > 0) {
            new Chart(document.getElementById('chart2'), {
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
                        x: { beginAtZero: true, grid: { display: false } },
                        y: { grid: { display: false } }
                    },
                    animation: {
                        onComplete: function() {
                            var chartInstance = this;
                            var ctx = chartInstance.ctx;
                            ctx.font = Chart.helpers.fontString(12, 'normal', Chart.defaults.font.family);
                            ctx.textAlign = 'left';
                            ctx.textBaseline = 'middle';
                            ctx.fillStyle = '#333';

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
                const chartInstance = new Chart(trendCanvas, {
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
                                ticks: { font: { size: 13, family: "'Inter', sans-serif" }, color: '#64748b' },
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
                                    font: { size: 12, family: "'Inter', sans-serif" },
                                    color: '#64748b',
                                    padding: 10
                                },
                                title: {
                                    display: true,
                                    text: 'Temuan',
                                    font: { size: 14, family: "'Inter', sans-serif", weight: 'bold' },
                                    color: '#64748b',
                                    padding: { bottom: 15 }
                                }
                            },
                        },
                        plugins: {
                            legend: {
                                display: false, // Disembunyikan, menggunakan custom HTML legend
                            },
                            tooltip: {
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

        new Chart(document.getElementById('chart3'), {
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
                    legend: { position: 'right' },
                    datalabels: {
                        color: '#333',
                        font: {
                            weight: 'normal',
                            size: 11
                        },
                        formatter: (value) => {
                            if (value === 0 || chart3Total === 0) return null;
                            const pct = Math.round((value / chart3Total) * 100);
                            return pct + '%';
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.raw;
                                const pct = chart3Total > 0 ? Math.round((val / chart3Total) * 100) : 0;
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
                document.getElementById('chart3-positif').textContent = fmt(positifTotal) + ' %';
                document.getElementById('chart3-negatif').textContent  = fmt(negatifTotal)  + ' %';
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
            if (notice4) {
                if (allNull) {
                    // Semua null: bulan belum dipilih atau manpower belum ada
                    // (pesan "pilih bulan" ditampilkan oleh Blade jika bulan = null;
                    //  JS hanya menampilkan pesan manpower jika bulan sudah dipilih)
                    const selectedMonth = {{ $selectedMonth ?? 'null' }};
                    if (selectedMonth) {
                        // Bulan sudah dipilih tapi manpower belum ada
                        notice4.innerHTML = '⚠️ Data manpower belum tersedia untuk periode ini. Pastikan data manpower sudah diinput di menu <strong>Master Manpower</strong>.';
                        notice4.classList.remove('hidden');
                    }
                    // Jika bulan null, Blade sudah menampilkan notice "Pilih Bulan"
                } else if (someNull) {
                    // Sebagian fungsi tidak ada manpower
                    const missingFungsi = invLabels.filter(k => invRaw[k] === null).join(', ');
                    notice4.innerHTML = `⚠️ Manpower belum tersedia untuk: <strong>${missingFungsi}</strong>. Fungsi lain ditampilkan normal.`;
                    notice4.classList.remove('hidden');
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
            new Chart(document.getElementById('chart4'), {
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
                                color: '#fff',
                                anchor: 'center',
                                align: 'center',
                                font: { weight: 'bold', size: 12 },
                                formatter: (value, ctx) => {
                                    const key = invLabels[ctx.dataIndex];
                                    if (invRaw[key] === null) return null; // fungsi null → jangan label
                                    if (value === 0) return null;
                                    return invRaw[key].toLocaleString('id-ID', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2,
                                    }) + '%';
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
                                font: { size: 11, style: 'italic' },
                                formatter: () => 'N/A',
                            }
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: {
                            stacked: true,
                            max: 100,
                            ticks: { callback: v => v + '%' }
                        }
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: { display: false }, // default off; per-dataset override di atas
                        tooltip: {
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
        
        new Chart(document.getElementById('chart5'), {
            type: 'bar',
            data: {
                labels: ptLabels,
                datasets: [
                    { label: 'Closed', data: closedData, backgroundColor: '#548235' },
                    { label: 'TOTAL (Open)', data: openData, backgroundColor: '#FFC000' }
                ]
            },
            options: { 
                ...commonOptions, 
                scales: { 
                    x: { stacked: true, grid: { display: false } }, 
                    y: { stacked: true, max: 100, ticks: { callback: v => v + '%' } } 
                },
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // 6. Donut Chart - Rekap % Penindak Lanjut Temuan (SAP)
        new Chart(document.getElementById('chart6'), {
            type: 'doughnut',
            data: { 
                labels: getLabels(charts.tindak_lanjut), 
                datasets: [{ 
                    data: getValues(charts.tindak_lanjut), 
                    backgroundColor: getBgColors(getLabels(charts.tindak_lanjut)) 
                }] 
            },
            options: { ...commonOptions, plugins: { legend: { position: 'right' } } }
        });

        // Helper for Horizontal Bar charts with Data Labels (Percentages)
        const renderHorizontalBar = (ctxId, chartData, color) => {
            // Sort from highest to lowest value
            let dataArr = Object.entries(chartData.data)
                .map(([k, v]) => ({ label: k, value: v }))
                .sort((a, b) => b.value - a.value);
            
            const total = chartData.total || 1; // Prevent division by zero
            
            new Chart(document.getElementById(ctxId), {
                type: 'bar',
                data: { 
                    labels: dataArr.map(d => d.label), 
                    datasets: [{ 
                        data: dataArr.map(d => ((d.value / total) * 100).toFixed(1)), 
                        backgroundColor: color,
                        borderWidth: 0,
                        borderRadius: 4
                    }] 
                },
                options: { 
                    ...commonOptions, 
                    indexAxis: 'y', 
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: {
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
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }, 
                        y: { 
                            grid: { display: false },
                            ticks: {
                                autoSkip: false,
                                font: { size: 11 }
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
    });
</script>
@endpush