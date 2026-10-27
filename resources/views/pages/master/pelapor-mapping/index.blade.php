@extends('layouts.app')

@section('title', 'Mapping Pelapor SIPEKA')

@section('content')
@php
    $fungsiColors = [
        'Operation'        => 'bg-blue-50 border-blue-200 text-blue-700',
        'Maintenance'      => 'bg-amber-50 border-amber-200 text-amber-700',
        'HSSE'             => 'bg-emerald-50 border-emerald-200 text-emerald-700',
        'Business Support' => 'bg-purple-50 border-purple-200 text-purple-700',
    ];
    $baseQuery = request()->except(['page', 'status']);
    $tabUrl    = fn (string $s) => route('master.pelapor-mapping.index', array_merge($baseQuery, ['status' => $s]));
    $inputCls  = 'text-xs border border-slate-200 rounded-lg px-3 py-2 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition';
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="mb-2">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Mapping Pelapor SIPEKA</h1>
        <p class="text-sm text-slate-500 mt-1 max-w-3xl">
            Hubungkan nama pelapor di SIPEKA dengan Master Personil secara <strong>manual</strong> bila penulisannya berbeda
            (mis. <em>"A. Budi Santoso"</em> → <em>"Budi Santoso"</em>). Mapping hanya dipakai oleh
            <strong>Daftar Belum Melapor</strong> — tidak mengubah data SIPEKA, Master Manpower, maupun grafik Keterlibatan.
        </p>
    </div>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs font-medium text-slate-500">Nama Pelapor SIPEKA</span>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalSipeka) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">nama unik pada seluruh data SIPEKA</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs font-medium text-slate-500">Cocok Persis Otomatis</span>
            <p class="text-2xl font-bold text-blue-700 mt-1">{{ number_format($autoExact) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">nama sama persis dg Master Personil</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <span class="text-xs font-medium text-slate-500">Dipetakan Manual</span>
            <p class="text-2xl font-bold text-emerald-700 mt-1">{{ number_format($countSudah) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">oleh Admin HSSE</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-200 p-4 shadow-sm">
            <span class="text-xs font-medium text-amber-700">Belum Terpetakan</span>
            <p class="text-2xl font-bold text-amber-700 mt-1">{{ number_format($countBelum) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">perlu mapping manual</p>
        </div>
    </div>

    {{-- TABS + FILTER + TABLE --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Tabs --}}
        <div class="flex border-b border-slate-100 px-3 pt-2 gap-1" role="tablist">
            <a id="tab-belum" role="tab" href="{{ $tabUrl('belum') }}"
               class="px-4 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 transition {{ $status === 'belum' ? 'border-[#9DBF2A] text-[#6f8a14] bg-[#9DBF2A]/10' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Belum Dipetakan
                <span class="ml-1.5 text-[11px] px-1.5 py-0.5 rounded-full {{ $status === 'belum' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">{{ number_format($countBelum) }}</span>
            </a>
            <a id="tab-sudah" role="tab" href="{{ $tabUrl('sudah') }}"
               class="px-4 py-2.5 text-sm font-semibold rounded-t-lg border-b-2 transition {{ $status === 'sudah' ? 'border-[#9DBF2A] text-[#6f8a14] bg-[#9DBF2A]/10' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Sudah Dipetakan
                <span class="ml-1.5 text-[11px] px-1.5 py-0.5 rounded-full {{ $status === 'sudah' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ number_format($countSudah) }}</span>
            </a>
        </div>

        {{-- Filter bar --}}
        <div class="px-5 py-4 border-b border-slate-100">
            <form method="GET" action="{{ route('master.pelapor-mapping.index') }}" class="flex flex-wrap items-center gap-2.5">
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <input type="text" name="q" value="{{ request('q') }}" id="mapping-search"
                           placeholder="{{ $status === 'sudah' ? 'Cari nama SIPEKA, nama personil, NOPEK...' : 'Cari nama pelapor SIPEKA...' }}"
                           class="w-full pl-9 pr-3 py-2 {{ $inputCls }}">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <select name="fungsi" onchange="this.form.submit()" class="{{ $inputCls }}" aria-label="Filter Fungsi">
                    <option value="">Semua Fungsi</option>
                    @foreach($fungsiList as $fn)
                        <option value="{{ $fn }}" {{ request('fungsi') === $fn ? 'selected' : '' }}>{{ $fn }}</option>
                    @endforeach
                </select>

                <select name="status" onchange="this.form.submit()" class="{{ $inputCls }}" aria-label="Filter Status">
                    <option value="belum" {{ $status === 'belum' ? 'selected' : '' }}>Belum Dipetakan</option>
                    <option value="sudah" {{ $status === 'sudah' ? 'selected' : '' }}>Sudah Dipetakan</option>
                </select>

                <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition">Terapkan</button>
                @if(request()->hasAny(['q', 'fungsi']))
                    <a href="{{ route('master.pelapor-mapping.index', ['status' => $status]) }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-700 underline whitespace-nowrap">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        @if($rows->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                <h3 class="text-base font-semibold text-slate-800">
                    @if($status === 'belum') Tidak ada pelapor yang belum dipetakan @else Belum ada mapping @endif
                </h3>
                <p class="text-xs text-slate-400 mt-1 max-w-sm">
                    @if(request()->hasAny(['q', 'fungsi']))
                        Coba sesuaikan pencarian/filter Anda atau klik reset.
                    @elseif($status === 'belum')
                        Semua nama pelapor SIPEKA sudah cocok dengan Master Personil atau sudah dipetakan manual.
                    @else
                        Pilih tab "Belum Dipetakan" dan klik Mapping pada nama pelapor SIPEKA.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="mapping-table">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="px-5 py-3 w-12 text-center">No</th>
                            @if($status === 'belum')
                                <th class="px-5 py-3">Nama Pelapor SIPEKA</th>
                                <th class="px-5 py-3">Fungsi</th>
                                <th class="px-5 py-3 text-right">Jumlah Laporan</th>
                                <th class="px-5 py-3 text-center w-32">Aksi</th>
                            @else
                                <th class="px-5 py-3">Nama SIPEKA</th>
                                <th class="px-5 py-3">Master Personil</th>
                                <th class="px-5 py-3">NOPEK</th>
                                <th class="px-5 py-3">Fungsi</th>
                                <th class="px-5 py-3 text-right">Laporan</th>
                                <th class="px-5 py-3 text-center w-40">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($rows as $i => $r)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5 text-center text-slate-400">{{ $rows->firstItem() + $i }}</td>

                            @if($status === 'belum')
                                @php $fn = $r['fungsi'] ?? null; @endphp
                                <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $r['nama'] }}</td>
                                <td class="px-5 py-3.5">
                                    @if($fn)
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $fungsiColors[$fn] ?? 'bg-slate-100 border-slate-200 text-slate-700' }}">{{ $fn }}</span>
                                    @else
                                        <span class="text-slate-400 italic">{{ $r['fungsi_raw'] ?: '—' }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono text-slate-700">{{ number_format($r['count']) }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <button type="button"
                                        class="btn-map px-3 py-1.5 text-xs font-semibold text-white bg-[#9DBF2A] hover:bg-[#8aaa22] rounded-lg shadow-sm transition active:scale-95"
                                        data-mode="create"
                                        data-nama="{{ $r['nama'] }}"
                                        data-fungsi="{{ $fn }}"
                                        data-fungsi-label="{{ $fn ?: ($r['fungsi_raw'] ?: '—') }}"
                                        data-jumlah="{{ $r['count'] }}">
                                        Mapping
                                    </button>
                                </td>
                            @else
                                <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $r->nama_sipeka }}</td>
                                <td class="px-5 py-3.5 text-slate-800">
                                    {{ $r->personil }}
                                    <span class="ml-1 inline-flex px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $r->tipe === 'TAD' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700' }}">{{ $r->tipe }}</span>
                                    @unless($r->aktif)<span class="ml-1 text-[10px] text-slate-400">(non-aktif)</span>@endunless
                                </td>
                                <td class="px-5 py-3.5 font-mono">
                                    @if($r->nopek)
                                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-700 font-semibold">{{ $r->nopek }}</span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">{{ $r->id_personil }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $fungsiColors[$r->fungsi] ?? 'bg-slate-100 border-slate-200 text-slate-700' }}">{{ $r->fungsi }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono text-slate-700">{{ number_format($r->jumlah) }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button"
                                            class="btn-map px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50 rounded-lg transition"
                                            data-mode="edit"
                                            data-id="{{ $r->id }}"
                                            data-nama="{{ $r->nama_sipeka }}"
                                            data-fungsi="{{ $r->fungsi }}"
                                            data-fungsi-label="{{ $r->fungsi }}"
                                            data-jumlah="{{ $r->jumlah }}"
                                            data-personil-id="{{ $r->personil_id }}"
                                            data-personil-nama="{{ $r->personil }}"
                                            data-personil-nopek="{{ $r->nopek ?: $r->id_personil }}">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('master.pelapor-mapping.destroy', $r->id) }}" class="form-delete inline" data-nama="{{ $r->nama_sipeka }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition">Hapus Mapping</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3.5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                <div>
                    Menampilkan <span class="font-semibold text-slate-700">{{ $rows->firstItem() }}</span> s/d
                    <span class="font-semibold text-slate-700">{{ $rows->lastItem() }}</span> dari
                    <span class="font-semibold text-slate-700">{{ $rows->total() }}</span> data
                </div>
                <div>{{ $rows->links() }}</div>
            </div>
        @endif
    </div>
</div>

{{-- ================================================================
     MODAL MAPPING (create & edit)
================================================================ --}}
<div id="modal-mapping" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="modal-mapping-title">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="modal-mapping-backdrop"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-800" id="modal-mapping-title">Mapping Pelapor SIPEKA</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Pilih Master Personil yang sesuai. Tidak ada pencocokan otomatis.</p>
                </div>
                <button type="button" id="btn-close-mapping" class="p-1.5 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600 transition" aria-label="Tutup">✕</button>
            </div>

            <form method="POST" action="{{ route('master.pelapor-mapping.store') }}" id="form-mapping">
                @csrf
                <input type="hidden" name="_method" id="mapping-method" value="POST" disabled>
                <input type="hidden" name="nama_sipeka" id="mapping-nama-input">
                <input type="hidden" name="master_personil_id" id="mapping-personil-input">

                <div class="px-6 py-5 space-y-4 text-xs">
                    {{-- Informasi SIPEKA --}}
                    <div class="rounded-xl bg-amber-50/70 border border-amber-200 p-3.5 space-y-2">
                        <div class="text-[11px] font-semibold text-amber-800 uppercase tracking-wider">Informasi SIPEKA</div>
                        <div>
                            <div class="text-slate-500">Nama Pelapor SIPEKA</div>
                            <div class="font-bold text-slate-800 text-sm" id="mapping-info-nama">—</div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div><div class="text-slate-500">Fungsi</div><div class="font-semibold text-slate-800" id="mapping-info-fungsi">—</div></div>
                            <div><div class="text-slate-500">Jumlah Laporan</div><div class="font-semibold text-slate-800" id="mapping-info-jumlah">0</div></div>
                        </div>
                    </div>

                    {{-- Pencarian Master Personil --}}
                    <div>
                        <label for="mapping-personil-search" class="block font-medium text-slate-700 mb-1">Cari Master Personil</label>
                        <input type="text" id="mapping-personil-search" autocomplete="off"
                               placeholder="Ketik nama, NOPEK, atau jabatan..."
                               class="w-full {{ $inputCls }}">
                        <label class="mt-2 inline-flex items-center gap-2 cursor-pointer text-slate-600">
                            <input type="checkbox" id="mapping-semua-fungsi" class="rounded border-slate-300 text-[#9DBF2A] focus:ring-[#9DBF2A]">
                            <span>Tampilkan personil fungsi lain <span class="text-slate-400">(fungsi personil tidak diubah)</span></span>
                        </label>
                        <div id="mapping-results" class="mt-2 max-h-52 overflow-y-auto border border-slate-200 rounded-lg divide-y divide-slate-100 bg-white"></div>
                    </div>

                    {{-- Personil terpilih --}}
                    <div id="mapping-selected" class="hidden rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-emerald-900">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700">Personil Terpilih</div>
                        <div class="font-bold text-sm" id="mapping-selected-nama"></div>
                        <div class="text-[11px] text-emerald-700" id="mapping-selected-meta"></div>
                    </div>
                </div>

                <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-2.5">
                    <button type="button" id="btn-cancel-mapping" class="px-3.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                    <button type="submit" id="btn-save-mapping" disabled
                            class="px-4 py-2 text-xs font-semibold text-white bg-[#9DBF2A] hover:bg-[#8aaa22] rounded-lg shadow-sm transition active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed">
                        Simpan Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const $ = (id) => document.getElementById(id);

    const modal      = $('modal-mapping');
    const form       = $('form-mapping');
    const methodIn   = $('mapping-method');
    const namaIn     = $('mapping-nama-input');
    const personilIn = $('mapping-personil-input');
    const searchIn   = $('mapping-personil-search');
    const semuaChk   = $('mapping-semua-fungsi');
    const results    = $('mapping-results');
    const saveBtn    = $('btn-save-mapping');
    const selBox     = $('mapping-selected');

    const STORE_URL   = @json(route('master.pelapor-mapping.store'));
    const UPDATE_TPL  = @json(route('master.pelapor-mapping.update', '__ID__'));
    const SEARCH_URL  = @json(route('master.pelapor-mapping.personils'));

    let ctx = { fungsi: '' };
    let seq = 0;
    let timer = null;

    function select(p) {
        personilIn.value = p.id;
        $('mapping-selected-nama').textContent = p.nama;
        $('mapping-selected-meta').textContent = [p.nopek ? 'NOPEK: ' + p.nopek : (p.id_personil || ''), p.fungsi ? 'Fungsi: ' + p.fungsi : '', p.tipe ? 'Tipe: ' + p.tipe : ''].filter(Boolean).join(' · ');
        selBox.classList.remove('hidden');
        saveBtn.disabled = false;
    }

    function load() {
        const mySeq = ++seq;
        const url = new URL(SEARCH_URL, window.location.origin);
        url.searchParams.set('q', searchIn.value.trim());
        if (ctx.fungsi) url.searchParams.set('fungsi', ctx.fungsi);
        if (semuaChk.checked) url.searchParams.set('semua', '1');
        results.innerHTML = '<div class="p-3 text-slate-400">Memuat…</div>';

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(res => {
                if (mySeq !== seq) return;
                if (!res.data || !res.data.length) {
                    results.innerHTML = '<div class="p-3 text-slate-400">Tidak ada personil aktif yang cocok.' + (ctx.fungsi && !semuaChk.checked ? ' Centang "Tampilkan personil fungsi lain" bila perlu.' : '') + '</div>';
                    return;
                }
                results.innerHTML = res.data.map((p, i) => `
                    <button type="button" data-i="${i}" class="pick w-full text-left px-3 py-2 hover:bg-[#9DBF2A]/10 focus:bg-[#9DBF2A]/10 focus:outline-none transition">
                        <div class="font-semibold text-slate-800">${esc(p.nama)}</div>
                        <div class="text-[11px] text-slate-500">${p.nopek ? 'NOPEK: ' + esc(p.nopek) : esc(p.id_personil)} · Fungsi: ${esc(p.fungsi)} · Tipe: ${esc(p.tipe)}</div>
                    </button>`).join('');
                results.querySelectorAll('.pick').forEach(btn => btn.addEventListener('click', () => select(res.data[btn.dataset.i])));
            })
            .catch(() => { if (mySeq === seq) results.innerHTML = '<div class="p-3 text-red-500">Gagal memuat data personil.</div>'; });
    }

    function open(d) {
        ctx = { fungsi: d.fungsi || '' };
        namaIn.value = d.nama;
        personilIn.value = '';
        saveBtn.disabled = true;
        selBox.classList.add('hidden');
        searchIn.value = '';
        semuaChk.checked = !ctx.fungsi; // fungsi tak dikenal → tampilkan semua kandidat

        $('mapping-info-nama').textContent   = d.nama;
        $('mapping-info-fungsi').textContent = d.fungsiLabel || '—';
        $('mapping-info-jumlah').textContent = Number(d.jumlah || 0).toLocaleString('id-ID');

        if (d.mode === 'edit') {
            form.action = UPDATE_TPL.replace('__ID__', d.id);
            methodIn.disabled = false; methodIn.value = 'PUT';
            $('modal-mapping-title').textContent = 'Edit Mapping Pelapor SIPEKA';
            select({ id: d.personilId, nama: d.personilNama, nopek: d.personilNopek });
        } else {
            form.action = STORE_URL;
            methodIn.disabled = true;
            $('modal-mapping-title').textContent = 'Mapping Pelapor SIPEKA';
        }

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        load();
        searchIn.focus();
    }

    function close() {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    document.querySelectorAll('.btn-map').forEach(btn => btn.addEventListener('click', function () {
        const d = this.dataset;
        open({
            mode: d.mode, id: d.id, nama: d.nama, fungsi: d.fungsi, fungsiLabel: d.fungsiLabel, jumlah: d.jumlah,
            personilId: d.personilId, personilNama: d.personilNama, personilNopek: d.personilNopek,
        });
    }));

    searchIn.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(load, 250); });
    semuaChk.addEventListener('change', load);
    $('btn-close-mapping').addEventListener('click', close);
    $('btn-cancel-mapping').addEventListener('click', close);
    $('modal-mapping-backdrop').addEventListener('click', close);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });

    document.querySelectorAll('.form-delete').forEach(f => f.addEventListener('submit', function (e) {
        if (!confirm(`Hapus mapping untuk "${this.dataset.nama}"?\n\nData SIPEKA dan Master Personil tidak berubah. Nama ini kembali menjadi "Belum Dipetakan" (kecuali cocok persis dengan Master Personil).`)) {
            e.preventDefault();
        }
    }));

    // Pencarian halaman: kirim otomatis setelah jeda mengetik
    const pageSearch = $('mapping-search');
    if (pageSearch) {
        let t = null;
        pageSearch.addEventListener('input', () => { clearTimeout(t); t = setTimeout(() => pageSearch.form.submit(), 600); });
        if (pageSearch.value) { pageSearch.focus(); pageSearch.setSelectionRange(pageSearch.value.length, pageSearch.value.length); }
    }
});
</script>
@endpush
@endsection
