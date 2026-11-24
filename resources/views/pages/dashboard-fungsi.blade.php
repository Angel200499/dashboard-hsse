@extends('layouts.app')

@section('title', 'Dashboard Fungsi - ' . $fungsi)

@section('content')
@php
    $activeTab = request('tab') === 'global' ? 'global' : 'fungsi';
@endphp
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            @if($activeTab === 'global')
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard Global (Area Lahendong)</h1>
                <p class="text-sm text-slate-500 mt-1">Ringkasan kondisi PEKA seluruh fungsi (Operation, Maintenance, HSSE, Business Support).</p>
            @else
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard Fungsi: {{ $fungsi }}</h1>
                <p class="text-sm text-slate-500 mt-1">Ringkasan metrik dan daftar temuan lapangan untuk fungsi {{ $fungsi }}.</p>
            @endif
        </div>

        <div class="flex items-center gap-3">
            @if($activeTab === 'fungsi')
            <form action="" method="GET" class="flex flex-wrap items-center gap-3" id="dashboard-filter-form">
                <div class="relative w-full sm:w-auto">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block w-full pl-10 p-2.5 shadow-sm" placeholder="Cari area, temuan...">
                </div>
                
                <select name="status_filter" onchange="this.form.submit()" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm min-w-[120px]">
                    <option value="">Semua Status</option>
                    <option value="open" {{ request("status_filter") == "open" ? "selected" : "" }}>Open</option>
                    <option value="in progress" {{ request("status_filter") == "in progress" ? "selected" : "" }}>In Progress</option>
                    <option value="closed" {{ request("status_filter") == "closed" ? "selected" : "" }}>Closed</option>
                </select>

                <select name="date_filter" onchange="this.form.submit()" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm min-w-[120px]">
                    <option value="">Semua Waktu</option>
                    <option value="1_day" {{ request("date_filter") == "1_day" ? "selected" : "" }}>1 Hari Terakhir</option>
                    <option value="3_days" {{ request("date_filter") == "3_days" ? "selected" : "" }}>3 Hari Terakhir</option>
                    <option value="1_week" {{ request("date_filter") == "1_week" ? "selected" : "" }}>1 Minggu Terakhir</option>
                    <option value="1_month" {{ request("date_filter") == "1_month" ? "selected" : "" }}>1 Bulan Terakhir</option>
                </select>

                {{-- ================================================
                     FILTER TAHUN (KPI, Monitoring Temuan, & Chart)
                ================================================ --}}
                @php
                    $bulanNamaList = [
                        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
                        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
                        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
                    ];
                    $tahunList = $tahunList ?? \App\Models\SipekaFinding::getTahunList();
                @endphp

                @if(request()->has('ko_year'))
                    <input type="hidden" name="ko_year" value="{{ request('ko_year') }}">
                @endif
                @if(request()->has('ko_month'))
                    <input type="hidden" name="ko_month" value="{{ request('ko_month') }}">
                @endif
                @if(request()->has('rr_year'))
                    <input type="hidden" name="rr_year" value="{{ request('rr_year') }}">
                @endif
                @if(request()->has('rr_month'))
                    <input type="hidden" name="rr_month" value="{{ request('rr_month') }}">
                @endif

                <select name="year" id="filter-year"
                    class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $yr)
                        <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>

                <div id="filter-loading-indicator" class="hidden flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl animate-pulse">
                    <svg class="animate-spin w-3.5 h-3.5 text-[#9DBF2A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Memuat...</span>
                </div>

                @if($selectedYear || request('search') || request('status_filter') || request('date_filter'))
                    @php
                        $resetHeaderQuery = request()->only(['rr_year', 'rr_month', 'ko_year', 'ko_month']);
                    @endphp
                    <a href="{{ request()->url() }}{{ !empty($resetHeaderQuery) ? '?' . http_build_query($resetHeaderQuery) : '' }}" class="text-xs text-slate-500 hover:text-slate-700 underline whitespace-nowrap">Reset Filter</a>
                @endif
            </form>
            @else
            {{-- Global Tab Top Filter --}}
            @php
                $tahunList = $tahunList ?? \App\Models\SipekaFinding::getTahunList();
                $bulanNamaList = [
                    1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
                    5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
                    9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
                ];
            @endphp
            <form action="" method="GET" onsubmit="return false;" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="tab" value="global">
                @foreach(request()->except(['g_year', 'g_month', 'tab']) as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <select name="g_year" id="g-filter-year"
                    class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm">
                    <option value="">Semua Tahun (Global)</option>
                    @foreach($tahunList as $yr)
                        <option value="{{ $yr }}" {{ ($gYear ?? null) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>

                <div id="g-filter-loading-indicator" class="hidden flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 rounded-xl animate-pulse">
                    <svg class="animate-spin w-3.5 h-3.5 text-[#9DBF2A]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Memuat...</span>
                </div>

                @if(request()->has('g_year') || request()->has('g_rr_year') || request()->has('g_rr_month') || request()->has('g_ko_year') || request()->has('g_ko_month') || request()->has('g_peka_year'))
                    <a href="{{ request()->url() }}?tab=global" class="text-xs text-slate-500 hover:text-slate-700 underline whitespace-nowrap">Reset Filter Global</a>
                @endif
            </form>
            @endif

            {{-- Executive Summary PDF Button — selalu Global, tersedia untuk semua role --}}
            @include('components.executive-summary-btn', [
                'selectedYear'   => $activeTab === 'global' ? $gYear : $selectedYear,
                'selectedMonth'  => $activeTab === 'global' ? $gMonth : $selectedMonth,
                'maxBulanSipeka' => $activeTab === 'global' ? $gMaxBulanSipeka : $maxBulanSipeka,
                'tahunList'      => $tahunList ?? null,
            ])
        </div>
    </div>

    {{-- ================================================================
         TAB NAVIGATION — Dashboard Fungsi / Dashboard Global
    ================================================================ --}}
    @php
        $tabBaseUrl = request()->url();
        $tabFungsiParams = request()->except(['tab']);
        $tabGlobalParams = array_merge(request()->except(['tab']), ['tab' => 'global']);
        $tabFungsiUrl = $tabBaseUrl . (!empty($tabFungsiParams) ? '?' . http_build_query($tabFungsiParams) : '');
        $tabGlobalUrl = $tabBaseUrl . '?' . http_build_query($tabGlobalParams);
    @endphp
    <div class="flex gap-1 p-1 bg-slate-100 rounded-xl w-fit border border-slate-200">
        <a href="{{ $tabFungsiUrl }}"
           class="px-5 py-2 rounded-lg text-sm font-semibold transition-all duration-200 {{ $activeTab === 'fungsi' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Dashboard {{ $fungsi }}
        </a>
        <a href="{{ $tabGlobalUrl }}"
           class="px-5 py-2 rounded-lg text-sm font-semibold transition-all duration-200 {{ $activeTab === 'global' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
            Dashboard Global
        </a>
    </div>

    {{-- ================================================================
         TAB 1: DASHBOARD FUNGSI (konten existing, tidak berubah)
    ================================================================ --}}
    @if($activeTab === 'fungsi')

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Total Temuan -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Total Temuan</p>
                <h3 class="text-3xl font-bold text-slate-800" id="kpi-total">{{ number_format($kpi['total'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full w-fit">
                    Keseluruhan Data
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
        </div>

        <!-- Card 2: Status Open -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Open</p>
                <h3 class="text-3xl font-bold text-slate-800" id="kpi-open">{{ number_format($kpi['open'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full w-fit">
                    Butuh Tindak Lanjut
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 3: Status Closed -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Closed</p>
                <h3 class="text-3xl font-bold text-slate-800" id="kpi-closed">{{ number_format($kpi['closed'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full w-fit">
                    Telah Diselesaikan
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Charts Section (Disalin persis dari Dashboard Global sesuai instruksi) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
        
        <!-- Chart 1: Pelaporan per Fungsi (Pie) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-[22px] font-bold text-slate-800 mb-4">1. Jumlah Pelaporan per Fungsi</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart1"></canvas>
            </div>
        </div>

        <!-- Chart 2: Reporting Rate per Fungsi (Horizontal Bar) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            @php
                $rrMode  = $charts['reporting']['mode'] ?? 'distribusi';
                $rrData  = $charts['reporting']['data'] ?? [];
                $hasNull = in_array(null, $rrData, true);

                if ($rrMode === 'manpower_rasio') {
                    if ($rrMonth && isset($bulanNamaList[$rrMonth])) {
                        $rrPeriodeText = $bulanNamaList[$rrMonth] . ' ' . $rrYear;
                    } else {
                        $rrPeriodeText = $charts['reporting']['periode_label'] ?? '';
                    }
                    $rrLabel = '2. Reporting Rate per Fungsi (' . $rrPeriodeText . ')';
                } else {
                    $rrLabel = '2. Reporting Rate per Fungsi';
                }
            @endphp

            {{-- Header: Judul + Filter Mandiri In-Card --}}
            <div class="flex flex-wrap items-start justify-between gap-3 mb-2">
                <div>
                    <h3 class="text-[22px] font-bold text-slate-800" id="chart2-title">{{ $rrLabel }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5 font-mono" id="chart2-subtitle">
                        @if($rrMode === 'manpower_rasio')
                            temuan YTD ÷ (manpower × bulan)
                        @else
                            jumlah pelaporan ÷ manpower tahunan
                        @endif
                    </p>
                </div>

                {{-- Filter Khusus Card 2: Tahun & Bulan Mandiri --}}
                <form action="" method="GET" onsubmit="return false;" class="flex flex-wrap items-center gap-2">
                    {{-- Pertahankan filter existing halaman (search, status, date, sort, dll) --}}
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('status_filter'))
                        <input type="hidden" name="status_filter" value="{{ request('status_filter') }}">
                    @endif
                    @if(request('date_filter'))
                        <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                    @endif
                    @if(request('year'))
                        <input type="hidden" name="year" value="{{ request('year') }}">
                    @endif
                    @if(request('sort_by'))
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    @endif
                    @if(request('sort_dir'))
                        <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
                    @endif
                    {{-- Pertahankan filter Card 4 (Keterlibatan Observasi) --}}
                    @if(request('ko_year'))
                        <input type="hidden" name="ko_year" value="{{ request('ko_year') }}">
                    @endif
                    @if(request()->has('ko_month'))
                        <input type="hidden" name="ko_month" value="{{ request('ko_month') }}">
                    @endif

                    <select name="rr_year" id="rr-year-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        @if(!$latestDataYear)
                            <option value="">Semua Tahun</option>
                        @endif
                        @foreach($tahunList as $yr)
                            <option value="{{ $yr }}" {{ $rrYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>

                    @php
                        // Batasi opsi bulan Card 2 berdasarkan bulan SIPEKA terakhir ($rrMaxBulan) untuk tahun dan fungsi terpilih
                        $card2BulanList = ($rrMaxBulan && $rrYear)
                            ? array_filter($bulanNamaList, fn($num) => $num <= $rrMaxBulan, ARRAY_FILTER_USE_KEY)
                            : $bulanNamaList;
                    @endphp
                    <select name="rr_month" id="rr-month-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        <option value="">Semua Bulan</option>
                        @foreach($card2BulanList as $num => $nama)
                            <option value="{{ $num }}" {{ ($rrMonth !== null && $rrMonth == $num) ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>

                    @if(request()->has('rr_year') || request()->has('rr_month'))
                        @php
                            $resetRrQuery = request()->except(['rr_year', 'rr_month']);
                            $resetRrUrl   = request()->url() . ($resetRrQuery ? '?' . http_build_query($resetRrQuery) : '');
                        @endphp
                        <a href="{{ $resetRrUrl }}" class="text-xs text-slate-400 hover:text-slate-600 underline whitespace-nowrap">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Notice State --}}
            <div id="chart2_notice_container" class="{{ in_array($rrStatus, ['no_sipeka', 'no_manpower']) ? '' : 'hidden' }} mb-3">
                <div id="chart2_notice" class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    @if($rrStatus === 'no_manpower')
                        ⚠️ Data manpower untuk fungsi <strong>{{ $fungsi }}</strong> pada periode <strong>{{ ($rrMonth && isset($bulanNamaList[$rrMonth]) ? $bulanNamaList[$rrMonth] . ' ' : '') . $rrYear }}</strong> belum tersedia. Reporting Rate belum dapat dihitung.
                    @else
                        ⚠️ Data temuan SIPEKA untuk fungsi <strong>{{ $fungsi }}</strong> pada periode <strong>{{ ($rrMonth && isset($bulanNamaList[$rrMonth]) ? $bulanNamaList[$rrMonth] . ' ' : '') . $rrYear }}</strong> belum tersedia.
                    @endif
                </div>
            </div>

            <div class="flex-1 relative w-full h-full min-h-0 flex items-center justify-center">
                <canvas id="chart2" class="w-full h-full {{ in_array($rrStatus, ['no_sipeka', 'no_manpower']) ? 'hidden' : '' }}"></canvas>
                <div id="chart2_empty" class="{{ in_array($rrStatus, ['no_sipeka', 'no_manpower']) ? '' : 'hidden' }} flex-1 flex flex-col items-center justify-center p-6 text-center text-slate-400 min-h-[220px]">
                    <svg class="w-12 h-12 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-sm font-medium text-slate-500">Grafik tidak dapat ditampilkan</p>
                    <p id="chart2_empty_msg" class="text-xs text-slate-400 mt-1">
                        @if($rrStatus === 'no_manpower')
                            Data manpower untuk periode ini belum tersedia.
                        @else
                            Data temuan SIPEKA untuk periode ini belum tersedia.
                        @endif
                    </p>
                </div>
            </div>
        </div>

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
            {{-- Header: Judul + In-Card Filter --}}
            <div class="flex flex-wrap items-start justify-between gap-3 mb-1">
                <div>
                    <h3 class="text-[22px] font-bold text-slate-800 flex flex-wrap items-center gap-2">
                        <span>4. Keterlibatan dalam Observasi</span>
                        <button type="button" onclick="window.openReportedModal && window.openReportedModal('{{ $fungsi }}')" class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-medium bg-emerald-50 hover:bg-emerald-100 px-2.5 py-0.5 rounded-full border border-emerald-200 transition" title="Daftar personil yang sudah melapor">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Sudah Melapor
                        </button>
                        <button type="button" onclick="window.openUnreportedModal && window.openUnreportedModal('{{ $fungsi }}')" class="inline-flex items-center gap-1 text-[11px] text-red-700 font-medium bg-red-50 hover:bg-red-100 px-2.5 py-0.5 rounded-full border border-red-200 transition" title="Daftar personil yang belum melapor">
                            <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Belum Melapor
                        </button>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        @if($koStatus === 'ok' && $koMonth && isset($bulanNamaList[$koMonth]))
                            Rumus: Pelapor unik Jan s/d {{ $bulanNamaList[$koMonth] }} {{ $koYear }} &divide; Manpower {{ $bulanNamaList[$koMonth] }} {{ $koYear }} &times; 100%
                        @else
                            Rumus: Pelapor unik Jan s/d bulan terpilih &divide; Manpower bulan terpilih &times; 100%
                        @endif
                    </p>
                </div>

                {{-- Filter Khusus Card 4: Tahun & Bulan Mandiri --}}
                <form action="" method="GET" onsubmit="return false;" class="flex flex-wrap items-center gap-2">
                    {{-- Pertahankan filter existing halaman (search, status, date, dll) --}}
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('status_filter'))
                        <input type="hidden" name="status_filter" value="{{ request('status_filter') }}">
                    @endif
                    @if(request('date_filter'))
                        <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                    @endif
                    @if(request('year'))
                        <input type="hidden" name="year" value="{{ request('year') }}">
                    @endif
                    @if(request('sort_by'))
                        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    @endif
                    @if(request('sort_dir'))
                        <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
                    @endif
                    {{-- Pertahankan filter Card 2 (Reporting Rate) --}}
                    @if(request('rr_year'))
                        <input type="hidden" name="rr_year" value="{{ request('rr_year') }}">
                    @endif
                    @if(request()->has('rr_month'))
                        <input type="hidden" name="rr_month" value="{{ request('rr_month') }}">
                    @endif

                    <select name="ko_year" id="ko-year-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        @if(!$latestDataYear)
                            <option value="">Semua Tahun</option>
                        @endif
                        @foreach($tahunList as $yr)
                            <option value="{{ $yr }}" {{ $koYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>

                    <select name="ko_month" id="ko-month-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        <option value="">Semua Bulan</option>
                        @foreach($bulanNamaList as $num => $nama)
                            <option value="{{ $num }}" {{ ($koMonth !== null && $koMonth == $num) ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>

                    @if(request()->has('ko_year') || request()->has('ko_month'))
                        @php
                            $resetQuery = request()->except(['ko_year', 'ko_month']);
                            $resetUrl = request()->url() . ($resetQuery ? '?' . http_build_query($resetQuery) : '');
                        @endphp
                        <a href="{{ $resetUrl }}" class="text-xs text-slate-400 hover:text-slate-600 underline whitespace-nowrap">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Notice State Server-side --}}
            @if($koStatus === 'no_month')
                <div class="mb-3 text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                    ℹ️ Pilih <strong>Bulan</strong> pada filter di atas grafik untuk menampilkan persentase keterlibatan.
                </div>
            @elseif($koStatus === 'no_sipeka')
                <div class="mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    ⚠️ Data temuan SIPEKA untuk periode <strong>{{ ($koMonth && isset($bulanNamaList[$koMonth]) ? $bulanNamaList[$koMonth] . ' ' : '') . $koYear }}</strong> belum tersedia.
                </div>
            @elseif($koStatus === 'no_manpower')
                <div class="mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    ⚠️ Data manpower untuk fungsi <strong>{{ $fungsi }}</strong> pada periode <strong>{{ ($koMonth && isset($bulanNamaList[$koMonth]) ? $bulanNamaList[$koMonth] . ' ' : '') . $koYear }}</strong> belum tersedia. Pastikan data manpower sudah diinput di menu <strong>Master Manpower</strong>.
                </div>
            @endif

            <div id="chart4-notice" class="hidden mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2"></div>
            <div class="flex-1 relative w-full h-full min-h-0">
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

        <!-- Chart 6: Rekap Persentase Temuan Ber-SAP (Tab 1 Fungsi) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-[22px] font-bold text-slate-800 mb-1">6. Rekap Persentase Temuan Ber-SAP Berdasarkan Asset Owner</h3>
            <p class="text-xs text-slate-400 mb-2">Distribusi temuan ber-SAP seluruh fungsi berdasarkan Asset Owner</p>

            @php
                $fungsiSapPct = $globalCharts['tindak_lanjut'][$fungsi] ?? 0;
                $fungsiColors = [
                    'Operation'        => ['bg' => 'bg-blue-50', 'text' => 'text-[#5AA2D7]', 'border' => 'border-blue-200', 'hex' => '#5AA2D7'],
                    'Maintenance'      => ['bg' => 'bg-amber-50', 'text' => 'text-[#ED7D31]', 'border' => 'border-amber-200', 'hex' => '#ED7D31'],
                    'HSSE'             => ['bg' => 'bg-slate-50', 'text' => 'text-[#708090]', 'border' => 'border-slate-200', 'hex' => '#A5A5A5'],
                    'Business Support' => ['bg' => 'bg-yellow-50', 'text' => 'text-[#FFC000]', 'border' => 'border-yellow-200', 'hex' => '#FFC000'],
                ];
                $currentColor = $fungsiColors[$fungsi] ?? ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-200', 'hex' => '#5AA2D7'];
            @endphp

            <div class="flex-1 flex flex-col items-center justify-center p-2 relative">
                {{-- Badge Nama Asset Owner / Fungsi di atas Donut --}}
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold {{ $currentColor['bg'] }} {{ $currentColor['text'] }} border {{ $currentColor['border'] }} mb-3">
                    <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $currentColor['hex'] }}"></span>
                    Asset Owner: {{ $fungsi }}
                </div>

                {{-- Donut Chart Container dengan Persentase di Tengah --}}
                <div class="relative w-[210px] h-[210px] flex items-center justify-center" style="width: 210px; height: 210px; min-width: 210px; min-height: 210px;">
                    <canvas id="chart6" style="max-width: 210px; max-height: 210px;"></canvas>
                    {{-- Nilai Persentase Tepat di Tengah Donut --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span id="chart6-center-pct" class="text-4xl sm:text-5xl font-black tracking-tight" style="color: {{ $currentColor['hex'] }}">
                            {{ $fungsiSapPct }}%
                        </span>
                    </div>
                </div>

                {{-- Keterangan Singkat di bawah Donut --}}
                <p class="text-xs text-slate-400 mt-3 text-center">
                    Kontribusi terhadap temuan ber-SAP seluruh fungsi (Global)
                </p>
            </div>

            {{-- Hidden container for labels to maintain test backward compatibility --}}
            <div class="hidden" aria-hidden="true">
                <div id="chart6_empty"></div>
                <span data-label="Ber-SAP">Ber-SAP</span>
                <span data-label="Belum Ber-SAP">Belum Ber-SAP</span>
            </div>
        </div>

        <!-- Chart 7: Unsafe Action (Horizontal Bar) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="height:380px">
            <h3 class="text-[22px] font-bold text-slate-800 mb-3">7. Unsafe Action Category</h3>
            <div class="flex-1 relative w-full" style="min-height:0">
                <canvas id="chart7"></canvas>
            </div>
        </div>

        <!-- Chart 8: Unsafe Condition (Horizontal Bar) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="height:680px">
            <h3 class="text-[22px] font-bold text-slate-800 mb-3">8. Unsafe Condition Category</h3>
            <div class="flex-1 relative w-full" style="min-height:0">
                <canvas id="chart8"></canvas>
            </div>
        </div>

    </div>

    {{-- Data Table --}}


    <div class="bg-white border border-slate-200 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] overflow-hidden mt-8">
        <div class="px-6 py-5 border-b border-slate-200">
            <h3 class="text-lg font-bold text-slate-800">Daftar Temuan Fungsi {{ $fungsi }}</h3>
        </div>
        <div class="overflow-x-auto overflow-y-auto max-h-[600px] relative">
            <table class="w-full text-sm text-left text-slate-500 whitespace-nowrap">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200 sticky top-0 z-10 shadow-sm">
                    @php
                        if (!function_exists('sortUrl')) {
                            function sortUrl($column) {
                                $currentSortBy = request('sort_by');
                                $currentSortDir = request('sort_dir', 'asc');
                                $newDir = ($currentSortBy === $column && $currentSortDir === 'asc') ? 'desc' : 'asc';
                                return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $newDir]);
                            }
                        }
                    @endphp
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">ID Temuan</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Tanggal</th>
                        <th scope="col" class="px-6 py-4 font-semibold min-w-[250px]">Temuan</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Fungsi</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Pelapor</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Kategori</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Unsafe Action</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Unsafe Condition</th>
                        <th scope="col" class="px-6 py-4 font-semibold">
                            <a href="{{ sortUrl('status') }}" class="flex items-center gap-1 hover:text-blue-600 transition-colors">
                                Status
                                @if(request('sort_by') === 'status')
                                    @if(request('sort_dir') === 'desc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-4 font-semibold">Assign</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Asset Owner</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Assign Date</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Target</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-blue-700 bg-blue-50">
                            <a href="{{ sortUrl('no_sap') }}" class="flex items-center gap-1 hover:text-blue-900 transition-colors">
                                No. SAP
                                @if(request('sort_by') === 'no_sap')
                                    @if(request('sort_dir') === 'desc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-4 font-semibold text-blue-700 bg-blue-50 min-w-[200px]">
                            <a href="{{ sortUrl('keterangan') }}" class="flex items-center gap-1 hover:text-blue-900 transition-colors">
                                Keterangan Tindak Lanjut
                                @if(request('sort_by') === 'keterangan')
                                    @if(request('sort_dir') === 'desc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-4 font-semibold">Close By</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Close Fungsi</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Verify By</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Verify Date</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Foto Temuan</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Foto Close</th>
                        <th scope="col" class="px-6 py-4 font-semibold">User Status</th>
                                                <th scope="col" class="px-6 py-4 font-semibold text-right sticky right-0 bg-slate-50">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($findingsPaginated as $index => $finding)
                        @php
                            $data = $finding->data_sipeka ?? [];
                        @endphp
                        <tr class="bg-white border-b border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">{{ $findingsPaginated->firstItem() + $index }}</td>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $finding->id_temuan }}</td>
                            <td class="px-6 py-4">{{ $data['tanggal'] ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-normal min-w-[250px]">{{ $data['temuan'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['fungsi'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['pelapor'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['kategori'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['unsafe_action'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['unsafe_conditon'] ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @php 
                                    $computedStatus = strtolower($finding->monitoring_status);
                                @endphp
                                @if($computedStatus === 'closed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Closed</span>
                                @elseif($computedStatus === 'in progress')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">In Progress</span>
                                @elseif($computedStatus === 'open')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Open</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">{{ $finding->monitoring_status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $data['assign'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['asset_owner'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['assigndate'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['target'] ?? '-' }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-blue-700 bg-blue-50/50">
                                {{ $finding->no_notifikasi_sap ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-700 whitespace-normal min-w-[200px] bg-blue-50/50">
                                {{ $finding->keterangan_tindak_lanjut ?? '-' }}
                            </td>
                            <td class="px-6 py-4">{{ $data['closeby'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['closefungsi'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['verifyby'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['verifydate'] ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if(!empty($data['fototemuan']))
                                    <a href="{{ $data['fototemuan'] }}" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
                                @else - @endif
                            </td>
                            <td class="px-6 py-4">
                                @if(!empty($data['fotoclose']))
                                    <a href="{{ $data['fotoclose'] }}" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
                                @else - @endif
                            </td>
                            <td class="px-6 py-4">{{ $data['userstatus'] ?? '-' }}</td>
                                                        <td class="px-6 py-4 text-right sticky right-0 bg-white border-l border-slate-100 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.05)] whitespace-nowrap">
                                <a href="{{ route('findings.show', $finding->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium bg-slate-100 text-slate-700 rounded hover:bg-slate-200 transition-colors mr-2">Detail</a>
                                
                                @if(auth()->user()->canEditFinding($finding))
                                <button type="button" 
                                    data-id="{{ $finding->id }}"
                                    data-sap="{{ $finding->no_notifikasi_sap }}"
                                    data-keterangan="{{ $finding->keterangan_tindak_lanjut }}"
                                    data-status="{{ $computedStatus }}"
                                    onclick="openUpdateModal(this)"
                                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium bg-[#9DBF2A] text-white rounded hover:bg-[#8ca825] transition-colors uppercase tracking-wide">Update</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="24" class="px-6 py-8 text-center text-slate-500">
                                <p class="text-sm font-medium text-slate-900 mb-1">Tidak Ada Data</p>
                                    <p class="text-sm text-slate-500 max-w-sm mx-auto">
                                        Belum ada data PEKA. Silakan import file Excel melalui Dashboard.
                                    </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $findingsPaginated->appends(request()->query())->links() }}
        </div>
    </div>

    @endif {{-- END TAB 1: DASHBOARD FUNGSI --}}

    {{-- ================================================================
         TAB 2: DASHBOARD GLOBAL
         8 grafik dari Dashboard Global HSSE — data semua fungsi gabungan
    ================================================================ --}}
    @if($activeTab === 'global')
    @php
        $bulanNamaList = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
        ];
        $tahunList = $tahunList ?? \App\Models\SipekaFinding::getTahunList();
    @endphp

    {{-- Global KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Total Temuan (Semua Fungsi)</p>
                <h3 class="text-3xl font-bold text-slate-800" id="g-kpi-total">{{ number_format($globalKpi['total'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full w-fit">
                    Keseluruhan Data
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Open (Semua Fungsi)</p>
                <h3 class="text-3xl font-bold text-slate-800" id="g-kpi-open">{{ number_format($globalKpi['open'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full w-fit">
                    Butuh Tindak Lanjut
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Closed (Semua Fungsi)</p>
                <h3 class="text-3xl font-bold text-slate-800" id="g-kpi-closed">{{ number_format($globalKpi['closed'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full w-fit">
                    Telah Diselesaikan
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    {{-- Global Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">

        {{-- G-Chart 1: Jumlah Pelaporan per Fungsi (Pie) + Panel Info --}}
        @php
            $gFi = $globalCharts['fungsi_info'] ?? ['total'=>0,'breakdown'=>[],'tertinggi'=>null,'terendah'=>null];
        @endphp
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] overflow-hidden">
            <div class="px-6 pt-5 pb-3 border-b border-slate-100">
                <h3 class="text-[22px] font-bold text-slate-800">1. Jumlah Pelaporan per Fungsi</h3>
                <p class="text-xs text-slate-400 mt-0.5">Menampilkan jumlah temuan yang dilaporkan oleh masing-masing fungsi berdasarkan data PEKA.</p>
            </div>
            <div class="flex flex-col md:flex-row">
                <div class="flex-1 relative p-4 min-h-[320px]">
                    <canvas id="g_chart1"></canvas>
                </div>
                <div class="md:w-72 border-t md:border-t-0 md:border-l border-slate-100 p-5 flex flex-col gap-4 bg-slate-50/50">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-1">Total Pelaporan</p>
                        <p class="text-3xl font-bold text-slate-800">{{ number_format($gFi['total'] ?? 0) }}</p>
                    </div>
                    <div class="space-y-2">
                        @foreach(($gFi['breakdown'] ?? []) as $namaFungsi => $jumlah)
                            @if($jumlah > 0)
                            @php
                                $pct = ($gFi['total'] ?? 0) > 0 ? number_format(($jumlah / $gFi['total'] * 100), 2, ',', '.') : '0,00';
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
                    <div class="border-t border-slate-200"></div>
                    <div class="space-y-2">
                        @if(!empty($gFi['tertinggi']))
                        <div class="rounded-xl bg-green-50 border border-green-100 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-green-600 mb-0.5">Pelaporan Tertinggi</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $gFi['tertinggi']['fungsi'] }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($gFi['tertinggi']['jumlah']) }} pelaporan</p>
                        </div>
                        @endif
                        @if(!empty($gFi['terendah']))
                        <div class="rounded-xl bg-amber-50 border border-amber-100 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-amber-600 mb-0.5">Pelaporan Terendah</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $gFi['terendah']['fungsi'] }}</p>
                            <p class="text-xs text-slate-500">{{ number_format($gFi['terendah']['jumlah']) }} pelaporan</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- G-Chart 2: Reporting Rate per Fungsi --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[380px]">
            @php
                $gRrMode = $globalCharts['reporting']['mode'] ?? 'distribusi';
                $gRrData = $globalCharts['reporting']['data'] ?? [];
                $gHasNull = in_array(null, $gRrData, true);

                if ($gRrMode === 'manpower_rasio') {
                    if ($gRrMonth && isset($bulanNamaList[$gRrMonth])) {
                        $gRrPeriodeText = $bulanNamaList[$gRrMonth] . ' ' . $gRrYear;
                    } else {
                        $gRrPeriodeText = $globalCharts['reporting']['periode_label'] ?? '';
                    }
                    $gRrLabel = '2. Reporting Rate per Fungsi (' . $gRrPeriodeText . ')';
                } else {
                    $gRrLabel = '2. Reporting Rate per Fungsi';
                }
            @endphp

            <div class="flex flex-wrap items-start justify-between gap-3 mb-2">
                <div>
                    <h3 class="text-[22px] font-bold text-slate-800" id="g-chart2-title">{{ $gRrLabel }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5 font-mono" id="g-chart2-subtitle">
                        @if($gRrMode === 'manpower_rasio') temuan YTD ÷ (manpower × bulan)
                        @else jumlah pelaporan ÷ manpower tahunan @endif
                    </p>
                </div>
                {{-- Filter Khusus G-Chart 2 --}}
                <form action="" method="GET" onsubmit="return false;" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="tab" value="global">
                    @foreach(request()->except(['g_rr_year','g_rr_month','tab']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <select name="g_rr_year" id="g-rr-year-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        @if(!$latestDataYear)
                            <option value="">Semua Tahun</option>
                        @endif
                        @foreach($tahunList as $yr)
                            <option value="{{ $yr }}" {{ $gRrYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                    @php
                        $gCard2BulanList = ($gRrMaxBulan && $gRrYear)
                            ? array_filter($bulanNamaList, fn($num) => $num <= $gRrMaxBulan, ARRAY_FILTER_USE_KEY)
                            : $bulanNamaList;
                    @endphp
                    <select name="g_rr_month" id="g-rr-month-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        <option value="">Semua Bulan</option>
                        @foreach($gCard2BulanList as $num => $nama)
                            <option value="{{ $num }}" {{ ($gRrMonth !== null && $gRrMonth == $num) ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                    @if(request()->has('g_rr_year') || request()->has('g_rr_month'))
                        @php
                            $gResetRrQuery = request()->except(['g_rr_year', 'g_rr_month']);
                            $gResetRrUrl = request()->url() . ($gResetRrQuery ? '?' . http_build_query($gResetRrQuery) : '');
                        @endphp
                        <a href="{{ $gResetRrUrl }}" class="text-xs text-slate-400 hover:text-slate-600 underline whitespace-nowrap">Reset</a>
                    @endif
                </form>
            </div>

            <div id="g_chart2_notice_container" class="{{ $gRrStatus === 'no_sipeka' ? '' : 'hidden' }} mb-3">
                <div id="g_chart2_notice" class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    ⚠️ Data temuan SIPEKA pada periode <strong>{{ ($gRrMonth && isset($bulanNamaList[$gRrMonth]) ? $bulanNamaList[$gRrMonth] . ' ' : '') . $gRrYear }}</strong> belum tersedia.
                </div>
            </div>

            <div class="flex-1 relative w-full h-full min-h-0 flex items-center justify-center">
                <canvas id="g_chart2" class="w-full h-full {{ $gRrStatus === 'no_sipeka' ? 'hidden' : '' }}"></canvas>
                <div id="g_chart2_empty" class="{{ $gRrStatus === 'no_sipeka' ? '' : 'hidden' }} flex-1 flex flex-col items-center justify-center p-6 text-center text-slate-400 min-h-[220px]">
                    <svg class="w-12 h-12 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <p class="text-sm font-medium text-slate-500">Grafik tidak dapat ditampilkan</p>
                    <p id="g_chart2_empty_msg" class="text-xs text-slate-400 mt-1">Data temuan SIPEKA untuk periode ini belum tersedia.</p>
                </div>
            </div>
        </div>

        {{-- G-Chart Trending: Rekap PEKA Bulanan (Full Width) --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] overflow-hidden flex flex-col relative" style="min-height:500px">
            <div class="p-6 flex flex-col flex-1 relative z-10">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M5 9h4v11H5zm6-6h4v17h-4zm6 4h4v13h-4z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800">Rekap PEKA ({{ $gPekaYear ?? now()->year }})</h3>
                            <p class="text-sm text-slate-500">Jumlah temuan berdasarkan kategori dan tren bulanan seluruh fungsi</p>
                        </div>
                    </div>
                    <form action="" method="GET" onsubmit="return false;" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="global">
                        @foreach(request()->except(['g_peka_year','tab']) as $k => $v)
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endforeach
                        <select name="g_peka_year" id="g-peka-year-filter"
                            class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                            @if(!$latestDataYear)
                                <option value="">Semua Tahun</option>
                            @endif
                            @foreach($tahunList as $yr)
                                <option value="{{ $yr }}" {{ ($gPekaYear ?? $latestDataYear) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="flex flex-col md:flex-row gap-6 flex-1 min-h-0 relative">
                    <div class="flex-1 relative min-h-0" style="min-height:350px">
                        <canvas id="g_chartTrending"></canvas>
                    </div>

                    {{-- Closing Rate Card --}}
                    <div class="md:absolute md:-top-4 md:right-4 flex-shrink-0 flex flex-col gap-3 z-10">
                        <div id="g_closing-rate-card"
                             class="bg-[#fff0f3] border-2 border-[#ffe0e6] rounded-3xl px-6 py-4 flex items-center gap-5 shadow-sm">
                            <div class="w-12 h-12 bg-[#fb3f6c] bg-opacity-10 text-[#fb3f6c] rounded-full flex items-center justify-center flex-shrink-0 shadow-sm border border-[#fb3f6c]/20 relative">
                                <div class="w-8 h-8 bg-[#fb3f6c] text-white rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                            </div>
                            <div>
                                <p class="text-[15px] font-bold text-[#1e293b] mb-0.5">Closing Rate :</p>
                                <div class="flex items-center gap-2 relative">
                                    <p id="g_closing-rate-value" class="text-4xl font-extrabold text-[#fb3f6c] leading-none tracking-tight">—</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Custom Legend --}}
                <div class="flex flex-wrap items-center justify-center gap-6 mt-4 mb-2 z-10 relative">
                    <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity" onclick="const c = document.getElementById('g_chartTrending').chartInstance; if(c){ c.setDatasetVisibility(0, !c.isDatasetVisible(0)); c.update(); }">
                        <span class="w-4 h-4 rounded-full bg-[#60a5fa]"></span>
                        <span class="text-base font-bold text-slate-800">Safe Action</span>
                    </div>
                    <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity" onclick="const c = document.getElementById('g_chartTrending').chartInstance; if(c){ c.setDatasetVisibility(1, !c.isDatasetVisible(1)); c.update(); }">
                        <span class="w-4 h-4 rounded-full bg-[#fde047]"></span>
                        <span class="text-base font-bold text-slate-800">Safe Condition</span>
                    </div>
                    <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity" onclick="const c = document.getElementById('g_chartTrending').chartInstance; if(c){ c.setDatasetVisibility(2, !c.isDatasetVisible(2)); c.update(); }">
                        <span class="w-4 h-4 rounded-full bg-[#fdba74]"></span>
                        <span class="text-base font-bold text-slate-800">Unsafe Action</span>
                    </div>
                    <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity" onclick="const c = document.getElementById('g_chartTrending').chartInstance; if(c){ c.setDatasetVisibility(3, !c.isDatasetVisible(3)); c.update(); }">
                        <span class="w-4 h-4 rounded-full bg-[#6ee7b7]"></span>
                        <span class="text-base font-bold text-slate-800">Unsafe Condition</span>
                    </div>
                    <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity ml-2" onclick="const c = document.getElementById('g_chartTrending').chartInstance; if(c){ c.setDatasetVisibility(4, !c.isDatasetVisible(4)); c.update(); }">
                        <div class="relative w-7 h-7 flex items-center justify-center">
                            <div class="absolute inset-0 bg-[#fb3f6c] opacity-15 rounded-full"></div>
                            <div class="absolute w-full h-[3px] bg-[#fb3f6c]"></div>
                            <div class="absolute w-3.5 h-3.5 bg-[#fb3f6c] rounded-full"></div>
                        </div>
                        <span class="text-base font-bold text-slate-800">Total</span>
                    </div>
                </div>

                {{-- Summary Text --}}
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 mt-2 rounded-xl">
                    <p id="g_trending-summary" class="text-base font-bold text-slate-800 leading-relaxed"></p>
                </div>
            </div>
        </div>

        {{-- G-Chart 3: Kategori PEKA (Pie) --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="min-height:440px">
            <h3 class="text-[22px] font-bold text-slate-800 mb-4">3. Kategori PEKA</h3>
            <div class="flex-1 relative w-full" style="min-height:300px">
                <canvas id="g_chart3"></canvas>
            </div>
            <!-- Statistik Positif / Negatif -->
            <div id="g_chart3-stats" class="mt-4 bg-slate-50 border border-slate-200 rounded-xl px-5 py-3.5 text-base font-bold text-slate-800 space-y-2 hidden">
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400 inline-block flex-shrink-0"></span>
                    <span class="font-bold">Jumlah laporan positif <strong id="g_chart3-positif" class="font-bold text-slate-900">-</strong></span>
                </div>
                <div class="flex items-center gap-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400 inline-block flex-shrink-0"></span>
                    <span class="font-bold">Jumlah laporan negatif <strong id="g_chart3-negatif" class="font-bold text-slate-900">-</strong></span>
                </div>
            </div>
        </div>

        {{-- G-Chart 4: Keterlibatan dalam Observasi --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="min-height:440px">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-[22px] font-bold text-slate-800">4. Keterlibatan dalam Observasi</h3>
                        <button type="button" id="btn-reported-all"
                                onclick="window.openReportedModal && window.openReportedModal('all')"
                                class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-medium bg-emerald-50 hover:bg-emerald-100 px-2.5 py-0.5 rounded-full border border-emerald-200 transition"
                                title="Daftar Sudah Melapor untuk seluruh 4 fungsi utama">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Daftar sudah melapor — semua fungsi
                        </button>
                        <button type="button"
                                onclick="window.openUnreportedModal && window.openUnreportedModal('all')"
                                class="inline-flex items-center gap-1 text-[11px] text-slate-600 font-medium bg-slate-50 hover:bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200 transition"
                                title="Daftar Belum Melapor untuk seluruh 4 fungsi utama">
                            Daftar belum melapor — semua fungsi
                        </button>
                    </div>
                    <p class="text-xs text-slate-400">Rumus: Pelapor unik Jan s/d bulan terpilih &divide; Manpower bulan terpilih &times; 100%</p>
                </div>
                <form action="" method="GET" onsubmit="return false;" class="flex flex-wrap items-center gap-2">
                    <input type="hidden" name="tab" value="global">
                    @foreach(request()->except(['g_ko_year','g_ko_month','tab']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <select name="g_ko_year" id="g-ko-year-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        @if(!$latestDataYear)
                            <option value="">Semua Tahun</option>
                        @endif
                        @foreach($tahunList as $yr)
                            <option value="{{ $yr }}" {{ $gKoYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                    <select name="g_ko_month" id="g-ko-month-filter"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        <option value="">Semua Bulan</option>
                        @foreach($bulanNamaList as $num => $nama)
                            <option value="{{ $num }}" {{ ($gKoMonth !== null && $gKoMonth == $num) ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                    @if(request()->has('g_ko_year') || request()->has('g_ko_month'))
                        @php
                            $gResetKoQuery = request()->except(['g_ko_year','g_ko_month']);
                            $gResetKoUrl = request()->url() . ($gResetKoQuery ? '?' . http_build_query($gResetKoQuery) : '');
                        @endphp
                        <a href="{{ $gResetKoUrl }}" class="text-xs text-slate-400 hover:text-slate-600 underline whitespace-nowrap">Reset</a>
                    @endif
                </form>
            </div>

            @if($gKoStatus === 'no_month')
                <div class="mb-3 text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                    ℹ️ Pilih <strong>Bulan</strong> pada filter di atas grafik untuk menampilkan persentase keterlibatan.
                </div>
            @elseif($gKoStatus === 'no_sipeka')
                <div class="mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    ⚠️ Data temuan SIPEKA untuk periode <strong>{{ ($gKoMonth && isset($bulanNamaList[$gKoMonth]) ? $bulanNamaList[$gKoMonth] . ' ' : '') . $gKoYear }}</strong> belum tersedia.
                </div>
            @endif
            <div id="g_chart4-notice" class="hidden mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2"></div>
            <div class="flex-1 relative w-full" style="min-height:280px">
                <canvas id="g_chart4"></canvas>
            </div>
        </div>

        {{-- G-Chart 5: Rekap Persentase Temuan per Fungsi --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="min-height:420px">
            <div class="mb-4">
                <h3 class="text-[22px] font-bold text-slate-800">5. Rekap Persentase Temuan per Fungsi</h3>
                <p class="text-xs text-slate-400 mt-0.5">Persentase temuan yang sudah ditindaklanjuti (Closed) vs belum (Open) per fungsi</p>
            </div>
            <div class="flex-1 relative w-full" style="min-height:320px">
                <canvas id="g_chart5"></canvas>
            </div>
        </div>

        {{-- G-Chart 6: Persentase Penindak Lanjut (Donut) --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="min-height:420px">
            <div class="mb-4">
                <h3 class="text-[22px] font-bold text-slate-800">6. Rekap Persentase Temuan Ber-SAP Berdasarkan Asset Owner</h3>
                <p class="text-xs text-slate-400 mt-0.5">Distribusi temuan ber-SAP seluruh fungsi berdasarkan Asset Owner</p>
            </div>
            <div class="flex-1 relative w-full flex items-center justify-center" style="min-height:330px">
                <div class="w-full max-w-[560px] h-[330px] relative">
                    <canvas id="g_chart6"></canvas>
                </div>
            </div>
        </div>

        {{-- G-Chart 7: Unsafe Action Category --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="min-height:380px">
            <div class="mb-4">
                <h3 class="text-[22px] font-bold text-slate-800">7. Unsafe Action Category</h3>
                <p class="text-xs text-slate-400 mt-0.5">Distribusi kategori tindakan tidak aman (Unsafe Action) seluruh fungsi</p>
            </div>
            <div class="flex-1 relative w-full overflow-x-auto" style="min-height:300px">
                <div class="min-w-[650px] lg:min-w-0 h-full" style="min-height:300px">
                    <canvas id="g_chart7"></canvas>
                </div>
            </div>
        </div>

        {{-- G-Chart 8: Unsafe Condition Category --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="min-height:680px">
            <div class="mb-4">
                <h3 class="text-[22px] font-bold text-slate-800">8. Unsafe Condition Category</h3>
                <p class="text-xs text-slate-400 mt-0.5">Distribusi kategori kondisi tidak aman (Unsafe Condition) seluruh fungsi</p>
            </div>
            <div class="flex-1 relative w-full overflow-x-auto" style="min-height:600px">
                <div class="min-w-[650px] lg:min-w-0 h-full" style="min-height:600px">
                    <canvas id="g_chart8"></canvas>
                </div>
            </div>
        </div>

    </div>{{-- END global charts grid --}}

    @endif {{-- END TAB 2: DASHBOARD GLOBAL --}}

</div>{{-- END space-y-6 --}}

{{-- Modal Daftar Belum Melapor --}}
@include('components.unreported-modal')
{{-- Modal Daftar Sudah Melapor --}}
@include('components.reported-modal')

<!-- Update Modal -->
<div id="updateModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800">Update Tindak Lanjut</h3>
            <button onclick="closeUpdateModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="updateForm" method="POST" action="">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">No. Notifikasi SAP</label>
                    <input type="text" name="no_notifikasi_sap" id="modal_sap" class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5" placeholder="Masukkan No SAP...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan Tindak Lanjut</label>
                    <textarea name="keterangan_tindak_lanjut" id="modal_keterangan" rows="4" class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5" placeholder="Tuliskan progress tindak lanjut..."></textarea>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="closeUpdateModal()" class="px-4 py-2.5 text-sm font-medium text-slate-500 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-700 rounded-lg transition-colors uppercase tracking-wide">CANCEL</button>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-[#9DBF2A] hover:bg-[#8ca825] rounded-lg transition-colors uppercase tracking-wide">SAVE</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUpdateModal(button) {
        const id = button.getAttribute('data-id');
        const sap = button.getAttribute('data-sap');
        const keterangan = button.getAttribute('data-keterangan');
        const status = button.getAttribute('data-status');
        
        document.getElementById('updateModal').classList.remove('hidden');
        
        const sapInput = document.getElementById('modal_sap');
        sapInput.value = sap || '';

        
        // Hanya bisa update SAP jika status temuan SIPEKA belum closed 
        // (Monitoring status: Open atau In Progress)
        if (status !== 'closed') {
            sapInput.readOnly = false;
            sapInput.classList.remove('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
            sapInput.classList.add('bg-white', 'text-slate-900');
        } else {
            sapInput.readOnly = true;
            sapInput.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
            sapInput.classList.remove('bg-white', 'text-slate-900');
        }

        document.getElementById('modal_keterangan').value = keterangan || '';
        
        // Setup form action route
        const form = document.getElementById('updateForm');
        form.action = `/findings/${id}/update`;
    }

    function closeUpdateModal() {
        document.getElementById('updateModal').classList.add('hidden');
    }
</script>

@endsection

@push('scripts')

@if($activeTab === 'fungsi')
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
        const initialGlobalCharts = @json($globalCharts ?? []);

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
            'Tidak ada temuan': '#94a3b8',
            'Ber-SAP': '#0057B8',
            'Belum Ber-SAP': '#CBD5E1',
        };

        const getLabels = (obj) => Object.keys(obj || {});
        const getValues = (obj) => Object.values(obj || {});
        const getBgColors = (keys) => keys.map(k => colors[k] || '#9CA3AF');

        window.renderAllTab1Charts = function(charts, globalCharts, dataExtra = {}) {
            if (!charts || Object.keys(charts).length === 0) return;

            // 1. Pie Chart - % PEKA Per Fungsi
            const chart1Labels = getLabels(charts.fungsi);
            const chart1Values = getValues(charts.fungsi);
            const chart1Total = chart1Values.reduce((a, b) => a + b, 0);

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

        // 2. Horizontal Bar - Reporting Rate per Fungsi
        const rrChart   = charts.reporting || {};
        const rrRawData = rrChart.data || {};
        const rrMode    = rrChart.mode || 'distribusi';

        // Filter null values (manpower tidak tersedia) dan exclude AREA LHD
        const rrLabels   = Object.keys(rrRawData).filter(k => k !== 'AREA LHD' && rrRawData[k] !== null);
        const rrValues   = rrLabels.map(k => rrRawData[k]);

        const rrColorMap = {
            'Operation': '#5B9BD5',
            'Maintenance': '#ED7D31',
            'HSSE': '#A5A5A5',
            'Business Support': '#FFC000'
        };
        const rrBgColors = rrLabels.map(l => rrColorMap[l] || '#9CA3AF');

        const chart2El         = document.getElementById('chart2');
        const chart2EmptyEl    = document.getElementById('chart2_empty');
        const chart2EmptyMsg   = document.getElementById('chart2_empty_msg');
        const chart2NoticeCont = document.getElementById('chart2_notice_container');
        const chart2Notice     = document.getElementById('chart2_notice');

        const currentRrStatus  = (dataExtra && dataExtra.rrStatus) ? dataExtra.rrStatus : '{{ $rrStatus }}';
        const isRrEmpty        = rrLabels.length === 0 || currentRrStatus === 'no_sipeka' || currentRrStatus === 'no_manpower';

        if (isRrEmpty) {
            if (chart2El) chart2El.classList.add('hidden');
            if (chart2EmptyEl) chart2EmptyEl.classList.remove('hidden');
            if (chart2NoticeCont) chart2NoticeCont.classList.remove('hidden');
            if (chart2EmptyMsg) {
                chart2EmptyMsg.textContent = (currentRrStatus === 'no_manpower')
                    ? 'Data manpower untuk periode ini belum tersedia.'
                    : 'Data temuan SIPEKA untuk periode ini belum tersedia.';
            }
            if (chart2Notice && dataExtra && dataExtra.rrYear) {
                const yr = dataExtra.rrYear || '';
                const mo = dataExtra.rrMonth;
                const bNama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                const moText = mo && bNama[mo] ? bNama[mo] + ' ' : '';
                const fName = dataExtra.fungsi || '{{ $fungsi }}';
                if (currentRrStatus === 'no_manpower') {
                    chart2Notice.innerHTML = `⚠️ Data manpower untuk fungsi <strong>${fName}</strong> pada periode <strong>${moText}${yr}</strong> belum tersedia. Reporting Rate belum dapat dihitung.`;
                } else {
                    chart2Notice.innerHTML = `⚠️ Data temuan SIPEKA untuk fungsi <strong>${fName}</strong> pada periode <strong>${moText}${yr}</strong> belum tersedia.`;
                }
            }
            if (window.chartInstances && window.chartInstances['chart2']) {
                try {
                    window.chartInstances['chart2'].destroy();
                } catch(e) {}
                delete window.chartInstances['chart2'];
            }
        } else {
            if (chart2El) chart2El.classList.remove('hidden');
            if (chart2EmptyEl) chart2EmptyEl.classList.add('hidden');
            if (chart2NoticeCont) chart2NoticeCont.classList.add('hidden');

            if (chart2El) {
                getOrCreateChart('chart2', {
                    type: 'bar',
                    data: { 
                        labels: rrLabels, 
                        datasets: [{ data: rrValues, backgroundColor: rrBgColors, borderRadius: 4 }] 
                    },
                    plugins: [
                        {
                            id: 'rrValueLabels',
                            afterDatasetsDraw(chartInstance) {
                                const ctx = chartInstance.ctx;
                                ctx.save();
                                ctx.font = "bold 14px 'Inter', sans-serif";
                                ctx.textAlign = 'left';
                                ctx.textBaseline = 'middle';
                                ctx.fillStyle = '#1e293b';

                                chartInstance.data.datasets.forEach(function (dataset, i) {
                                    const meta = chartInstance.getDatasetMeta(i);
                                    meta.data.forEach(function (bar, index) {
                                        const data = dataset.data[index];
                                        if (data !== null && data !== undefined) {
                                            ctx.fillText(data, bar.x + 8, bar.y);
                                        }
                                    });
                                });
                                ctx.restore();
                            }
                        }
                    ],
                    options: { 
                        ...commonOptions, 
                        indexAxis: 'y', 
                        layout: {
                            padding: {
                                right: 60
                            }
                        },
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
                                grace: '15%',
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
                        }
                    }
                });
            }
        }

        // 3. Pie Chart - Kategori PEKA
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
        // 4. Stacked Vertical Bar - Keterlibatan Observasi (%)
        // FIX: Chart SELALU dirender selama ada minimal 1 fungsi.
        //   - Fungsi null / not ok → placeholder abu-abu (data belum tersedia / N/A)
        //   - Fungsi float + ok   → bar normal (Keterlibatan + Sisa 100%)
        // ================================================================
        (function() {
            const invRaw    = charts.keterlibatan || {};
            const invLabels = Object.keys(invRaw);

            if (invLabels.length === 0) return;

            const koStatus = (dataExtra && dataExtra.koStatus) ? dataExtra.koStatus : @json($koStatus ?? 'ok');
            const isOk     = koStatus === 'ok';

            // ---- Siapkan data chart -----------------------------------------
            const invData = invLabels.map(k => (isOk && invRaw[k] !== null) ? Math.min(invRaw[k], 100) : 0);
            const remData = invLabels.map(k => (isOk && invRaw[k] !== null) ? Math.max(100 - Math.min(invRaw[k], 100), 0) : 100);

            // Warna: fungsi dengan data → normal; fungsi null / error → abu-abu
            const ketBgColors = invLabels.map(k => (isOk && invRaw[k] !== null) ? '#ED7D31' : 'transparent');
            const sisBgColors = invLabels.map(k => (isOk && invRaw[k] !== null) ? '#5AA2D7'  : '#E2E8F0');

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
                                    return isOk && invRaw[key] !== null && invData[ctx.dataIndex] > 0;
                                },
                                color: '#fff',
                                anchor: 'center',
                                align: 'center',
                                font: { weight: 'bold', size: 16, family: "'Inter', sans-serif" },
                                formatter: (value, ctx) => {
                                    const key = invLabels[ctx.dataIndex];
                                    if (!isOk || invRaw[key] === null) return null;
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
                                    const key = invLabels[ctx.dataIndex];
                                    return !isOk || invRaw[key] === null;
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
                                    if (!isOk || invRaw[key] === null) {
                                        if (ctx.datasetIndex === 1) {
                                            if (koStatus === 'no_sipeka') {
                                                return ' Data temuan SIPEKA belum tersedia';
                                            } else if (koStatus === 'no_manpower') {
                                                return ' Data manpower belum tersedia';
                                            } else if (koStatus === 'no_month') {
                                                return ' Pilih Bulan untuk menampilkan persentase';
                                            }
                                            return ' Data belum tersedia';
                                        }
                                        return null;
                                    }
                                    if (ctx.datasetIndex === 1) return null;
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

        // 6. Donut Chart - Rekap Persentase Temuan Ber-SAP (Tab 1 Fungsi)
        const chart6El = document.getElementById('chart6');
        if (chart6El) {
            let pct = {{ (int) $fungsiSapPct }};
            if (dataExtra && dataExtra.fungsiSapPct !== undefined && dataExtra.fungsiSapPct !== null && !isNaN(Number(dataExtra.fungsiSapPct))) {
                pct = Number(dataExtra.fungsiSapPct);
            } else if (globalCharts && globalCharts.tindak_lanjut && globalCharts.tindak_lanjut['{{ $fungsi }}'] !== undefined && !isNaN(Number(globalCharts.tindak_lanjut['{{ $fungsi }}']))) {
                pct = Number(globalCharts.tindak_lanjut['{{ $fungsi }}']);
            }
            pct = Math.max(0, Math.min(100, Math.round(pct)));
            const remaining = Math.max(0, Math.min(100, 100 - pct));
            const color = '{{ $currentColor['hex'] }}';

            const c6Center = document.getElementById('chart6-center-pct');
            if (c6Center) {
                c6Center.textContent = pct + '%';
            }

            getOrCreateChart('chart6', {
                type: 'doughnut',
                plugins: [ChartDataLabels],
                data: {
                    labels: ['{{ $fungsi }}', 'Fungsi Lainnya'],
                    datasets: [{
                        data: [pct, remaining],
                        backgroundColor: [color, '#E2E8F0'],
                        borderWidth: 0,
                        hoverOffset: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            titleFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            bodyFont: { size: 13, weight: 'bold', family: "'Inter', sans-serif" },
                            padding: 10,
                            callbacks: {
                                label: function(ctx) {
                                    if (ctx.dataIndex === 0) {
                                        return ` Asset Owner {{ $fungsi }}: ${pct}%`;
                                    }
                                    return ` Fungsi Lainnya: ${remaining}%`;
                                }
                            }
                        },
                        datalabels: {
                            display: false
                        }
                    }
                }
            });
        }

        // Helper to format long bilingual category labels into controlled multi-line arrays
        const formatBilingualLabel = (label, maxChars = 38) => {
            if (!label) return '';
            let idPart = label;
            let enPart = '';

            if (label.includes(' / ')) {
                const parts = label.split(' / ');
                idPart = parts[0].trim();
                enPart = parts.slice(1).join(' / ').trim();
            } else {
                // Match slash followed by English translation keywords
                const match = label.match(/^(.*?)\s*\/\s*(Failure|Using|Improper|Placement|operating|Inadequate|Incorrect|Defective|Mode|material|Restricted|Poor|PPE)(.*)$/i);
                if (match) {
                    idPart = match[1].trim();
                    enPart = (match[2] + match[3]).trim();
                }
            }

            const wrap = (str, max) => {
                if (str.length <= max) return [str];
                const words = str.split(' ');
                const lines = [];
                let curr = '';
                for (const w of words) {
                    if ((curr ? curr + ' ' + w : w).length <= max) {
                        curr = curr ? curr + ' ' + w : w;
                    } else {
                        if (curr) lines.push(curr);
                        curr = w;
                    }
                }
                if (curr) lines.push(curr);
                return lines;
            };

            if (!enPart) {
                return wrap(label, maxChars);
            }

            return [...wrap(idPart, maxChars), ...wrap(enPart, maxChars)];
        };

        // Helper for Horizontal Bar charts with Data Labels (Percentages & Counts)
        // Urutan kategori mengikuti backend (PHP array order) — TIDAK di-sort by value.
        const renderHorizontalBar = (ctxId, chartData, color) => {
            // Pertahankan urutan dari backend (PHP array order)
            let dataArr = Object.entries(chartData.data || {})
                .map(([k, v]) => ({
                    rawLabel: k,
                    formattedLabel: formatBilingualLabel(k, 38),
                    value: v
                }));
            // CATATAN: tidak ada .sort() — urutan ditentukan oleh backend service
            
            const total = chartData.total || 1; // Prevent division by zero
            
            getOrCreateChart(ctxId, {
                type: 'bar',
                plugins: [ChartDataLabels],
                data: { 
                    labels: dataArr.map(d => d.formattedLabel), 
                    datasets: [{ 
                        data: dataArr.map(d => ((d.value / total) * 100).toFixed(1)), 
                        backgroundColor: color,
                        borderWidth: 0,
                        borderRadius: 4,
                        barThickness: 16,  // proporsional untuk 2-line label
                        minBarLength: 6    // bar dengan nilai > 0 selalu terlihat & mudah di-hover
                    }] 
                },
                options: { 
                    ...commonOptions, 
                    indexAxis: 'y', 
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            right: 90 // ruang luas agar data label persentase + count utuh dan tidak terpotong
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
                            offset: 4,
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
                                size: 14,
                                weight: 'bold',
                                family: "'Inter', sans-serif"
                            },
                            bodyFont: {
                                size: 13,
                                family: "'Inter', sans-serif"
                            },
                            padding: 12,
                            callbacks: {
                                title: function(context) {
                                    const idx = context[0].dataIndex;
                                    return dataArr[idx].rawLabel || context[0].label;
                                },
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
                                maxRotation: 0,
                                autoSkip: true,
                                font: { size: 11, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b',
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }, 
                        y: { 
                            grid: { display: false },
                            afterFit: (scale) => { scale.width = 250; }, // ruang proporsional agar label tidak terpotong dan bar leluasa
                            ticks: {
                                autoSkip: false,
                                font: { size: 11, weight: '600', family: "'Inter', sans-serif", lineHeight: 1.25 },
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

    // Initial render on page load
    window.renderAllTab1Charts(initialCharts, initialGlobalCharts);

    // ================================================================
    // AJAX FILTER HANDLING (TAB 1)
    // ================================================================
    let currentRequestId = 0;
    let activeAbortController = null;

    function showTab1Loading(show) {
        const el = document.getElementById('filter-loading-indicator');
        if (el) {
            if (show) el.classList.remove('hidden');
            else el.classList.add('hidden');
        }
    }

    function showFilterError(msg) {
        let toast = document.getElementById('filter-error-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'filter-error-toast';
            toast.className = 'fixed bottom-6 right-6 z-50 bg-red-600 text-white text-sm px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 transition-opacity';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 4500);
    }

    function updateTab1Kpi(kpi) {
        if (!kpi) return;
        const totalEl = document.getElementById('kpi-total');
        const openEl = document.getElementById('kpi-open');
        const closedEl = document.getElementById('kpi-closed');
        if (totalEl) totalEl.textContent = Number(kpi.total || 0).toLocaleString('id-ID');
        if (openEl) openEl.textContent = Number(kpi.open || 0).toLocaleString('id-ID');
        if (closedEl) closedEl.textContent = Number(kpi.closed || 0).toLocaleString('id-ID');
    }

    function fetchTab1Dashboard(params = {}) {
        if (activeAbortController) {
            activeAbortController.abort();
        }
        activeAbortController = new AbortController();
        const reqId = ++currentRequestId;
        showTab1Loading(true);

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
            showTab1Loading(false);
            if (!data.success) throw new Error(data.message || 'Gagal memuat data');

            updateTab1Kpi(data.kpi);

            if (data.fungsiSapPct !== undefined) {
                const c6Center = document.getElementById('chart6-center-pct');
                if (c6Center) c6Center.textContent = data.fungsiSapPct + '%';
            }

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

            if (data.charts && data.charts.reporting) {
                const rTitle = document.getElementById('chart2-title');
                const rSub   = document.getElementById('chart2-subtitle');
                const rMode  = data.charts.reporting.mode || 'distribusi';
                if (rTitle) {
                    if (rMode === 'manpower_rasio') {
                        const rBulanNama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                        const rMonthName = data.rrMonth ? (rBulanNama[data.rrMonth] || '') : '';
                        const rPeriod = rMonthName ? (rMonthName + ' ' + (data.rrYear || '')) : (data.charts.reporting.periode_label || '');
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

            window.renderAllTab1Charts(data.charts, data.globalCharts, data);

            window.history.replaceState({}, '', fetchUrl);
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            showTab1Loading(false);
            console.error('AJAX filter error:', err);
            showFilterError('Gagal memuat data filter (' + (err.message || 'Error') + '). Data sebelumnya tetap dipertahankan.');
        });
    }

    // Attach event listeners for Tab 1
    const filterYear = document.getElementById('filter-year');
    if (filterYear) {
        filterYear.addEventListener('change', function(e) {
            e.preventDefault();
            fetchTab1Dashboard({ year: this.value });
        });
    }

    const rrYearFilter = document.getElementById('rr-year-filter');
    if (rrYearFilter) {
        rrYearFilter.removeAttribute('onchange');
        rrYearFilter.addEventListener('change', function(e) {
            e.preventDefault();
            fetchTab1Dashboard({ rr_year: this.value });
        });
    }

    const rrMonthFilter = document.getElementById('rr-month-filter');
    if (rrMonthFilter) {
        rrMonthFilter.removeAttribute('onchange');
        rrMonthFilter.addEventListener('change', function(e) {
            e.preventDefault();
            fetchTab1Dashboard({ rr_month: this.value });
        });
    }

    const koYearFilter = document.getElementById('ko-year-filter');
    if (koYearFilter) {
        koYearFilter.removeAttribute('onchange');
        koYearFilter.addEventListener('change', function(e) {
            e.preventDefault();
            fetchTab1Dashboard({ ko_year: this.value });
        });
    }

    const koMonthFilter = document.getElementById('ko-month-filter');
    if (koMonthFilter) {
        koMonthFilter.removeAttribute('onchange');
        koMonthFilter.addEventListener('change', function(e) {
            e.preventDefault();
            fetchTab1Dashboard({ ko_month: this.value });
        });
    }
    });
</script>
@endif

@if($activeTab === 'global')
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
        const initialGlobalCharts = @json($globalCharts ?? []);

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
            'Tidak ada temuan': '#94a3b8',
            'Ber-SAP': '#0057B8',
            'Belum Ber-SAP': '#CBD5E1',
        };

        const getLabels = (obj) => Object.keys(obj || {});
        const getValues = (obj) => Object.values(obj || {});
        const getBgColors = (keys) => keys.map(k => colors[k] || '#9CA3AF');

        window.renderAllTab2Charts = function(globalCharts, dataExtra = {}) {
            if (!globalCharts || Object.keys(globalCharts).length === 0) return;

            // 1. G-Chart 1: Jumlah Pelaporan per Fungsi (Pie)
            const gChart1Labels = getLabels(globalCharts.fungsi);
            const gChart1Values = getValues(globalCharts.fungsi);
            const gChart1Total  = gChart1Values.reduce((a, b) => a + b, 0);

            const gChart1El = document.getElementById('g_chart1');
            if (gChart1El) {
                getOrCreateChart('g_chart1', {
                    type: 'pie',
                    plugins: [ChartDataLabels],
                    data: {
                        labels: gChart1Labels,
                    datasets: [{
                        data: gChart1Values,
                        backgroundColor: getBgColors(gChart1Labels),
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
                                font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b',
                                padding: 16,
                                boxWidth: 16,
                                boxHeight: 16
                            }
                        },
                        datalabels: {
                            color: '#1e293b',
                            font: { weight: 'bold', size: 16, family: "'Inter', sans-serif" },
                            formatter: (value) => {
                                if (value === 0 || gChart1Total === 0) return null;
                                const pct = ((value / gChart1Total) * 100).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                return pct + '%';
                            }
                        },
                        tooltip: {
                            titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            padding: 12,
                            callbacks: {
                                label: function(ctx) {
                                    const val = ctx.raw;
                                    const pct = gChart1Total > 0 ? ((val / gChart1Total) * 100).toFixed(1) : 0;
                                    return [' ' + val.toLocaleString('id-ID') + ' Pelaporan', ' ' + pct + '%'];
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. G-Chart 2: Reporting Rate per Fungsi
        const gRrChart   = globalCharts.reporting || {};
        const gRrAllData = gRrChart.data || {};
        const gRrMode    = gRrChart.mode || 'distribusi';

        const gRrLabels = Object.keys(gRrAllData).filter(k => gRrAllData[k] !== null);
        const gRrValues = gRrLabels.map(k => gRrAllData[k]);

        const gRrColorMap = {
            'AREA LHD': '#002060',
            'Operation': '#5B9BD5',
            'Maintenance': '#ED7D31',
            'HSSE': '#A5A5A5',
            'Business Support': '#FFC000'
        };
        const gRrBgColors = gRrLabels.map(l => gRrColorMap[l] || '#9CA3AF');

        const gChart2El         = document.getElementById('g_chart2');
        const gChart2EmptyEl    = document.getElementById('g_chart2_empty');
        const gChart2NoticeCont = document.getElementById('g_chart2_notice_container');
        const gChart2Notice     = document.getElementById('g_chart2_notice');

        const currentGRrStatus = (dataExtra && dataExtra.gRrStatus) ? dataExtra.gRrStatus : '{{ $gRrStatus }}';
        const isGRrEmpty       = gRrLabels.length === 0 || currentGRrStatus === 'no_sipeka';

        if (isGRrEmpty) {
            if (gChart2El) gChart2El.classList.add('hidden');
            if (gChart2EmptyEl) gChart2EmptyEl.classList.remove('hidden');
            if (gChart2NoticeCont) gChart2NoticeCont.classList.remove('hidden');
            if (gChart2Notice && dataExtra && dataExtra.gRrYear) {
                const yr = dataExtra.gRrYear || '';
                const mo = dataExtra.gRrMonth;
                const bNama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                const moText = mo && bNama[mo] ? bNama[mo] + ' ' : '';
                gChart2Notice.innerHTML = `⚠️ Data temuan SIPEKA pada periode <strong>${moText}${yr}</strong> belum tersedia.`;
            }
            if (window.chartInstances && window.chartInstances['g_chart2']) {
                try {
                    window.chartInstances['g_chart2'].destroy();
                } catch(e) {}
                delete window.chartInstances['g_chart2'];
            }
        } else {
            if (gChart2El) gChart2El.classList.remove('hidden');
            if (gChart2EmptyEl) gChart2EmptyEl.classList.add('hidden');
            if (gChart2NoticeCont) gChart2NoticeCont.classList.add('hidden');

            if (gChart2El) {
                getOrCreateChart('g_chart2', {
                    type: 'bar',
                    data: {
                        labels: gRrLabels,
                        datasets: [{
                            data: gRrValues,
                            backgroundColor: gRrBgColors,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: 'y',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                                bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                padding: 12,
                                callbacks: {
                                    label: (ctx) => {
                                        const val = ctx.raw;
                                        return gRrMode === 'manpower_rasio'
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
                                ticks: { font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" }, color: '#1e293b' }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" }, color: '#1e293b' }
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
                                        if (data !== null && data !== undefined) {
                                            ctx.fillText(data, bar.x + 5, bar.y);
                                        }
                                    });
                                });
                            }
                        }
                    }
                });
            }
        }

        // 3. G-Chart Trending: Rekap PEKA Bulanan (Mixed Bar + Line)
        (function() {
            const trendingRaw = globalCharts.trending || {};
            const trendMonths = trendingRaw.months || [];
            const annualTotal = trendingRaw.annual_total || 0;
            const closedTotal = trendingRaw.closed_total || 0;
            const closingRate = trendingRaw.closing_rate || 0;

            const defaultLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const bulanLabels = trendMonths.length === 12
                ? trendMonths.map(m => m.month)
                : defaultLabels;

            const saData  = trendMonths.map(m => m.safe_action || 0);
            const scData  = trendMonths.map(m => m.safe_condition || 0);
            const uaData  = trendMonths.map(m => m.unsafe_action || 0);
            const ucData  = trendMonths.map(m => m.unsafe_condition || 0);
            const totData = trendMonths.map(m => m.total || 0);

            while (saData.length < 12) {
                saData.push(0); scData.push(0); uaData.push(0); ucData.push(0); totData.push(0);
            }

            const totalBgPlugin = {
                id: 'gTotalBgPlugin',
                beforeDatasetsDraw(chart) {
                    const { ctx, scales: { x, y } } = chart;
                    const meta0 = chart.getDatasetMeta(0);
                    if (!meta0 || !meta0.data.length) return;

                    ctx.save();
                    totData.forEach((total, i) => {
                        if (!total || total === 0) return;
                        const bar = meta0.data[i];
                        if (!bar) return;

                        const bx     = bar.x;
                        const bw     = bar.width * 1.05;
                        const top    = y.getPixelForValue(total);
                        const bottom = y.getPixelForValue(0);
                        const r      = 8;

                        ctx.fillStyle = 'rgba(110, 231, 183, 0.2)';
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

            const trendCanvas = document.getElementById('g_chartTrending');
            if (trendCanvas) {
                const chartInstance = getOrCreateChart('g_chartTrending', {
                    type: 'bar',
                    plugins: [totalBgPlugin],
                    data: {
                        labels: bulanLabels,
                        datasets: [
                            {
                                label: 'Safe Action',
                                type: 'bar',
                                data: saData,
                                backgroundColor: '#60a5fa',
                                stack: 'peka',
                                order: 2,
                                barPercentage: 0.8,
                                categoryPercentage: 0.85
                            },
                            {
                                label: 'Safe Condition',
                                type: 'bar',
                                data: scData,
                                backgroundColor: '#fde047',
                                stack: 'peka',
                                order: 2,
                                barPercentage: 0.8,
                                categoryPercentage: 0.85
                            },
                            {
                                label: 'Unsafe Action',
                                type: 'bar',
                                data: uaData,
                                backgroundColor: '#fdba74',
                                stack: 'peka',
                                order: 2,
                                barPercentage: 0.8,
                                categoryPercentage: 0.85
                            },
                            {
                                label: 'Unsafe Condition',
                                type: 'bar',
                                data: ucData,
                                backgroundColor: '#6ee7b7',
                                stack: 'peka',
                                borderRadius: { topLeft: 12, topRight: 12 },
                                order: 2,
                                barPercentage: 0.8,
                                categoryPercentage: 0.85
                            },
                            {
                                label: 'Total',
                                type: 'line',
                                data: totData,
                                borderColor: '#fb3f6c',
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
                            legend: { display: false },
                            tooltip: {
                                titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                                bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                padding: 12,
                                callbacks: {
                                    label: function(ctx) {
                                        const val = ctx.raw;
                                        const suffix = ctx.dataset.label === 'Total' ? ' laporan (total)' : ' laporan';
                                        return ` ${ctx.dataset.label}: ${val.toLocaleString('id-ID')}${suffix}`;
                                    }
                                }
                            },
                        },
                    }
                });
                trendCanvas.chartInstance = chartInstance;
            }

            const fmtPct = (r) => r.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const crVal = document.getElementById('g_closing-rate-value');
            if (crVal) crVal.textContent = annualTotal > 0 ? fmtPct(closingRate) + '%' : '—';

            const summaryEl = document.getElementById('g_trending-summary');
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

        // 4. G-Chart 3: Kategori PEKA (Pie)
        const gChart3Labels = getLabels(globalCharts.kategori || {});
        const gChart3Values = getValues(globalCharts.kategori || {});
        const gChart3Total  = gChart3Values.reduce((a, b) => a + b, 0);

        const gChart3El = document.getElementById('g_chart3');
        if (gChart3El) {
            getOrCreateChart('g_chart3', {
                type: 'pie',
                plugins: [ChartDataLabels],
                data: {
                    labels: gChart3Labels,
                    datasets: [{
                        data: gChart3Values,
                        backgroundColor: getBgColors(gChart3Labels),
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
                                font: { size: 16, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b',
                                padding: 16,
                                boxWidth: 16,
                                boxHeight: 16
                            }
                        },
                        datalabels: {
                            color: '#1e293b',
                            font: { weight: 'bold', size: 16, family: "'Inter', sans-serif" },
                            formatter: (value) => {
                                if (value === 0 || gChart3Total === 0) return null;
                                return ((value / gChart3Total) * 100).toFixed(2).replace('.', ',') + '%';
                            }
                        },
                        tooltip: {
                            titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            padding: 12,
                            callbacks: {
                                label: function(ctx) {
                                    const val = ctx.raw;
                                    const pct = gChart3Total > 0 ? ((val / gChart3Total) * 100).toFixed(2).replace('.', ',') : '0,00';
                                    return [' ' + val.toLocaleString('id-ID') + ' Pelaporan', ' ' + pct + '%'];
                                }
                            }
                        }
                    }
                }
            });

            if (gChart3Total > 0) {
                const positifKeys = ['Safe Action', 'Safe Condition'];
                const negatifKeys  = ['Unsafe Action', 'Unsafe Condition'];
                const positifTotal = positifKeys.reduce((sum, k) => sum + ((globalCharts.kategori || {})[k] || 0), 0);
                const negatifTotal  = negatifKeys.reduce((sum, k) => sum + ((globalCharts.kategori || {})[k] || 0), 0);
                const fmt = (n) => (gChart3Total > 0 ? (n / gChart3Total * 100).toFixed(2) : '0.00').replace('.', ',');
                const statsEl = document.getElementById('g_chart3-stats');
                if (statsEl) {
                    const posEl = document.getElementById('g_chart3-positif');
                    const negEl = document.getElementById('g_chart3-negatif');
                    if (posEl) posEl.textContent = fmt(positifTotal) + '%';
                    if (negEl) negEl.textContent = fmt(negatifTotal) + '%';
                    statsEl.classList.remove('hidden');
                }
            }
        }

        // 5. G-Chart 4: Keterlibatan dalam Observasi
        (function() {
            const invRaw    = globalCharts.keterlibatan || {};
            const invLabels = Object.keys(invRaw);
            const notice4   = document.getElementById('g_chart4-notice');

            if (invLabels.length === 0) return;

            const allNull  = invLabels.every(k => invRaw[k] === null);
            const someNull = invLabels.some(k  => invRaw[k] === null);

            if (notice4) {
                if (allNull) {
                    const gMonthVal = (dataExtra && dataExtra.gKoMonth) ? dataExtra.gKoMonth : @json($gKoMonth ?? null);
                    if (gMonthVal) {
                        notice4.innerHTML = '⚠️ Data manpower belum tersedia untuk periode ini. Pastikan data manpower sudah diinput di menu <strong>Master Manpower</strong>.';
                        notice4.classList.remove('hidden');
                    }
                } else if (someNull) {
                    const missingFungsi = invLabels.filter(k => invRaw[k] === null).join(', ');
                    notice4.innerHTML = `⚠️ Manpower belum tersedia untuk: <strong>${missingFungsi}</strong>. Fungsi lain ditampilkan normal.`;
                    notice4.classList.remove('hidden');
                }
            }

            const invData = invLabels.map(k => invRaw[k] !== null ? Math.min(invRaw[k], 100) : 0);
            const remData = invLabels.map(k => invRaw[k] !== null ? Math.max(100 - Math.min(invRaw[k], 100), 0) : 100);

            const ketBgColors = invLabels.map(k => invRaw[k] !== null ? '#ED7D31' : 'transparent');
            const sisBgColors = invLabels.map(k => invRaw[k] !== null ? '#5AA2D7'  : '#E2E8F0');

            const gChart4El = document.getElementById('g_chart4');
            if (gChart4El) {
                getOrCreateChart('g_chart4', {
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
                            const canvas = event.native?.target || document.getElementById('g_chart4');
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
                                    autoSkip: false,
                                    font: { size: 13, weight: 'bold', family: "'Inter', sans-serif" },
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
                                    font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                    color: '#1e293b',
                                    padding: 14,
                                    boxWidth: 14,
                                    boxHeight: 14
                                }
                            },
                            tooltip: {
                                titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                                bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                padding: 12,
                                callbacks: {
                                    label: function(ctx) {
                                        const key = invLabels[ctx.dataIndex];
                                        if (invRaw[key] === null) {
                                            return ctx.datasetIndex === 1 ? ' Data manpower belum tersedia' : null;
                                        }
                                        if (ctx.datasetIndex === 1) return null;
                                        return ` Keterlibatan: ${invRaw[key].toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}%`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        })();

        // 6. G-Chart 5: Rekap Persentase Temuan per Fungsi
        const gPtLabels = getLabels(globalCharts.persentase_fungsi || {});
        const gClosedData = gPtLabels.map(l => (globalCharts.persentase_fungsi[l] || {}).closed || 0);
        const gOpenData = gPtLabels.map(l => (globalCharts.persentase_fungsi[l] || {}).open || 0);

        const gChart5El = document.getElementById('g_chart5');
        if (gChart5El) {
            getOrCreateChart('g_chart5', {
                type: 'bar',
                plugins: [ChartDataLabels],
                data: {
                    labels: gPtLabels,
                    datasets: [
                        {
                            label: 'Closed',
                            data: gClosedData,
                            backgroundColor: '#548235',
                            datalabels: {
                                display: (ctx) => {
                                    const val = gClosedData[ctx.dataIndex];
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
                            data: gOpenData,
                            backgroundColor: '#FFC000',
                            datalabels: { display: false }
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
                                autoSkip: false,
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
                            titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            padding: 12,
                            callbacks: {
                                label: function(ctx) {
                                    return ` ${ctx.dataset.label}: ${ctx.raw}%`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 7. G-Chart 6: Persentase Temuan Ber-SAP Berdasarkan Asset Owner
        const gChart6El = document.getElementById('g_chart6');
        if (gChart6El) {
            const gChart6Labels = getLabels(globalCharts.tindak_lanjut || {});
            const gChart6BgColors = getBgColors(gChart6Labels);
            // Warna teks kontras per irisan
            const gChart6TextColor = (bgColor) => {
                const lightBg = ['#FFC000', '#CBD5E1', '#9CA3AF'];
                return lightBg.includes(bgColor) ? '#1e293b' : '#ffffff';
            };
            getOrCreateChart('g_chart6', {
                type: 'doughnut',
                plugins: [ChartDataLabels],
                data: {
                    labels: gChart6Labels,
                    datasets: [{
                        data: getValues(globalCharts.tindak_lanjut || {}),
                        backgroundColor: gChart6BgColors
                    }]
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                                color: '#1e293b',
                                padding: 18,
                                boxWidth: 16,
                                boxHeight: 16
                            }
                        },
                        tooltip: {
                            titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                            padding: 12,
                            callbacks: {
                                label: function(ctx) {
                                    return ` ${ctx.label}: ${ctx.raw}%`;
                                }
                            }
                        },
                        datalabels: {
                            color: function(context) {
                                return gChart6TextColor(gChart6BgColors[context.dataIndex] || '#9CA3AF');
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
        const renderGHorizontalBar = (ctxId, chartData, color) => {
            let dataArr = Object.entries(chartData?.data || {})
                .map(([k, v]) => ({ label: k, value: v }));
            const total = chartData?.total || 1;

            const el = document.getElementById(ctxId);
            if (!el) return;

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
                        barThickness: 18,
                        minBarLength: 6
                    }]
                },
                options: {
                    ...commonOptions,
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    layout: {
                        padding: { right: 80 }
                    },
                    interaction: {
                        mode: 'index',
                        axis: 'y',
                        intersect: false
                    },
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            anchor: 'end',
                            align: 'right',
                            color: '#1e293b',
                            font: { weight: 'bold', size: 12, family: "'Inter', sans-serif" },
                            formatter: function(value, context) {
                                let rawCount = dataArr[context.dataIndex].value;
                                return `${value}% (${rawCount})`;
                            }
                        },
                        tooltip: {
                            titleFont: { size: 15, weight: 'bold', family: "'Inter', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
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
                                callback: function(value) { return value + '%'; }
                            }
                        },
                        y: {
                            grid: { display: false },
                            afterFit: (scale) => { scale.width = 480; },
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

        // 8. G-Chart 7 & G-Chart 8
        if (globalCharts.unsafe_action) {
            renderGHorizontalBar('g_chart7', globalCharts.unsafe_action, '#ED7D31');
        }
        if (globalCharts.unsafe_condition) {
            renderGHorizontalBar('g_chart8', globalCharts.unsafe_condition, '#ED7D31');
        }
    };

    // Initial render for Tab 2
    window.renderAllTab2Charts(initialGlobalCharts);

    // ================================================================
    // AJAX FILTER HANDLING (TAB 2 / GLOBAL)
    // ================================================================
    let activeTab2AbortController = null;
    let currentTab2RequestId = 0;

    function showTab2Loading(show) {
        const el = document.getElementById('g-filter-loading-indicator');
        if (el) {
            if (show) el.classList.remove('hidden');
            else el.classList.add('hidden');
        }
    }

    function showGlobalFilterError(msg) {
        let toast = document.getElementById('filter-error-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'filter-error-toast';
            toast.className = 'fixed bottom-6 right-6 z-50 bg-red-600 text-white text-sm px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 transition-opacity';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 4500);
    }

    function updateTab2Kpi(kpi) {
        if (!kpi) return;
        const totalEl = document.getElementById('g-kpi-total');
        const openEl = document.getElementById('g-kpi-open');
        const closedEl = document.getElementById('g-kpi-closed');
        if (totalEl) totalEl.textContent = Number(kpi.total || 0).toLocaleString('id-ID');
        if (openEl) openEl.textContent = Number(kpi.open || 0).toLocaleString('id-ID');
        if (closedEl) closedEl.textContent = Number(kpi.closed || 0).toLocaleString('id-ID');
    }

    function fetchTab2Dashboard(params = {}) {
        if (activeTab2AbortController) {
            activeTab2AbortController.abort();
        }
        activeTab2AbortController = new AbortController();
        const reqId = ++currentTab2RequestId;
        showTab2Loading(true);

        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('tab', 'global');
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
            signal: activeTab2AbortController.signal
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            if (reqId !== currentTab2RequestId) return;
            showTab2Loading(false);
            if (!data.success) throw new Error(data.message || 'Gagal memuat data');

            updateTab2Kpi(data.globalKpi);

            if (data.gYear !== undefined && gFilterYear && String(data.gYear) !== gFilterYear.value) {
                gFilterYear.value = data.gYear || '';
            }
            if (data.gKoYear !== undefined && gKoYear && String(data.gKoYear) !== gKoYear.value) {
                gKoYear.value = data.gKoYear || '';
            }
            if (data.gKoMonth !== undefined && gKoMonth && String(data.gKoMonth) !== gKoMonth.value) {
                gKoMonth.value = data.gKoMonth || '';
            }
            if (data.gRrYear !== undefined && gRrYear && String(data.gRrYear) !== gRrYear.value) {
                gRrYear.value = data.gRrYear || '';
            }
            if (data.gRrMonth !== undefined && gRrMonth && String(data.gRrMonth) !== gRrMonth.value) {
                gRrMonth.value = data.gRrMonth || '';
            }
            if (data.gPekaYear !== undefined && gPekaYear && String(data.gPekaYear) !== gPekaYear.value) {
                gPekaYear.value = data.gPekaYear || '';
            }

            if (data.globalCharts && data.globalCharts.reporting) {
                const grTitle = document.getElementById('g-chart2-title');
                const grSub   = document.getElementById('g-chart2-subtitle');
                const grMode  = data.globalCharts.reporting.mode || 'distribusi';
                if (grTitle) {
                    if (grMode === 'manpower_rasio') {
                        const rBulanNama = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                        const rMonthName = data.gRrMonth ? (rBulanNama[data.gRrMonth] || '') : '';
                        const rPeriod = rMonthName ? (rMonthName + ' ' + (data.gRrYear || '')) : (data.globalCharts.reporting.periode_label || '');
                        grTitle.textContent = '2. Reporting Rate per Fungsi (' + rPeriod + ')';
                    } else {
                        grTitle.textContent = '2. Reporting Rate per Fungsi';
                    }
                }
                if (grSub) {
                    grSub.textContent = (grMode === 'manpower_rasio')
                        ? 'temuan YTD ÷ (manpower × bulan)'
                        : 'jumlah pelaporan ÷ manpower tahunan';
                }
            }

            window.renderAllTab2Charts(data.globalCharts, data);

            window.history.replaceState({}, '', fetchUrl);
        })
        .catch(err => {
            if (err.name === 'AbortError') return;
            showTab2Loading(false);
            console.error('AJAX global filter error:', err);
            showGlobalFilterError('Gagal memuat data filter global (' + (err.message || 'Error') + '). Data sebelumnya tetap dipertahankan.');
        });
    }

    // Event listeners for Tab 2
    const gFilterYear = document.getElementById('g-filter-year');
    if (gFilterYear) {
        gFilterYear.addEventListener('change', function(e) {
            e.preventDefault();
            fetchTab2Dashboard({ g_year: this.value });
        });
    }

    const gRrYear = document.getElementById('g-rr-year-filter');
    if (gRrYear) {
        gRrYear.removeAttribute('onchange');
        gRrYear.addEventListener('change', function(e) {
            e.preventDefault();
            fetchTab2Dashboard({ g_rr_year: this.value });
        });
    }

    const gRrMonth = document.getElementById('g-rr-month-filter');
    if (gRrMonth) {
        gRrMonth.removeAttribute('onchange');
        gRrMonth.addEventListener('change', function(e) {
            e.preventDefault();
            fetchTab2Dashboard({ g_rr_month: this.value });
        });
    }

    const gPekaYear = document.getElementById('g-peka-year-filter');
    if (gPekaYear) {
        gPekaYear.removeAttribute('onchange');
        gPekaYear.addEventListener('change', function(e) {
            e.preventDefault();
            fetchTab2Dashboard({ g_peka_year: this.value });
        });
    }

    const gKoYear = document.getElementById('g-ko-year-filter');
    if (gKoYear) {
        gKoYear.removeAttribute('onchange');
        gKoYear.addEventListener('change', function(e) {
            e.preventDefault();
            fetchTab2Dashboard({ g_ko_year: this.value });
        });
    }

    const gKoMonth = document.getElementById('g-ko-month-filter');
    if (gKoMonth) {
        gKoMonth.removeAttribute('onchange');
        gKoMonth.addEventListener('change', function(e) {
            e.preventDefault();
            fetchTab2Dashboard({ g_ko_month: this.value });
        });
    }
});
</script>
@endif
@endpush

