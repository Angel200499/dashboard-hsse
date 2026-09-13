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
                     FILTER TAHUN + BULAN (untuk KPI, Chart, Reporting Rate)
                ================================================ --}}
                @php
                    $bulanNamaList = [
                        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
                        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
                        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
                    ];
                    $tahunList = range(now()->year + 1, 2020);
                @endphp

                <select name="year" onchange="document.getElementById('month-filter').value=''; this.form.submit()"
                    class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm">
                    <option value="">Semua Tahun</option>
                    @foreach($tahunList as $yr)
                        <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>

                <select name="month" id="month-filter" onchange="this.form.submit()"
                    class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm {{ !$selectedYear ? 'opacity-50' : '' }}"
                    {{ !$selectedYear ? 'disabled' : '' }}>
                    <option value="">Semua Bulan</option>
                    @foreach($bulanNamaList as $num => $nama)
                        <option value="{{ $num }}" {{ ($selectedMonth ?? null) == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>

                @if($selectedYear || $selectedMonth ?? false)
                    <a href="{{ request()->url() }}" class="text-xs text-slate-500 hover:text-slate-700 underline whitespace-nowrap">Reset Filter</a>
                @endif
            </form>
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
            <h3 class="text-base font-bold text-slate-800 mb-4">1. Jumlah Pelaporan per Fungsi</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart1"></canvas>
            </div>
        </div>

        <!-- Chart 2: Reporting Rate per Fungsi (Horizontal Bar) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            @php
                $rrMode   = $charts['reporting']['mode'] ?? 'distribusi';
                $rrLabel  = $rrMode === 'manpower_rasio'
                    ? '2. Reporting Rate (' . ($charts['reporting']['periode_label'] ?? '') . ')'
                    : '2. Reporting Rate per Fungsi';
                $rrData   = $charts['reporting']['data'] ?? [];
                $hasNull  = in_array(null, $rrData, true);
            @endphp
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-bold text-slate-800">{{ $rrLabel }}</h3>
                @if($rrMode === 'manpower_rasio')
                    <span class="text-xs font-mono text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">temuan YTD ÷ (manpower × bulan) × 100</span>
                @endif
            </div>
            @if($rrMode === 'manpower_rasio' && $hasNull)
                <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-1.5 mb-2">
                    ⚠️ Data manpower untuk periode ini belum tersedia. Reporting Rate belum dapat dihitung.
                </p>
            @elseif($rrMode === 'distribusi' && ($selectedMonth ?? null))
                <p class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 mb-2">
                    ℹ️ Pilih Tahun terlebih dahulu untuk mengaktifkan Reporting Rate berbasis manpower bulanan.
                </p>
            @endif
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart2"></canvas>
            </div>
        </div>

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
            <h3 class="text-base font-bold text-slate-800 mb-1">4. Keterlibatan dalam Observasi</h3>
            <p class="text-xs text-slate-400 mb-3">
                Rumus: Pelapor unik bulan terpilih &divide; Manpower bulan terpilih &times; 100%
            </p>
            <!-- Notice: muncul jika manpower tidak tersedia -->
            <div id="chart4-notice" class="hidden mb-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                ⚠️ Data manpower belum tersedia untuk periode ini. Pilih Tahun dan Bulan, lalu pastikan data manpower sudah diinput.
            </div>
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
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-base font-bold text-slate-800 mb-4">7. Unsafe Action Category</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart7"></canvas>
            </div>
        </div>

        <!-- Chart 8: Unsafe Condition (Horizontal Bar) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-base font-bold text-slate-800 mb-4">8. Unsafe Condition Category</h3>
            <div class="flex-1 relative w-full h-full">
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
                        function sortUrl($column) {
                            $currentSortBy = request('sort_by');
                            $currentSortDir = request('sort_dir', 'asc');
                            $newDir = ($currentSortBy === $column && $currentSortDir === 'asc') ? 'desc' : 'asc';
                            return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $newDir]);
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

        if (rrLabels.length > 0) {
            new Chart(document.getElementById('chart2'), {
                type: 'bar',
                data: { 
                    labels: rrLabels, 
                    datasets: [{ data: rrValues, backgroundColor: rrBgColors, borderRadius: 4 }] 
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

        // 4. Stacked Vertical Bar - Keterlibatan
        // Data berupa [fungsi => float|null]. null = manpower tidak tersedia.
        const invRaw    = charts.keterlibatan;
        const invLabels = Object.keys(invRaw);
        const invData   = invLabels.map(k => invRaw[k] !== null ? Math.min(invRaw[k], 100) : 0);
        const remData   = invLabels.map(k => invRaw[k] !== null ? Math.max(100 - invRaw[k], 0) : 100);

        const allNull   = invLabels.every(k => invRaw[k] === null);
        const someNull  = invLabels.some(k => invRaw[k] === null);
        const notice4   = document.getElementById('chart4-notice');

        if (allNull) {
            if (notice4) notice4.classList.remove('hidden');
        } else {
            if (notice4 && someNull) {
                notice4.textContent = '⚠️ Beberapa fungsi tidak memiliki data manpower untuk periode ini.';
                notice4.classList.remove('hidden');
            }

            new Chart(document.getElementById('chart4'), {
                type: 'bar',
                plugins: [ChartDataLabels],
                data: {
                    labels: invLabels,
                    datasets: [
                        {
                            label: 'Keterlibatan',
                            data: invData,
                            backgroundColor: '#ED7D31',
                            datalabels: {
                                color: '#fff',
                                anchor: 'center',
                                align: 'center',
                                font: { weight: 'bold', size: 12 },
                                formatter: (value, ctx) => {
                                    const key = invLabels[ctx.dataIndex];
                                    if (invRaw[key] === null) return null;
                                    if (value === 0) return null;
                                    return invRaw[key].toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '%';
                                }
                            }
                        },
                        {
                            label: 'Sisa',
                            data: remData,
                            backgroundColor: '#5AA2D7',
                            datalabels: { display: false }
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, max: 100, ticks: { callback: v => v + '%' } }
                    },
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const key = invLabels[ctx.dataIndex];
                                    if (invRaw[key] === null) return ' Data manpower belum tersedia';
                                    if (ctx.datasetIndex === 1) return null;
                                    return ` Keterlibatan: ${invRaw[key].toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2})}%`;
                                }
                            }
                        }
                    }
                }
            });
        }

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

        // Helper for Horizontal Bar charts with Data Labels
        const renderHorizontalBar = (ctxId, chartData, color) => {
            const dataArr = Object.entries(chartData.data).map(([k, v]) => ({ label: k, value: v }));
            
            new Chart(document.getElementById(ctxId), {
                type: 'bar',
                data: { 
                    labels: dataArr.map(d => d.label), 
                    datasets: [{ data: dataArr.map(d => d.value), backgroundColor: color }] 
                },
                options: { 
                    ...commonOptions, 
                    indexAxis: 'y', 
                    plugins: { legend: { display: false } }, 
                    scales: { x: { beginAtZero: true, grid: { display: false } }, y: { grid: { display: false } } }
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
