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

    <!-- ================================================================
         FILTER BAR — Tahun + Bulan (berlaku untuk SEMUA ROLE)
    ================================================================ -->
    @php
        $bulanNamaList = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
        ];
        $tahunList = range(now()->year + 1, 2020);
    @endphp
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm px-5 py-4">
        <form action="" method="GET" class="flex flex-wrap items-center gap-3" id="global-filter-form">
            <span class="text-sm font-semibold text-slate-500 mr-1">Filter:</span>

            <select name="year" id="year-filter"
                onchange="document.getElementById('month-filter').value=''; this.form.submit()"
                class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm">
                <option value="">Semua Tahun</option>
                @foreach($tahunList as $yr)
                    <option value="{{ $yr }}" {{ ($selectedYear ?? null) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                @endforeach
            </select>

            <select name="month" id="month-filter"
                onchange="this.form.submit()"
                class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm {{ !($selectedYear ?? null) ? 'opacity-50' : '' }}"
                {{ !($selectedYear ?? null) ? 'disabled' : '' }}>
                <option value="">Semua Bulan</option>
                @foreach($bulanNamaList as $num => $nama)
                    <option value="{{ $num }}" {{ ($selectedMonth ?? null) == $num ? 'selected' : '' }}>{{ $nama }}</option>
                @endforeach
            </select>

            @if(($selectedYear ?? null) || ($selectedMonth ?? null))
                <a href="{{ route('dashboard') }}" class="text-xs text-slate-500 hover:text-slate-700 underline whitespace-nowrap">Reset Filter</a>
            @endif

            @if($selectedYear ?? null)
                <span class="text-xs text-slate-400 ml-2">
                    Menampilkan data tahun {{ $selectedYear }}
                    @if($selectedMonth ?? null)
                        — Reporting Rate: Januari–{{ $bulanNamaList[$selectedMonth] }}
                    @endif
                </span>
            @endif
        </form>
    </div>

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
                                $pct = $fi['total'] > 0 ? round($jumlah / $fi['total'] * 100, 1) : 0;
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
             CHART 2a — REPORTING RATE AREA LHD (NEW — PR 1)
             Ditempatkan DI ATAS Reporting Rate per Fungsi
        ================================================================ --}}
        @php
            $lhd = $charts['reporting_lhd'] ?? ['aktif' => false];
        @endphp
        <div class="lg:col-span-2 bg-gradient-to-br from-[#002060] to-[#003090] rounded-2xl border border-[#001540] shadow-[0_4px_20px_-4px_rgba(0,32,96,0.4)] overflow-hidden">
            <div class="px-6 py-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Judul & info -->
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-blue-300 mb-1">Area LHD — Seluruh Fungsi</p>
                        <h3 class="text-xl font-bold text-white">Reporting Rate Area LHD</h3>
                        @if($lhd['aktif'] && ($selectedMonth ?? null))
                            <p class="text-sm text-blue-200 mt-1">Periode: {{ $lhd['periode_label'] ?? '' }}</p>
                        @elseif(!($selectedYear ?? null))
                            <p class="text-sm text-blue-300 mt-1">Pilih Tahun dan Bulan untuk melihat Reporting Rate.</p>
                        @elseif(!($selectedMonth ?? null))
                            <p class="text-sm text-blue-300 mt-1">Pilih Bulan untuk menghitung Reporting Rate YTD.</p>
                        @endif
                    </div>

                    <!-- Nilai Rate -->
                    <div class="flex items-center gap-6">
                        @if($lhd['aktif'])
                            @if($lhd['tersedia'] && $lhd['rate'] !== null)
                                <div class="text-center">
                                    <p class="text-5xl font-black text-white">{{ number_format($lhd['rate'], 2) }}<span class="text-2xl font-bold text-blue-300">%</span></p>
                                    <p class="text-xs text-blue-300 mt-1">Reporting Rate</p>
                                </div>
                                <div class="hidden md:block h-16 w-px bg-blue-700"></div>
                                <div class="grid grid-cols-3 gap-4 text-center">
                                    <div>
                                        <p class="text-2xl font-bold text-white">{{ number_format($lhd['total_temuan']) }}</p>
                                        <p class="text-xs text-blue-300">Total Temuan YTD</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-white">{{ number_format($lhd['total_manpower']) }}</p>
                                        <p class="text-xs text-blue-300">Total Manpower</p>
                                    </div>
                                    <div>
                                        <p class="text-2xl font-bold text-white">{{ $lhd['jumlah_bulan'] }}</p>
                                        <p class="text-xs text-blue-300">Bulan</p>
                                    </div>
                                </div>
                                <div class="hidden md:block">
                                    <p class="text-xs text-blue-300 font-mono bg-blue-900/50 rounded-lg px-3 py-2 whitespace-nowrap">
                                        {{ number_format($lhd['total_temuan']) }} ÷ ({{ number_format($lhd['total_manpower']) }} × {{ $lhd['jumlah_bulan'] }}) × 100
                                    </p>
                                </div>
                            @else
                                <div class="bg-amber-500/20 border border-amber-400/30 rounded-xl px-5 py-3">
                                    <p class="text-amber-300 font-semibold text-sm">⚠️ Data manpower untuk periode ini belum tersedia.</p>
                                    <p class="text-amber-200 text-xs mt-1">Reporting Rate belum dapat dihitung. Pastikan data manpower sudah diinput untuk bulan yang dipilih.</p>
                                </div>
                            @endif
                        @else
                            <div class="text-center text-blue-300">
                                <svg class="w-10 h-10 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm">Pilih <strong>Tahun</strong> dan <strong>Bulan</strong> di atas untuk menampilkan Reporting Rate Area LHD.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 2b: Reporting Rate per Fungsi (Horizontal Bar) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[380px]">
            @php
                $rrMode   = $charts['reporting']['mode'] ?? 'distribusi';
                $rrLabel  = $rrMode === 'manpower_rasio'
                    ? '2. Reporting Rate per Fungsi (' . ($charts['reporting']['periode_label'] ?? '') . ')'
                    : '2. Reporting Rate per Fungsi';
                $rrData   = $charts['reporting']['data'] ?? [];
                $hasNull  = in_array(null, $rrData, true);
            @endphp
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-800">{{ $rrLabel }}</h3>
                @if($rrMode === 'manpower_rasio')
                    <span class="text-xs font-mono text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">temuan YTD ÷ (manpower × bulan) × 100</span>
                @endif
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
             CHART TRENDING TEMUAN (NEW — PR 3) — full width
        ================================================================ --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[380px]">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Trending Temuan{{ ($selectedYear ?? null) ? ' ' . $selectedYear : '' }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Jumlah temuan per bulan sepanjang tahun {{ ($selectedYear ?? null) ? $selectedYear : 'yang dipilih' }}. Dropdown bulan tidak memengaruhi grafik ini.</p>
                </div>
                @if(!($selectedYear ?? null))
                    <span class="text-xs text-slate-500 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">Pilih tahun untuk melihat trending</span>
                @endif
            </div>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chartTrending"></canvas>
            </div>
        </div>

        <!-- Chart 3: Kategori PEKA (Pie) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-base font-bold text-slate-800 mb-4">3. Kategori PEKA</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart3"></canvas>
            </div>
        </div>

        <!-- Chart 4: Keterlibatan Observasi (Stacked Vertical Bar) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-base font-bold text-slate-800 mb-4">4. Keterlibatan dalam Observasi</h3>
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

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            'Tindakan aman': '#5AA2D7',
            'Kondisi aman': '#ED7D31',
            'Tindakan tidak aman': '#A5A5A5',
            'Kondisi tidak aman': '#FFC000',
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
        // Hanya tampilkan fungsi yang bukan AREA LHD (LHD sudah di card terpisah)
        // dan yang memiliki data (bukan null)
        const rrAllData = charts.reporting.data || {};
        const rrMode    = charts.reporting.mode || 'distribusi';
        
        // Filter: exclude AREA LHD dari chart2 (sudah ditampilkan di card LHD)
        // Untuk mode manpower_rasio, null = data manpower tidak tersedia
        const rrLabels = Object.keys(rrAllData).filter(k => k !== 'AREA LHD' && rrAllData[k] !== null);
        const rrValues = rrLabels.map(k => rrAllData[k]);
        
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
                                        ? ` ${val}% (temuan YTD ÷ manpower × bulan × 100)`
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
                                    var label = rrMode === 'manpower_rasio' ? data + '%' : data;
                                    ctx.fillText(label, bar.x + 5, bar.y);
                                });
                            });
                        }
                    }
                }
            });
        }

        // ================================================================
        // CHART TRENDING TEMUAN (NEW — PR 3)
        // Selalu 12 bulan, hanya mengikuti filter TAHUN (bukan bulan)
        // ================================================================
        const trendingData = charts.trending || {};
        const bulanLabels  = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        // trendingData adalah {1: count, 2: count, ..., 12: count}
        const trendingValues = bulanLabels.map((_, i) => trendingData[i + 1] || 0);

        new Chart(document.getElementById('chartTrending'), {
            type: 'line',
            data: {
                labels: bulanLabels,
                datasets: [{
                    label: 'Jumlah Temuan',
                    data: trendingValues,
                    borderColor: '#5AA2D7',
                    backgroundColor: 'rgba(90, 162, 215, 0.1)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#5AA2D7',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.35,
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.raw.toLocaleString('id-ID')} temuan`
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            callback: (v) => v.toLocaleString('id-ID')
                        }
                    }
                }
            }
        });

        // 3. Pie Chart - Kategori PEKA
        new Chart(document.getElementById('chart3'), {
            type: 'pie',
            data: { 
                labels: getLabels(charts.kategori), 
                datasets: [{ 
                    data: getValues(charts.kategori), 
                    backgroundColor: getBgColors(getLabels(charts.kategori)) 
                }] 
            },
            options: { ...commonOptions, plugins: { legend: { position: 'right' } } }
        });

        // 4. Stacked Vertical Bar - Keterlibatan
        const invLabels = getLabels(charts.keterlibatan);
        const invData = getValues(charts.keterlibatan);
        const remData = invData.map(v => 100 - v);
        
        new Chart(document.getElementById('chart4'), {
            type: 'bar',
            data: {
                labels: invLabels,
                datasets: [
                    { label: 'Keterlibatan', data: invData, backgroundColor: '#ED7D31' },
                    { label: 'Jumlah', data: remData, backgroundColor: '#5AA2D7' }
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