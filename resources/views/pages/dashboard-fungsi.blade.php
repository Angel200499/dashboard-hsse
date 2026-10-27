@extends('layouts.app')

@section('title', 'Dashboard Fungsi - ' . $fungsi)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard Fungsi: {{ $fungsi }}</h1>
            <p class="text-sm text-slate-500 mt-1">Ringkasan metrik dan daftar temuan lapangan untuk fungsi {{ $fungsi }}.</p>
        </div>

        <div class="flex items-center gap-3">
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
                    $tahunList = range(now()->year + 1, 2020);
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

                <select name="year" onchange="this.form.submit()"
                    class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $yr)
                        <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>

                @if($selectedYear || request('search') || request('status_filter') || request('date_filter'))
                    @php
                        $resetHeaderQuery = request()->only(['rr_year', 'rr_month', 'ko_year', 'ko_month']);
                    @endphp
                    <a href="{{ request()->url() }}{{ !empty($resetHeaderQuery) ? '?' . http_build_query($resetHeaderQuery) : '' }}" class="text-xs text-slate-500 hover:text-slate-700 underline whitespace-nowrap">Reset Filter</a>
                @endif
            </form>

            {{-- Executive Summary PDF Button — selalu Global, tersedia untuk semua role --}}
            @include('components.executive-summary-btn', [
                'selectedYear'   => $selectedYear,
                'selectedMonth'  => $selectedMonth,
                'maxBulanSipeka' => $maxBulanSipeka,
            ])
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1: Total Temuan -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Total Temuan</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['total'] ?? 0) }}</h3>
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
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['open'] ?? 0) }}</h3>
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
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['closed'] ?? 0) }}</h3>
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
                    <h3 class="text-[22px] font-bold text-slate-800">{{ $rrLabel }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5 font-mono">
                        @if($rrMode === 'manpower_rasio')
                            temuan YTD ÷ (manpower × bulan)
                        @else
                            jumlah pelaporan ÷ manpower tahunan
                        @endif
                    </p>
                </div>

                {{-- Filter Khusus Card 2: Tahun & Bulan Mandiri --}}
                <form action="" method="GET" class="flex flex-wrap items-center gap-2">
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
                        onchange="this.form.submit()"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
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
                        onchange="this.form.submit()"
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

            {{-- Notice State Server-side --}}
            @if($rrStatus === 'no_sipeka')
                <div class="mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    ⚠️ Data temuan SIPEKA untuk fungsi <strong>{{ $fungsi }}</strong> pada periode <strong>{{ ($rrMonth && isset($bulanNamaList[$rrMonth]) ? $bulanNamaList[$rrMonth] . ' ' : '') . $rrYear }}</strong> belum tersedia.
                </div>
            @elseif($rrStatus === 'no_manpower')
                <div class="mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    ⚠️ Data manpower untuk fungsi <strong>{{ $fungsi }}</strong> pada periode <strong>{{ ($rrMonth && isset($bulanNamaList[$rrMonth]) ? $bulanNamaList[$rrMonth] . ' ' : '') . $rrYear }}</strong> belum tersedia. Reporting Rate belum dapat dihitung.
                </div>
            @endif

            @if($rrStatus === 'no_sipeka' || $rrStatus === 'no_manpower')
                <div class="flex-1 flex flex-col items-center justify-center p-6 text-center text-slate-400 min-h-[220px]">
                    <svg class="w-12 h-12 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-sm font-medium text-slate-500">Grafik tidak dapat ditampilkan</p>
                    <p class="text-xs text-slate-400 mt-1">
                        @if($rrStatus === 'no_sipeka')
                            Data temuan SIPEKA untuk periode ini belum tersedia.
                        @else
                            Data manpower untuk periode ini belum tersedia.
                        @endif
                    </p>
                </div>
            @else
                <div class="flex-1 relative w-full h-full min-h-0">
                    <canvas id="chart2"></canvas>
                </div>
            @endif
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
                        <span class="inline-flex items-center gap-1 text-[11px] text-blue-600 font-medium bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100" title="Klik bar segmen Belum Melapor untuk melihat daftar personil">
                            <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                            Klik bar untuk daftar personil
                        </span>
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
                <form action="" method="GET" class="flex flex-wrap items-center gap-2">
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
                        onchange="this.form.submit()"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-[#9DBF2A] focus:border-[#9DBF2A] py-1.5 px-2.5 shadow-sm">
                        @foreach($tahunList as $yr)
                            <option value="{{ $yr }}" {{ $koYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>

                    <select name="ko_month" id="ko-month-filter"
                        onchange="this.form.submit()"
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

        <!-- Chart 6: Persentase Penindak Lanjut (Donut) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-[22px] font-bold text-slate-800 mb-4">6. Rekap Persentase Penindak Lanjut (By SAP)</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart6"></canvas>
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
</div>

{{-- Modal Daftar Belum Melapor --}}
@include('components.unreported-modal')

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
            'Tidak ada temuan': '#94a3b8',
        };

        // Helper to get array of values and labels from assoc array
        const getLabels = (obj) => Object.keys(obj);
        const getValues = (obj) => Object.values(obj);
        const getBgColors = (keys) => keys.map(k => colors[k] || '#9CA3AF');

        // 1. Pie Chart - % PEKA Per Fungsi
        const chart1Labels = getLabels(charts.fungsi);
        const chart1Values = getValues(charts.fungsi);
        const chart1Total = chart1Values.reduce((a, b) => a + b, 0);

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

        const chart2El = document.getElementById('chart2');
        if (chart2El && rrLabels.length > 0) {
            new Chart(chart2El, {
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

        // 3. Pie Chart - Kategori PEKA
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

            const koStatus = @json($koStatus ?? 'ok');
            const isOk     = koStatus === 'ok';

            // ---- Siapkan data chart -----------------------------------------
            const invData = invLabels.map(k => (isOk && invRaw[k] !== null) ? Math.min(invRaw[k], 100) : 0);
            const remData = invLabels.map(k => (isOk && invRaw[k] !== null) ? Math.max(100 - Math.min(invRaw[k], 100), 0) : 100);

            // Warna: fungsi dengan data → normal; fungsi null / error → abu-abu
            const ketBgColors = invLabels.map(k => (isOk && invRaw[k] !== null) ? '#ED7D31' : 'transparent');
            const sisBgColors = invLabels.map(k => (isOk && invRaw[k] !== null) ? '#5AA2D7'  : '#E2E8F0');

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

                        if (typeof window.openUnreportedModal === 'function') {
                            window.openUnreportedModal(key);
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
        
        new Chart(document.getElementById('chart5'), {
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
                        padding: 12
                    }
                } 
            }
        });

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
            
            new Chart(document.getElementById(ctxId), {
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
    });
</script>
@endpush

