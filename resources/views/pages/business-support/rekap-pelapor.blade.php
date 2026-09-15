@extends('layouts.app')

@section('title', 'Rekap Pelapor - Business Support')

@section('content')
<div class="space-y-6">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('dashboard.fungsi', 'Business Support') }}" class="hover:text-[#9DBF2A] transition-colors">Dashboard Business Support</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-700 font-medium">Rekap Pelapor</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Rekap Pelapor
            </h1>
            <p class="text-sm text-slate-500 mt-1">Rekap jumlah temuan per bulan oleh masing-masing pelapor pada fungsi <strong>Business Support</strong>.</p>
        </div>

        {{-- Tombol Download PDF --}}
        <a href="{{ route('dashboard.business-support.rekap-pelapor.pdf', array_filter([
                'rekap_periode' => $rekapPeriode,
                'rekap_bulan'   => $rekapBulan,
                'rekap_tahun'   => $rekapTahun,
                'rekap_search'  => $rekapSearch,
            ])) }}"
           target="_blank"
           class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition-colors whitespace-nowrap flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download PDF
        </a>
    </div>

    {{-- ===== FILTER PERIODE ===== --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
        <form method="GET" action="" class="flex flex-wrap items-end gap-4" id="filterForm">
            {{-- Search Nama Pelapor --}}
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Cari Pelapor</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        name="rekap_search"
                        id="rekapSearch"
                        value="{{ $rekapSearch }}"
                        placeholder="Ketik nama pelapor..."
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-amber-400 focus:border-amber-400 pl-9 pr-8 py-2.5 shadow-sm w-56"
                        autocomplete="off"
                    >
                    @if($rekapSearch)
                    <a href="{{ request()->fullUrlWithQuery(['rekap_search' => '']) }}"
                       class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors"
                       title="Hapus pencarian">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Periode</label>
                <select name="rekap_periode" id="rekapPeriode" onchange="togglePerBulan(); this.form.submit();"
                    class="bg-white border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-amber-400 focus:border-amber-400 p-2.5 shadow-sm min-w-[180px]">
                    <option value=""          {{ $rekapPeriode === ''         ? 'selected' : '' }}>Semua Waktu</option>
                    <option value="1_day"     {{ $rekapPeriode === '1_day'    ? 'selected' : '' }}>1 Hari Terakhir</option>
                    <option value="3_days"    {{ $rekapPeriode === '3_days'   ? 'selected' : '' }}>3 Hari Terakhir</option>
                    <option value="1_week"    {{ $rekapPeriode === '1_week'   ? 'selected' : '' }}>1 Minggu Terakhir</option>
                    <option value="1_month"   {{ $rekapPeriode === '1_month'  ? 'selected' : '' }}>1 Bulan Terakhir</option>
                    <option value="per_bulan" {{ $rekapPeriode === 'per_bulan'? 'selected' : '' }}>Per Bulan</option>
                </select>
            </div>

            <div id="perBulanFields" class="{{ $rekapPeriode === 'per_bulan' ? 'flex' : 'hidden' }} items-end gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Bulan</label>
                    <select name="rekap_bulan" onchange="this.form.submit()"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-amber-400 focus:border-amber-400 p-2.5 shadow-sm">
                        @foreach(['Januari'=>1,'Februari'=>2,'Maret'=>3,'April'=>4,'Mei'=>5,'Juni'=>6,'Juli'=>7,'Agustus'=>8,'September'=>9,'Oktober'=>10,'November'=>11,'Desember'=>12] as $nama => $num)
                            <option value="{{ $num }}" {{ $rekapBulan === $num ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Tahun</label>
                    <select name="rekap_tahun" onchange="this.form.submit()"
                        class="bg-white border border-slate-300 text-slate-800 text-sm rounded-xl focus:ring-amber-400 focus:border-amber-400 p-2.5 shadow-sm">
                        @for($y = now()->year; $y >= 2023; $y--)
                            <option value="{{ $y }}" {{ $rekapTahun === $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            {{-- Tombol Search --}}
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-slate-700 hover:bg-slate-800 rounded-xl shadow-sm transition-colors whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                Cari
            </button>

            {{-- Badge periode aktif --}}
            <div class="flex items-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $rekapPelapor['periode_label'] }}
                </span>
            </div>
        </form>
    </div>

    {{-- ===== KPI CARDS ===== --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Total Pelaporan --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Total Pelaporan</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($rekapPelapor['total_pelaporan']) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full w-fit">
                    {{ $rekapPelapor['periode_label'] }}
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>

        {{-- Total Pelapor --}}
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Total Pelapor</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($rekapPelapor['total_pelapor']) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full w-fit">
                    Pelapor Unik
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>

        {{-- Pelapor Terbanyak --}}
        <div class="bg-amber-50 p-6 rounded-2xl border border-amber-200 shadow-sm hover:shadow-md transition-shadow group flex items-start justify-between">
            <div class="min-w-0 flex-1 pr-3">
                <p class="text-sm font-semibold text-amber-700 mb-1">Pelapor Terbanyak</p>
                <h3 class="text-xl font-bold text-amber-900 truncate">{{ $rekapPelapor['pelapor_terbanyak']['nama'] }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-amber-700 bg-amber-100 px-2 py-1 rounded-full w-fit">
                    {{ number_format($rekapPelapor['pelapor_terbanyak']['jumlah']) }} laporan
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-200 text-amber-700 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
        </div>

    </div>

    {{-- ===== TABEL REKAP BULANAN ===== --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Rekap Bulanan Pelapor</h3>
                <p class="text-xs text-slate-500 mt-0.5">Jumlah temuan per pelapor per bulan (Jan-Des)</p>
            </div>
            <div class="flex items-center gap-2">
                @if($rekapSearch)
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                        &ldquo;{{ $rekapSearch }}&rdquo;
                    </span>
                @endif
                @if(!empty($rekapPelapor['rekap']))
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                        {{ count($rekapPelapor['rekap']) }} pelapor
                    </span>
                @endif
            </div>
        </div>

        @if(empty($rekapPelapor['rekap']))
            <div class="px-6 py-16 text-center">
                <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-base font-semibold text-slate-700 mb-1">Belum ada data</p>
                <p class="text-sm text-slate-400">Belum ada data pelaporan Business Support pada periode yang dipilih.</p>
            </div>
        @else
            {{-- Horizontal scroll agar tabel 15 kolom tidak merusak layout --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-slate-600" style="min-width:900px;">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 font-semibold text-center w-12">No</th>
                            <th class="px-4 py-3 font-semibold text-left" style="min-width:120px;">Fungsi</th>
                            <th class="px-4 py-3 font-semibold text-left" style="min-width:180px;">Nama</th>
                            @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $bln)
                                <th class="px-2 py-3 font-semibold text-right" style="min-width:44px;">{{ $bln }}</th>
                            @endforeach
                            <th class="px-3 py-3 font-semibold text-right bg-slate-200 text-slate-800" style="min-width:56px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekapPelapor['rekap'] as $idx => $row)
                            <tr class="border-b border-slate-100 {{ $idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/50' }} hover:bg-amber-50/40 transition-colors">
                                <td class="px-4 py-3 text-center">
                                    <span class="text-slate-400 font-medium text-xs">{{ $idx + 1 }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold" style="background:#f3f9e0;color:#4a6a0a;">Business Support</span>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $row['pelapor'] }}</td>
                                @for($m = 1; $m <= 12; $m++)
                                    @php $val = $row['monthly'][$m] ?? 0; @endphp
                                    <td class="px-2 py-3 text-right {{ $val > 0 ? 'font-semibold text-slate-800' : 'text-slate-300' }}">{{ $val > 0 ? number_format($val) : '0' }}</td>
                                @endfor
                                <td class="px-3 py-3 text-right font-bold text-slate-900 bg-slate-100">{{ number_format($row['jumlah_total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-800 text-white">
                            <td class="px-4 py-3 text-center"></td>
                            <td class="px-4 py-3 font-semibold text-xs uppercase tracking-wide" colspan="2">Total</td>
                            @for($m = 1; $m <= 12; $m++)
                                <td class="px-2 py-3 text-right font-bold">{{ number_format($rekapPelapor['monthly_totals'][$m] ?? 0) }}</td>
                            @endfor
                            <td class="px-3 py-3 text-right font-bold" style="background:#7a9920;">{{ number_format($rekapPelapor['total_pelaporan']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

</div>

<script>
function togglePerBulan() {
    const val = document.getElementById('rekapPeriode').value;
    const box = document.getElementById('perBulanFields');
    if (val === 'per_bulan') {
        box.classList.remove('hidden');
        box.classList.add('flex');
    } else {
        box.classList.add('hidden');
        box.classList.remove('flex');
    }
}
</script>
@endsection