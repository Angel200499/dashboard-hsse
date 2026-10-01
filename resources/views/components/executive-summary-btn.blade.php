{{--
    KOMPONEN: Executive Summary PDF Button
    =========================================================
    Menampilkan tombol "Executive Summary (PDF)" dengan dropdown
    pemilih Tahun dan Bulan sebelum download.

    Data PDF selalu GLOBAL (semua fungsi), tidak bergantung
    pada dashboard mana komponen ini di-include.

    Variables opsional yang dapat di-pass dari parent:
      $selectedYear     : tahun aktif dashboard
      $selectedMonth    : bulan aktif dashboard
      $maxBulanSipeka   : max bulan data SIPEKA untuk tahun terpilih
--}}

@php
    $esBulanNama = [
        1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
        5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
        9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember',
    ];
    $esYear      = $selectedYear  ?? null;
    $esMonth     = $selectedMonth ?? null;
    $esTahunList = range((int) now()->year + 1, 2020);
    $esMaxBulan  = $maxBulanSipeka ?? null;
    $esBulanList = ($esMaxBulan && $esYear)
        ? array_filter($esBulanNama, fn($num) => $num <= $esMaxBulan, ARRAY_FILTER_USE_KEY)
        : $esBulanNama;
    $esUid = 'es-' . substr(md5(microtime()), 0, 8);
@endphp

<div class="relative" id="{{ $esUid }}-wrap">

    {{-- Tombol utama --}}
    <button
        type="button"
        onclick="document.getElementById('{{ $esUid }}-dd').classList.toggle('hidden')"
        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 border border-transparent rounded-xl transition-all shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Executive Summary (PDF)
        <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown Panel --}}
    <div id="{{ $esUid }}-dd"
         class="hidden absolute right-0 z-50 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-200 p-4">

        <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Pilih Periode PDF</p>

        <form action="{{ route('dashboard.executive-summary.pdf') }}" method="GET" target="_blank" class="space-y-3">

            {{-- Tahun --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tahun</label>
                <select name="year"
                        class="w-full bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 py-2 px-3 shadow-sm"
                        required>
                    @foreach($esTahunList as $yr)
                        <option value="{{ $yr }}" {{ $esYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Bulan --}}
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Bulan</label>
                <select name="month"
                        class="w-full bg-white border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 py-2 px-3 shadow-sm">
                    <option value="">-- Semua s/d bulan terakhir --</option>
                    @foreach($esBulanList as $num => $nama)
                        <option value="{{ $num }}" {{ $esMonth == $num ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400 mt-1">PDF = data YTD s/d bulan terpilih &bull; Scope: GLOBAL semua fungsi</p>
            </div>

            {{-- Download --}}
            <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Executive Summary
            </button>
        </form>

        <div class="mt-3 pt-3 border-t border-slate-100">
            <p class="text-[10px] text-slate-400 leading-relaxed">
                📋 8 section: Rekap PEKA, Reporting Rate, Kategori, % Temuan,
                Penindak Lanjut, Keterlibatan, Unsafe Action, Unsafe Condition.
            </p>
        </div>
    </div>
</div>

<script>
(function(){
    var uid = '{{ $esUid }}';
    document.addEventListener('click', function(e){
        var wrap = document.getElementById(uid + '-wrap');
        if (wrap && !wrap.contains(e.target)) {
            var dd = document.getElementById(uid + '-dd');
            if (dd) dd.classList.add('hidden');
        }
    });
})();
</script>
