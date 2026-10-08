{{-- ================================================================
     MODAL DAFTAR BELUM MELAPOR
     Menampilkan personil aktif yang BELUM PERNAH tercatat sebagai pelapor
     pada SELURUH data SIPEKA yang tersedia (tanpa batas bulan/tahun).
     Judul modal dinamis mengikuti fungsi yang diklik pada chart Keterlibatan.
================================================================ --}}
<div id="modal-unreported" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-unreported-title" role="dialog" aria-modal="true">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" id="modal-unreported-backdrop"></div>

    {{-- Dialog Panel --}}
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-3 sm:p-4 md:p-6 text-center">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all w-full max-w-4xl border border-slate-100 flex flex-col max-h-[90vh]">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 tracking-tight" id="modal-unreported-title">
                            Belum Melapor — <span id="unreported-title-fungsi">Operation</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Personil aktif yang belum pernah tercatat sebagai pelapor pada data SIPEKA
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Dihitung dari seluruh riwayat SIPEKA<span id="unreported-range-wrap" class="hidden"> (<span id="unreported-range-label" class="font-semibold text-slate-600"></span>)</span> — bukan per bulan/tahun.</span>
                        </p>
                    </div>
                    <button
                        type="button"
                        id="btn-close-unreported"
                        class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors"
                        title="Tutup Modal (Esc)"
                        aria-label="Tutup"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Summary Stats Cards --}}
                <div class="px-6 py-3 bg-white border-b border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3 rounded-xl bg-red-50/80 border border-red-100 flex items-center justify-between sm:col-span-1">
                        <div>
                            <span class="text-[11px] font-semibold text-red-600 block">Orang Belum Melapor</span>
                            <span id="unreported-count-unreported" class="text-2xl font-bold text-red-700">0</span>
                            <span class="text-[10px] text-red-500/90 block">dari total <span id="unreported-count-total-inline">0</span> manpower <span id="unreported-summary-fungsi"></span></span>
                        </div>
                        <div class="w-9 h-9 rounded-lg bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-emerald-50/80 border border-emerald-100 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-semibold text-emerald-600 block">Sudah Pernah Melapor</span>
                            <span id="unreported-count-reported" class="text-xl font-bold text-emerald-700">0</span>
                            <span class="text-[10px] text-emerald-500/90 block">personil aktif tercatat di SIPEKA</span>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-semibold text-slate-500 block">Total Personil Aktif</span>
                            <span id="unreported-count-total" class="text-xl font-bold text-slate-700">0</span>
                            <span class="text-[10px] text-slate-400 block">pada fungsi ini (Master Personil)</span>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Filter & Action Bar --}}
                <div class="px-6 py-3 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
                    <div class="flex items-center gap-2 flex-1 min-w-[240px]">
                        {{-- Search Input --}}
                        <div class="relative flex-1 max-w-sm">
                            <input
                                type="text"
                                id="unreported-search-input"
                                placeholder="Cari nama, NOPEK, atau ID Personil..."
                                autocomplete="off"
                                class="w-full pl-9 pr-3 py-1.5 text-xs border border-slate-200 rounded-lg text-slate-700 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>

                        {{-- Filter Tipe --}}
                        <select
                            id="unreported-filter-tipe"
                            class="text-xs border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                            <option value="">Semua Tipe</option>
                            <option value="Pekerja">Pekerja</option>
                            <option value="TAD">TAD</option>
                        </select>
                    </div>

                    {{-- Export Button --}}
                    <button
                        type="button"
                        id="btn-export-unreported"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-semibold rounded-lg shadow-sm transition active:scale-95 whitespace-nowrap"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export Daftar
                    </button>
                </div>

                {{-- Table Area --}}
                <div class="flex-1 overflow-y-auto min-h-[280px] max-h-[460px] relative bg-slate-50/30">
                    {{-- Loading Indicator --}}
                    <div id="unreported-loading" class="hidden absolute inset-0 bg-white/75 backdrop-blur-[1px] z-10 flex items-center justify-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-7 h-7 border-2 border-[#9DBF2A] border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-xs text-slate-500 font-medium">Memuat data personil...</span>
                        </div>
                    </div>

                    <table class="w-full text-left border-collapse" id="unreported-table">
                        <thead class="sticky top-0 bg-slate-100/90 backdrop-blur-xs text-[11px] font-semibold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-5 py-2.5 w-12 text-center">No</th>
                                <th class="px-5 py-2.5">Nama</th>
                                <th class="px-5 py-2.5">NOPEK / ID Personil</th>
                                <th class="px-5 py-2.5">Tipe</th>
                                <th class="px-5 py-2.5">Jabatan</th>
                                <th class="px-5 py-2.5 text-center w-36">Status</th>
                            </tr>
                        </thead>
                        <tbody id="unreported-table-body" class="divide-y divide-slate-100 text-xs bg-white">
                            {{-- Rows diisi via JavaScript --}}
                        </tbody>
                    </table>

                    {{-- Empty State --}}
                    <div id="unreported-empty" class="hidden py-14 px-6 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center mx-auto mb-2.5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800" id="unreported-empty-title">Tidak ada data</h4>
                        <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto" id="unreported-empty-desc"></p>
                    </div>
                </div>

                {{-- Notice & Disclaimer --}}
                <div class="px-6 py-2.5 bg-slate-50 border-t border-slate-100 text-[11px] text-slate-500 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 text-slate-500">
                        <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>"Belum Melapor" = nama tidak pernah tercatat sebagai pelapor pada seluruh data SIPEKA. Pencocokan memakai nama ternormalisasi (tanpa fuzzy). TAD tanpa NOPEK ditampilkan dengan ID Personil internal.</span>
                    </div>
                    <div id="unreported-unmatched-notice" class="hidden">
                        <button type="button" id="btn-toggle-unmatched" class="text-amber-700 hover:text-amber-800 font-semibold underline text-[11px]">
                            Pelapor SIPEKA Belum Terpetakan (<span id="unmatched-count">0</span>)
                        </button>
                    </div>
                </div>

                {{-- Expandable Unmatched SIPEKA Names --}}
                <div id="unreported-unmatched-box" class="hidden px-6 py-3 bg-amber-50/80 border-t border-amber-200 text-xs text-amber-900">
                    <p class="font-semibold text-amber-800 mb-1">Pelapor SIPEKA Belum Terpetakan (<span id="unmatched-count-title">0</span>)</p>
                    <p class="text-[11px] text-amber-700 mb-2 leading-relaxed">
                        Nama pelapor berikut ada di data SIPEKA, tetapi tidak cocok persis dengan Master Personil mana pun (bisa karena beda ejaan/singkatan, atau belum terdaftar). Nama-nama ini tidak dimasukkan otomatis ke Master Personil.
                    </p>
                    <div id="unreported-unmatched-list" class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto">
                        {{-- Badges diisi JS --}}
                    </div>
                </div>

                {{-- Footer & Pagination --}}
                <div class="px-6 py-3 border-t border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-slate-500">
                    <div id="unreported-pagination-info">
                        Menampilkan 0–0 dari 0
                    </div>
                    <div class="flex items-center gap-1" id="unreported-pagination-controls">
                        {{-- Tombol halaman diisi JS --}}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const PER_PAGE = 10;

        const modal = document.getElementById('modal-unreported');
        const backdrop = document.getElementById('modal-unreported-backdrop');
        const btnClose = document.getElementById('btn-close-unreported');
        const searchInput = document.getElementById('unreported-search-input');
        const filterTipe = document.getElementById('unreported-filter-tipe');
        const btnExport = document.getElementById('btn-export-unreported');
        const btnToggleUnmatched = document.getElementById('btn-toggle-unmatched');
        const unmatchedBox = document.getElementById('unreported-unmatched-box');

        // State parameters (status "belum pernah melapor" TIDAK bergantung tahun/bulan)
        let currentFungsi = '';
        let currentPage = 1;
        let searchTimeout = null;
        let requestSeq = 0;

        const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        const fmt = (n) => Number(n || 0).toLocaleString('id-ID');
        const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };

        // Buka modal secara global. Argumen tahun/bulan (jika ada) diabaikan:
        // status dihitung dari seluruh riwayat SIPEKA.
        window.openUnreportedModal = function(fungsi) {
            if (!fungsi) return;

            currentFungsi = fungsi;
            currentPage = 1;

            if (searchInput) searchInput.value = '';
            if (filterTipe) filterTipe.value = '';
            if (unmatchedBox) unmatchedBox.classList.add('hidden');

            // Judul dinamis mengikuti fungsi yang diklik
            setText('unreported-title-fungsi', fungsi);
            setText('unreported-summary-fungsi', fungsi);
            setText('unreported-count-unreported', '0');
            setText('unreported-count-total-inline', '0');
            setText('unreported-count-reported', '0');
            setText('unreported-count-total', '0');

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');

            loadData();
        };

        function closeModal() {
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        if (btnClose) btnClose.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        // Toggle Unmatched Box
        if (btnToggleUnmatched) {
            btnToggleUnmatched.addEventListener('click', function() {
                if (unmatchedBox) unmatchedBox.classList.toggle('hidden');
            });
        }

        // Live Search (debounce singkat; tanpa reload halaman)
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    currentPage = 1;
                    loadData();
                }, 250);
            });
        }

        // Filter Tipe
        if (filterTipe) {
            filterTipe.addEventListener('change', function() {
                currentPage = 1;
                loadData();
            });
        }

        // Export Button — hanya data sesuai search/filter yang sedang tampil
        if (btnExport) {
            btnExport.addEventListener('click', function() {
                if (!currentFungsi) return;
                const q = searchInput ? searchInput.value.trim() : '';
                const tipe = filterTipe ? filterTipe.value : '';

                const url = new URL('{{ route("unreported-personil.export") }}', window.location.origin);
                url.searchParams.set('fungsi', currentFungsi);
                if (q) url.searchParams.set('q', q);
                if (tipe) url.searchParams.set('tipe', tipe);

                window.location.href = url.toString();
            });
        }

        // Fetch Data via AJAX
        function loadData() {
            const loading = document.getElementById('unreported-loading');
            const tbody = document.getElementById('unreported-table-body');
            const emptyEl = document.getElementById('unreported-empty');
            const q = searchInput ? searchInput.value.trim() : '';
            const tipe = filterTipe ? filterTipe.value : '';
            const seq = ++requestSeq;

            if (loading) loading.classList.remove('hidden');

            const url = new URL('{{ route("unreported-personil.data") }}', window.location.origin);
            url.searchParams.set('fungsi', currentFungsi);
            url.searchParams.set('page', currentPage);
            url.searchParams.set('per_page', PER_PAGE);
            if (q) url.searchParams.set('q', q);
            if (tipe) url.searchParams.set('tipe', tipe);

            fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(res => {
                if (seq !== requestSeq) return; // abaikan respons lama (search cepat)
                if (loading) loading.classList.add('hidden');
                if (!res.success) {
                    alert(res.message || 'Gagal memuat data personil.');
                    return;
                }

                // Konteks rentang data SIPEKA (informasi saja)
                const rangeWrap = document.getElementById('unreported-range-wrap');
                if (res.meta && res.meta.sipeka_range) {
                    setText('unreported-range-label', res.meta.sipeka_range);
                    if (rangeWrap) rangeWrap.classList.remove('hidden');
                } else if (rangeWrap) {
                    rangeWrap.classList.add('hidden');
                }

                // Summary (Belum + Sudah = Total)
                setText('unreported-count-unreported', fmt(res.summary.total_unreported));
                setText('unreported-count-reported', fmt(res.summary.total_reported));
                setText('unreported-count-total', fmt(res.summary.total_active));
                setText('unreported-count-total-inline', fmt(res.summary.total_active));
                setText('unreported-summary-fungsi', currentFungsi);

                // Unmatched Notice
                const unmatchedNotice = document.getElementById('unreported-unmatched-notice');
                const unmatchedList = document.getElementById('unreported-unmatched-list');
                const unmatchedData = res.summary.unmatched_sipeka || [];

                if (unmatchedNotice) {
                    if (unmatchedData.length > 0) {
                        setText('unmatched-count', unmatchedData.length);
                        setText('unmatched-count-title', unmatchedData.length);
                        unmatchedNotice.classList.remove('hidden');
                        if (unmatchedList) {
                            unmatchedList.innerHTML = unmatchedData.map(name =>
                                `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-100 text-amber-800 border border-amber-300">${esc(name)}</span>`
                            ).join('');
                        }
                    } else {
                        unmatchedNotice.classList.add('hidden');
                        if (unmatchedBox) unmatchedBox.classList.add('hidden');
                    }
                }

                // Render Table Rows
                if (!res.data || res.data.length === 0) {
                    if (tbody) tbody.innerHTML = '';
                    const searching = q !== '' || tipe !== '';
                    setText('unreported-empty-title', searching ? 'Tidak ada hasil' : 'Semua personil aktif sudah pernah melapor');
                    setText('unreported-empty-desc', searching
                        ? 'Tidak ada personil belum melapor yang cocok dengan pencarian/filter ini.'
                        : 'Seluruh personil aktif pada fungsi ini pernah tercatat sebagai pelapor di data SIPEKA.');
                    if (emptyEl) emptyEl.classList.remove('hidden');
                } else {
                    if (emptyEl) emptyEl.classList.add('hidden');
                    const startNo = (res.pagination.from || 1);

                    tbody.innerHTML = res.data.map((item, idx) => {
                        const no = startNo + idx;
                        const tipeBadge = item.tipe_manpower === 'Pekerja'
                            ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-700">Pekerja</span>'
                            : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-700">TAD</span>';

                        // NOPEK resmi bila ada; jika tidak, ID Personil internal (bukan NOPEK)
                        const idBadge = item.nopek
                            ? `<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200" title="NOPEK">${esc(item.nopek)}</span>`
                            : `<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono text-slate-500 bg-slate-50 border border-dashed border-slate-300" title="ID Personil internal (bukan NOPEK resmi)">${esc(item.id_personil)}</span>`;

                        return `
                            <tr class="hover:bg-slate-50/75 transition-colors">
                                <td class="px-5 py-2.5 text-center text-slate-400 font-mono">${no}</td>
                                <td class="px-5 py-2.5 font-semibold text-slate-800">${esc(item.nama)}</td>
                                <td class="px-5 py-2.5">${idBadge}</td>
                                <td class="px-5 py-2.5">${tipeBadge}</td>
                                <td class="px-5 py-2.5 text-slate-600">${esc(item.jabatan)}</td>
                                <td class="px-5 py-2.5 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700" title="Belum pernah tercatat melapor pada seluruh data SIPEKA yang tersedia">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Belum Melapor
                                    </span>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }

                renderPagination(res.pagination);
            })
            .catch(err => {
                if (seq !== requestSeq) return;
                if (loading) loading.classList.add('hidden');
                console.error('Error fetching unreported personils:', err);
            });
        }

        // Daftar halaman dengan ellipsis: 1 2 3 4 5 … 8
        function pageList(current, last) {
            if (last <= 7) return Array.from({length: last}, (_, i) => i + 1);
            const pages = new Set([1, last, current, current - 1, current + 1]);
            if (current <= 3) { pages.add(2); pages.add(3); pages.add(4); pages.add(5); }
            if (current >= last - 2) { pages.add(last - 1); pages.add(last - 2); pages.add(last - 3); pages.add(last - 4); }
            const sorted = [...pages].filter(p => p >= 1 && p <= last).sort((a, b) => a - b);
            const out = [];
            sorted.forEach((p, i) => {
                if (i > 0 && p - sorted[i - 1] > 1) out.push('…');
                out.push(p);
            });
            return out;
        }

        // Render Pagination Controls
        function renderPagination(pg) {
            const infoEl = document.getElementById('unreported-pagination-info');
            const controlsEl = document.getElementById('unreported-pagination-controls');

            if (infoEl) {
                infoEl.innerHTML = `Menampilkan <span class="font-semibold text-slate-700">${fmt(pg.from || 0)}</span>–<span class="font-semibold text-slate-700">${fmt(pg.to || 0)}</span> dari <span class="font-semibold text-slate-700">${fmt(pg.total || 0)}</span>`;
            }

            if (!controlsEl) return;
            controlsEl.innerHTML = '';

            if (!pg.last_page || pg.last_page <= 1) return;

            const go = (p) => { if (p >= 1 && p <= pg.last_page && p !== currentPage) { currentPage = p; loadData(); } };
            const base = 'min-w-[28px] h-7 px-2 inline-flex items-center justify-center text-xs rounded-lg transition';

            // ←
            const btnPrev = document.createElement('button');
            btnPrev.type = 'button';
            btnPrev.setAttribute('aria-label', 'Halaman sebelumnya');
            btnPrev.className = `${base} border border-slate-200 ${pg.current_page > 1 ? 'hover:bg-slate-100 text-slate-700' : 'opacity-40 cursor-not-allowed text-slate-400'}`;
            btnPrev.textContent = '←';
            btnPrev.disabled = (pg.current_page <= 1);
            btnPrev.onclick = () => go(pg.current_page - 1);
            controlsEl.appendChild(btnPrev);

            pageList(pg.current_page, pg.last_page).forEach(p => {
                if (p === '…') {
                    const dots = document.createElement('span');
                    dots.className = 'px-1 text-slate-400';
                    dots.textContent = '…';
                    controlsEl.appendChild(dots);
                    return;
                }
                const btn = document.createElement('button');
                btn.type = 'button';
                const isCurrent = (p === pg.current_page);
                btn.className = `${base} font-semibold ${isCurrent ? 'bg-[#9DBF2A] text-white shadow-sm' : 'border border-slate-200 hover:bg-slate-100 text-slate-700'}`;
                btn.textContent = p;
                btn.onclick = () => go(p);
                controlsEl.appendChild(btn);
            });

            // →
            const btnNext = document.createElement('button');
            btnNext.type = 'button';
            btnNext.setAttribute('aria-label', 'Halaman berikutnya');
            btnNext.className = `${base} border border-slate-200 ${pg.current_page < pg.last_page ? 'hover:bg-slate-100 text-slate-700' : 'opacity-40 cursor-not-allowed text-slate-400'}`;
            btnNext.textContent = '→';
            btnNext.disabled = (pg.current_page >= pg.last_page);
            btnNext.onclick = () => go(pg.current_page + 1);
            controlsEl.appendChild(btnNext);
        }
    })();
</script>
