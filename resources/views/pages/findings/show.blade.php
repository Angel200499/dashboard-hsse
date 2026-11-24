@extends('layouts.app')

@section('title', 'Detail Temuan - ' . $finding->id_temuan)

@section('content')
@php
    $data = $finding->data_sipeka ?? [];
    $status = strtolower($data['status'] ?? '');

    // Tampilkan tanggal serial Excel (mis. 46260 -> 2026-08-26) secara benar.
    // Hanya presentasi: data sumber tidak diubah.
    $formatDate = function ($value) {
        if (empty($value) || $value === '-') {
            return '-';
        }
        if (is_numeric($value) && (float) $value > 20000 && (float) $value < 100000) {
            try {
                if (class_exists('\PhpOffice\PhpSpreadsheet\Shared\Date')) {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
                }
                return gmdate('Y-m-d', ((int) $value - 25569) * 86400);
            } catch (\Throwable $e) {
                return $value;
            }
        }
        return $value;
    };

    $photos = [
        ['label' => 'Foto Temuan (Sebelum)', 'url' => $data['fototemuan'] ?? null, 'alt' => 'Foto kondisi temuan sebelum tindak lanjut, ID Temuan ' . $finding->id_temuan],
        ['label' => 'Foto Close (Sesudah)',  'url' => $data['fotoclose'] ?? null,  'alt' => 'Foto kondisi setelah temuan ditutup, ID Temuan ' . $finding->id_temuan],
    ];

    $card       = 'bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden';
    $cardHeader = 'flex items-center gap-2 px-4 py-1.5 2xl:px-5 2xl:py-2.5 bg-slate-50 border-b border-slate-200';
    $cardBody   = 'px-4 py-3 2xl:px-5 2xl:py-4';
    $focusRing  = 'focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2';
@endphp

<div class="finding-detail flex flex-col gap-3 2xl:gap-4 pb-2">

    {{-- Header: kembali, judul, ID temuan, status --}}
    <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 pb-2 border-b border-slate-200">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 min-w-0">
            <a href="{{ route('findings.index') }}"
               class="inline-flex items-center justify-center w-8 h-8 shrink-0 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors {{ $focusRing }}"
               title="Kembali ke Daftar Temuan" aria-label="Kembali ke Daftar Temuan">
                <svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="fd-title">Detail Temuan</h1>
            <span class="font-mono text-sm font-medium text-slate-700 bg-slate-100 border border-slate-300 rounded px-2 py-0.5">ID: {{ $finding->id_temuan }}</span>
        </div>

        @if($status === 'open')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-900 border border-amber-300">Status: OPEN</span>
        @elseif($status === 'closed')
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-300">Status: CLOSED</span>
        @else
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-slate-100 text-slate-800 border border-slate-300">Status: {{ $data['status'] ?? '-' }}</span>
        @endif
    </div>

    {{-- Dua kolom desktop: kiri = apa yang ditemukan, kanan = alur penanganan --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 2xl:gap-4 lg:flex-1">

        {{-- KOLOM KIRI --}}
        <div class="flex flex-col gap-3 2xl:gap-4 min-w-0">

            {{-- Informasi Temuan --}}
            <section id="fd-informasi" aria-labelledby="fd-informasi-title" class="{{ $card }}">
                <div class="{{ $cardHeader }}">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h2 id="fd-informasi-title" class="fd-heading">Informasi Temuan</h2>
                </div>
                <dl class="{{ $cardBody }} grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-3 2xl:gap-y-4">
                    <div class="col-span-2 sm:col-span-3 min-w-0">
                        <dt class="fd-label">Deskripsi Temuan</dt>
                        <dd class="fd-text mt-1 px-3 py-2 2xl:py-2.5 bg-slate-50 border border-slate-200 rounded-lg">{{ $data['temuan'] ?? '-' }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Tanggal Temuan</dt>
                        <dd class="fd-value mt-0.5">{{ $formatDate($data['tanggal'] ?? '-') }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Fungsi</dt>
                        <dd class="fd-value mt-0.5">{{ $data['fungsi'] ?? '-' }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Pelapor</dt>
                        <dd class="fd-value mt-0.5">{{ $data['pelapor'] ?? '-' }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Kategori</dt>
                        <dd class="fd-value mt-0.5">{{ $data['kategori'] ?? '-' }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Unsafe Action</dt>
                        <dd class="fd-value mt-0.5">{{ $data['unsafe_action'] ?? '-' }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Unsafe Condition</dt>
                        <dd class="fd-value mt-0.5">{{ $data['unsafe_conditon'] ?? '-' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Galeri Foto: mengisi sisa tinggi kolom, foto tampil utuh (object-contain) --}}
            <section id="fd-galeri" aria-labelledby="fd-galeri-title" class="{{ $card }} flex flex-col lg:flex-1">
                <div class="{{ $cardHeader }}">
                    <svg class="w-5 h-5 text-purple-600 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <h2 id="fd-galeri-title" class="fd-heading">Galeri Foto</h2>
                </div>
                <div class="{{ $cardBody }} flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3 2xl:gap-4">
                    @foreach($photos as $photo)
                        <figure class="flex flex-col min-w-0">
                            <figcaption class="fd-label mb-1.5">{{ $photo['label'] }}</figcaption>
                            @if(!empty($photo['url']))
                                <a href="{{ $photo['url'] }}" target="_blank" rel="noopener noreferrer"
                                   class="fd-thumb group relative block h-48 lg:h-auto lg:flex-1 min-h-28 rounded-lg overflow-hidden border border-slate-300 bg-slate-100 hover:border-blue-500 transition-colors {{ $focusRing }}"
                                   title="Buka {{ $photo['label'] }} ukuran penuh"
                                   aria-label="Buka {{ $photo['label'] }} ukuran penuh di tab baru">
                                    <img src="{{ $photo['url'] }}" alt="{{ $photo['alt'] }}" loading="lazy"
                                         class="absolute inset-0 w-full h-full object-contain"
                                         onerror="this.classList.add('hidden'); this.nextElementSibling.classList.replace('hidden', 'flex');">
                                    <span class="hidden absolute inset-0 flex-col items-center justify-center gap-1 p-2 text-center">
                                        <svg class="w-6 h-6 text-blue-700" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        <span class="text-sm font-medium text-blue-700 underline">Buka URL {{ $photo['label'] }}</span>
                                    </span>
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/25 group-focus-visible:bg-black/25 transition-colors pointer-events-none" aria-hidden="true">
                                        <span class="opacity-0 group-hover:opacity-100 group-focus-visible:opacity-100 transition-opacity inline-flex items-center gap-1.5 rounded-full bg-black/80 px-3 py-1 text-sm font-medium text-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                            Perbesar
                                        </span>
                                    </span>
                                </a>
                            @else
                                <div class="fd-thumb h-48 lg:h-auto lg:flex-1 min-h-28 flex flex-col items-center justify-center gap-1 rounded-lg border border-dashed border-slate-300 bg-slate-50 text-slate-600">
                                    <svg class="w-6 h-6 text-slate-400" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <span class="text-sm">Tidak ada foto</span>
                                </div>
                            @endif
                        </figure>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- KOLOM KANAN: Penugasan -> Tindak Lanjut -> Validasi (urutan alur penanganan) --}}
        <div class="flex flex-col gap-3 2xl:gap-4 min-w-0">

            {{-- Penugasan --}}
            <section id="fd-penugasan" aria-labelledby="fd-penugasan-title" class="{{ $card }} lg:grow">
                <div class="{{ $cardHeader }}">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <h2 id="fd-penugasan-title" class="fd-heading">Penugasan</h2>
                </div>
                <dl class="{{ $cardBody }} grid grid-cols-2 gap-x-4 gap-y-3 2xl:gap-y-4">
                    <div class="min-w-0">
                        <dt class="fd-label">Assign Ke</dt>
                        <dd class="fd-value mt-0.5">{{ $data['assign'] ?? '-' }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Asset Owner</dt>
                        <dd class="fd-value mt-0.5">{{ $data['asset_owner'] ?? '-' }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Assign Date</dt>
                        <dd class="fd-value mt-0.5">{{ $formatDate($data['assigndate'] ?? '-') }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Target</dt>
                        <dd class="fd-value mt-0.5">{{ $formatDate($data['target'] ?? '-') }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Tindak Lanjut & SAP --}}
            <section id="fd-tindak-lanjut" aria-labelledby="fd-tindak-lanjut-title" class="{{ $card }} relative lg:grow">
                <div class="absolute inset-x-0 top-0 h-0.5 bg-blue-600" aria-hidden="true"></div>
                <div class="{{ $cardHeader }}">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <h2 id="fd-tindak-lanjut-title" class="fd-heading">Tindak Lanjut & SAP</h2>
                </div>
                <dl class="{{ $cardBody }} grid grid-cols-1 gap-y-3 2xl:gap-y-4">
                    <div class="min-w-0">
                        <dt class="fd-label">No. Notifikasi SAP</dt>
                        <dd class="mt-0.5">
                            @if(!empty($finding->no_notifikasi_sap))
                                <span class="fd-value inline-block font-mono font-semibold text-blue-800 bg-blue-50 border border-blue-200 rounded px-2 py-0.5">{{ $finding->no_notifikasi_sap }}</span>
                            @else
                                <span class="fd-value">-</span>
                            @endif
                        </dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Keterangan Tindak Lanjut</dt>
                        <dd class="fd-text mt-1 px-3 py-2 2xl:py-2.5 bg-slate-50 border border-slate-200 rounded-lg">{{ $finding->keterangan_tindak_lanjut ?? '-' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Validasi & Closing --}}
            <section id="fd-validasi" aria-labelledby="fd-validasi-title" class="{{ $card }} lg:grow">
                <div class="{{ $cardHeader }}">
                    <svg class="w-5 h-5 text-green-600 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h2 id="fd-validasi-title" class="fd-heading">Validasi & Closing</h2>
                </div>
                <dl class="{{ $cardBody }} grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-3 2xl:gap-y-4">
                    <div class="min-w-0">
                        <dt class="fd-label">Close By</dt>
                        <dd class="fd-value mt-0.5">{{ $data['closeby'] ?? '-' }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Close Fungsi</dt>
                        <dd class="fd-value mt-0.5">{{ $data['closefungsi'] ?? '-' }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">User Status</dt>
                        <dd class="fd-value mt-0.5">{{ $data['userstatus'] ?? '-' }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="fd-label">Verify By</dt>
                        <dd class="fd-value mt-0.5">{{ $data['verifyby'] ?? '-' }}</dd>
                    </div>
                    <div class="col-span-2 min-w-0">
                        <dt class="fd-label">Verify Date</dt>
                        <dd class="fd-value mt-0.5">{{ $formatDate($data['verifydate'] ?? '-') }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</div>
@endsection
