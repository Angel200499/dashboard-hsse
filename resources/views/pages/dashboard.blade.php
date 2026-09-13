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
     CHART 2 — REPORTING RATE per FUNGSI (termasuk AREA LHD sebagai bar pertama)
        ================================================================ --}}

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
                    <span class="text-xs font-mono text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">temuan YTD ÷ (manpower × bulan)</span>
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
             CHART REKAP PEKA BULANAN (Trending Temuan — revisi)
             Full width, hanya mengikuti filter TAHUN
        ================================================================ --}}
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col" style="min-height:500px">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-4 flex-shrink-0">
                <div>
                    <h3 class="text-base font-bold text-slate-800">
                        Rekap PEKA{{ ($selectedYear ?? null) ? ' Tahun ' . $selectedYear : '' }}
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Distribusi laporan PEKA per bulan sepanjang tahun{{ ($selectedYear ?? null) ? ' ' . $selectedYear : ' yang dipilih' }}.
                        Dropdown bulan tidak memengaruhi grafik ini.
                    </p>
                </div>
                @if(!($selectedYear ?? null))
                    <span class="text-xs text-slate-500 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">
                        Pilih tahun untuk melihat Rekap PEKA
                    </span>
                @endif
            </div>

            {{-- Body: Chart + Closing Rate Card --}}
            <div class="flex gap-4 flex-1 min-h-0">

                {{-- Chart Canvas Area --}}
                <div class="flex-1 relative min-h-0" style="min-height:280px">
                    <canvas id="chartTrending"></canvas>
                </div>

                {{-- Closing Rate Card --}}
                <div class="flex-shrink-0 w-40 flex flex-col gap-3">
                    <div id="closing-rate-card"
                         class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-2xl p-4 flex flex-col items-center justify-center text-center shadow-sm flex-1">
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-2">Closing Rate</p>
                        <p id="closing-rate-value"
                           class="text-3xl font-bold text-blue-700 leading-tight">—</p>
                        <p class="text-xs text-blue-500 mt-1">dari total laporan</p>
                        <div class="mt-3 pt-3 border-t border-blue-200 w-full text-center">
                            <p id="closing-rate-closed" class="text-sm font-bold text-slate-700">—</p>
                            <p class="text-xs text-slate-500">Closed</p>
                            <p id="closing-rate-total" class="text-sm font-bold text-slate-700 mt-1">—</p>
                            <p class="text-xs text-slate-500">Total Laporan</p>
                        </div>
                    </div>
                </div>

            </div>{{-- end Body --}}

            {{-- Summary Text --}}
            <div class="flex-shrink-0 mt-4 pt-3 border-t border-slate-100">
                <p id="trending-summary" class="text-xs text-slate-500 leading-relaxed">
                    {{-- diisi via JavaScript --}}
                    @if(!($selectedYear ?? null))
                        Pilih tahun untuk melihat rekap PEKA.
                    @endif
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
            <h3 class="text-base font-bold text-slate-800 mb-1">4. Keterlibatan dalam Observasi</h3>
            <p class="text-xs text-slate-400 mb-3">
                Rumus: Pelapor unik Jan s/d bulan terpilih &divide; Manpower bulan terpilih &times; 100%
            </p>

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

            // ---- Render Chart.js (mixed: bar + line) ---------------------
            const trendCanvas = document.getElementById('chartTrending');
            if (trendCanvas) {
                new Chart(trendCanvas, {
                    type: 'bar',
                    data: {
                        labels: bulanLabels,
                        datasets: [
                            // ---- 4 Stacked Bar ----
                            {
                                label: 'Safe Action',
                                type: 'bar',
                                data: saData,
                                backgroundColor: '#22c55e',
                                stack: 'peka',
                                borderRadius: { topLeft: 0, topRight: 0 },
                                order: 2,
                            },
                            {
                                label: 'Safe Condition',
                                type: 'bar',
                                data: scData,
                                backgroundColor: '#86efac',
                                stack: 'peka',
                                order: 2,
                            },
                            {
                                label: 'Unsafe Action',
                                type: 'bar',
                                data: uaData,
                                backgroundColor: '#f97316',
                                stack: 'peka',
                                order: 2,
                            },
                            {
                                label: 'Unsafe Condition',
                                type: 'bar',
                                data: ucData,
                                backgroundColor: '#ef4444',
                                stack: 'peka',
                                borderRadius: { topLeft: 3, topRight: 3 },
                                order: 2,
                            },
                            // ---- Total Line ----
                            {
                                label: 'Total',
                                type: 'line',
                                data: totData,
                                borderColor: '#5AA2D7',
                                backgroundColor: 'rgba(90, 162, 215, 0.08)',
                                borderWidth: 2.5,
                                pointBackgroundColor: '#5AA2D7',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                fill: false,
                                tension: 0.3,
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
                                grid: { display: false },
                                ticks: { font: { size: 11 } },
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: {
                                    callback: (v) => v.toLocaleString('id-ID'),
                                    font: { size: 11 },
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 14,
                                    usePointStyle: true,
                                    pointStyleWidth: 10,
                                    font: { size: 11 },
                                },
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
            }

            // ---- Update Closing Rate Card ---------------------------------
            const fmt   = (n) => n.toLocaleString('id-ID');
            const fmtPct = (r) => r.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            const crVal   = document.getElementById('closing-rate-value');
            const crClose = document.getElementById('closing-rate-closed');
            const crTotal = document.getElementById('closing-rate-total');

            if (crVal)   crVal.textContent   = annualTotal > 0 ? fmtPct(closingRate) + '%' : '—';
            if (crClose) crClose.textContent = annualTotal > 0 ? fmt(closedTotal)            : '—';
            if (crTotal) crTotal.textContent = annualTotal > 0 ? fmt(annualTotal)             : '—';

            // ---- Update Summary Text --------------------------------------
            const summaryEl = document.getElementById('trending-summary');
            if (summaryEl && annualTotal > 0) {
                const pctStr   = fmtPct(closingRate).replace('.', ',');
                const totalStr = fmt(annualTotal);
                const closStr  = fmt(closedTotal);
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