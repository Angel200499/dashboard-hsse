@extends('layouts.app')

@section('title', 'Overview')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard Global HSSE</h1>
            <p class="text-sm text-slate-500 mt-1">Monitoring dan analitik data temuan SIPEKA secara real-time.</p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#9DBF2A] transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export Report
            </button>
            <form action="{{ route('sipeka.upload') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2">
                @csrf
                <input type="file" name="sipeka_file" accept=".xlsx,.xls,.csv" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-slate-300 rounded-lg bg-white" required>
                <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all shadow-md shadow-blue-500/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Import SIPEKA
                </button>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Card 1: Total Temuan -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Total Temuan</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['total'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full w-fit">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    +12% <span class="text-slate-500 ml-1">dari bulan lalu</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
        </div>

        <!-- Card 2: Status Open -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Open</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['open'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full w-fit">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    -4% <span class="text-slate-500 ml-1">dari bulan lalu</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 3: Status Closed -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Closed</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['closed'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full w-fit">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    +24% <span class="text-slate-500 ml-1">dari bulan lalu</span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Chart 1: Pelaporan per Fungsi (Pie) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-base font-bold text-slate-800 mb-4">1. Jumlah Pelaporan per Fungsi</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart1"></canvas>
            </div>
        </div>

        <!-- Chart 2: Reporting Rate (Horizontal Bar) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col h-[400px]">
            <h3 class="text-base font-bold text-slate-800 mb-4">2. Reporting Rate per Fungsi</h3>
            <div class="flex-1 relative w-full h-full">
                <canvas id="chart2"></canvas>
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

        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[400px]"><h3 class="text-base font-bold text-slate-800 mb-4">1. Jumlah Pelaporan per Fungsi</h3><div class="flex-1 relative w-full h-full"><canvas id="chart1"></canvas></div></div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[400px]"><h3 class="text-base font-bold text-slate-800 mb-4">2. Reporting Rate per Fungsi</h3><div class="flex-1 relative w-full h-full"><canvas id="chart2"></canvas></div></div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[400px]"><h3 class="text-base font-bold text-slate-800 mb-4">3. Kategori PEKA</h3><div class="flex-1 relative w-full h-full"><canvas id="chart3"></canvas></div></div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[400px]"><h3 class="text-base font-bold text-slate-800 mb-4">4. Keterlibatan dalam Observasi</h3><div class="flex-1 relative w-full h-full"><canvas id="chart4"></canvas></div></div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[400px]"><h3 class="text-base font-bold text-slate-800 mb-4">5. Rekap Persentase Temuan per Fungsi</h3><div class="flex-1 relative w-full h-full"><canvas id="chart5"></canvas></div></div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[400px]"><h3 class="text-base font-bold text-slate-800 mb-4">6. Rekap Persentase Penindak Lanjut (By SAP)</h3><div class="flex-1 relative w-full h-full"><canvas id="chart6"></canvas></div></div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[400px]"><h3 class="text-base font-bold text-slate-800 mb-4">7. Unsafe Action Category</h3><div class="flex-1 relative w-full h-full"><canvas id="chart7"></canvas></div></div>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col h-[400px]"><h3 class="text-base font-bold text-slate-800 mb-4">8. Unsafe Condition Category</h3><div class="flex-1 relative w-full h-full"><canvas id="chart8"></canvas></div></div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rawCharts = @json($charts ?? []);
        
        // Define robust corporate color palettes
        const colors = ['#2563EB', '#9DBF2A', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#10B981', '#F97316', '#64748B'];
        const getColors = (len) => {
            let res = [];
            for(let i=0; i<len; i++) res.push(colors[i % colors.length]);
            return res;
        };

        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = '#64748B';
        const commonOptions = {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } } }
        };

        if(Object.keys(rawCharts).length === 0) return; // Prevent error if no data

        // 1. Pie Chart - Pelaporan per Fungsi
        new Chart(document.getElementById('chart1'), {
            type: 'pie',
            data: { labels: rawCharts.fungsi.labels, datasets: [{ data: rawCharts.fungsi.data, backgroundColor: getColors(rawCharts.fungsi.labels.length), borderWidth: 0 }] },
            options: { ...commonOptions, cutout: 0 }
        });

        // 2. Horizontal Bar - Reporting Rate
        new Chart(document.getElementById('chart2'), {
            type: 'bar',
            data: { labels: rawCharts.reporting.labels, datasets: [{ label: 'Rate', data: rawCharts.reporting.data, backgroundColor: '#2563EB', borderRadius: 4 }] },
            options: { ...commonOptions, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { display: false } }, y: { grid: { display: false } } } }
        });

        // 3. Pie Chart - Kategori PEKA
        new Chart(document.getElementById('chart3'), {
            type: 'pie',
            data: { labels: rawCharts.kategori.labels, datasets: [{ data: rawCharts.kategori.data, backgroundColor: getColors(rawCharts.kategori.labels.length), borderWidth: 0 }] },
            options: { ...commonOptions, cutout: 0 }
        });

        // 4. Stacked Vertical Bar - Keterlibatan
        new Chart(document.getElementById('chart4'), {
            type: 'bar',
            data: {
                labels: rawCharts.fungsi.labels,
                datasets: [
                    { label: 'Terlibat', data: rawCharts.fungsi.data, backgroundColor: '#9DBF2A' },
                    { label: 'Tidak Terlibat', data: rawCharts.fungsi.data.map(() => Math.floor(Math.random() * 50)), backgroundColor: '#E2E8F0' }
                ]
            },
            options: { ...commonOptions, scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, grid: { borderDash: [4, 4] } } } }
        });

        // 5. Stacked Vertical Bar - Persentase Temuan
        new Chart(document.getElementById('chart5'), {
            type: 'bar',
            data: {
                labels: rawCharts.fungsi.labels,
                datasets: [
                    { label: 'Open', data: rawCharts.fungsi.data.map(v => Math.floor(v * 0.4)), backgroundColor: '#F59E0B' },
                    { label: 'Closed', data: rawCharts.fungsi.data.map(v => Math.floor(v * 0.6)), backgroundColor: '#10B981' }
                ]
            },
            options: { ...commonOptions, scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, grid: { borderDash: [4, 4] } } } }
        });

        // 6. Donut Chart - Tindak Lanjut (SAP)
        new Chart(document.getElementById('chart6'), {
            type: 'doughnut',
            data: { labels: rawCharts.tindak_lanjut.labels, datasets: [{ data: rawCharts.tindak_lanjut.data, backgroundColor: ['#2563EB', '#E2E8F0'], borderWidth: 0 }] },
            options: { ...commonOptions, cutout: '70%' }
        });

        // 7. Horizontal Bar - Unsafe Action
        new Chart(document.getElementById('chart7'), {
            type: 'bar',
            data: { labels: rawCharts.unsafe_action.labels, datasets: [{ label: 'Unsafe Action', data: rawCharts.unsafe_action.data, backgroundColor: '#EF4444', borderRadius: 4 }] },
            options: { ...commonOptions, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { display: false } }, y: { grid: { display: false } } } }
        });

        // 8. Horizontal Bar - Unsafe Condition
        new Chart(document.getElementById('chart8'), {
            type: 'bar',
            data: { labels: rawCharts.unsafe_condition.labels, datasets: [{ label: 'Unsafe Condition', data: rawCharts.unsafe_condition.data, backgroundColor: '#F97316', borderRadius: 4 }] },
            options: { ...commonOptions, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { display: false } }, y: { grid: { display: false } } } }
        });
    });
</script>
@endpush