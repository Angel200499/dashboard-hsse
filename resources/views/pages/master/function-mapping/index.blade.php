@extends('layouts.app')

@section('title', 'Master Mapping Fungsi')

@section('content')
<div class="space-y-6">

    {{-- ================================================================
         HEADER
    ================================================================ --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Master Mapping Fungsi</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kelola pemetaan fungsi dari data PEKA ke fungsi utama Dashboard HSSE.
            </p>
        </div>
        <button
            id="btn-open-create"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#9DBF2A] hover:bg-[#8aaa22] text-white text-sm font-semibold rounded-lg shadow-md shadow-[#9DBF2A]/30 transition-all duration-200 active:scale-95"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Mapping
        </button>
    </div>


    {{-- ================================================================
         PANEL FUNGSI BELUM DIPETAKAN
    ================================================================ --}}
    @if($unmappedFungsi->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 border border-amber-200 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-sm font-semibold text-amber-800 mb-0.5">
                        Fungsi Belum Dipetakan
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-200 text-amber-800">
                            {{ $unmappedFungsi->count() }}
                        </span>
                    </h2>
                    <p class="text-xs text-amber-700 mb-3">
                        Terdapat {{ $unmappedFungsi->count() }} nilai FUNGSI dari data PEKA yang belum memiliki mapping.
                        Temuan dengan fungsi di bawah ini tidak akan muncul di Dashboard Fungsi mana pun hingga dipetakan.
                    </p>
                    <ul class="space-y-1">
                        @foreach($unmappedFungsi as $uf)
                            <li class="flex items-center gap-2 text-sm text-amber-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                                <code class="font-mono text-xs bg-amber-100 px-2 py-0.5 rounded border border-amber-200">{{ $uf }}</code>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @else
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-700 font-medium">Semua fungsi PEKA telah memiliki mapping.</p>
        </div>
    @endif

    {{-- ================================================================
         TABEL MAPPING
    ================================================================ --}}
    @php
        $fungsiColors = [
            'Operation'        => 'bg-blue-50 border-blue-200 text-blue-700',
            'Maintenance'      => 'bg-amber-50 border-amber-200 text-amber-700',
            'HSSE'             => 'bg-green-50 border-green-200 text-green-700',
            'Business Support' => 'bg-purple-50 border-purple-200 text-purple-700',
        ];
    @endphp

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Table Header Bar --}}
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-slate-800">Daftar Mapping Fungsi</h2>
                <p class="text-xs text-slate-400 mt-0.5">Total {{ $mappings->count() }} mapping terdaftar</p>
            </div>
        </div>

        @if($mappings->isEmpty())
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-20 px-6 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-700 mb-1">Belum ada mapping fungsi</h3>
                <p class="text-sm text-slate-400 mb-6 max-w-sm">
                    Tambahkan mapping untuk memetakan nilai FUNGSI dari data PEKA ke fungsi utama Dashboard HSSE.
                </p>
                <button
                    id="btn-open-create-empty"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#9DBF2A] hover:bg-[#8aaa22] text-white text-sm font-semibold rounded-lg shadow-md shadow-[#9DBF2A]/30 transition-all duration-200 active:scale-95"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Mapping
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-12">No</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fungsi PEKA</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fungsi Dashboard</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Terakhir Diperbarui</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($mappings as $idx => $mp)
                        @php
                            $badge = $fungsiColors[$mp->fungsi_dashboard] ?? 'bg-slate-100 border-slate-200 text-slate-700';
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <td class="px-5 py-3.5 text-slate-400 text-xs font-medium">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <code class="font-mono text-xs bg-slate-100 px-2 py-1 rounded text-slate-700 border border-slate-200">{{ $mp->fungsi_sipeka }}</code>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badge }}">
                                    {{ $mp->fungsi_dashboard }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm text-slate-500">{{ $mp->updated_at->translatedFormat('d M Y') }}</span>
                                <span class="text-xs text-slate-400 block">{{ $mp->updated_at->diffForHumans() }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                    {{-- Edit Button --}}
                                    <button
                                        type="button"
                                        class="btn-edit inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                                        data-id="{{ $mp->id }}"
                                        data-fungsi-sipeka="{{ $mp->fungsi_sipeka }}"
                                        data-fungsi-dashboard="{{ $mp->fungsi_dashboard }}"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>

                                    {{-- Delete Form --}}
                                    <form
                                        method="POST"
                                        action="{{ route('master.function-mapping.destroy', $mp->id) }}"
                                        class="form-delete"
                                        data-label="{{ $mp->fungsi_sipeka }}"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>

{{-- ================================================================
     MODAL TAMBAH MAPPING
================================================================ --}}
<div id="modal-create" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="modal-create-backdrop"></div>

    {{-- Modal Panel --}}
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Tambah Mapping Fungsi</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Petakan nilai FUNGSI PEKA ke fungsi utama dashboard</p>
                </div>
                <button type="button" id="btn-close-create" class="p-2 hover:bg-slate-100 rounded-lg text-slate-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form method="POST" action="{{ route('master.function-mapping.store') }}" id="form-create">
                @csrf
                <div class="px-6 py-5 space-y-5">

                    {{-- Fungsi SIPEKA --}}
                    <div>
                        <label for="create-fungsi-sipeka" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Fungsi PEKA <span class="text-red-500">*</span>
                        </label>
                        @if($unmappedFungsi->isEmpty())
                            {{-- Semua sudah dipetakan --}}
                            <div class="w-full text-sm border border-green-200 bg-green-50 rounded-lg px-3 py-2.5 text-green-700 flex items-center gap-2">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Semua fungsi PEKA sudah dipetakan.
                            </div>
                            {{-- Hidden input agar form tidak error --}}
                            <input type="hidden" name="fungsi_sipeka" value="">
                        @else
                            <select
                                id="create-fungsi-sipeka"
                                name="fungsi_sipeka"
                                required
                                class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                            >
                                <option value="" disabled {{ old('fungsi_sipeka') ? '' : 'selected' }}>— Pilih Fungsi PEKA —</option>
                                @foreach($unmappedFungsi as $uf)
                                    <option value="{{ $uf }}" {{ old('fungsi_sipeka') === $uf ? 'selected' : '' }}>
                                        {{ $uf }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <p class="text-xs text-slate-400 mt-1">Nilai FUNGSI asli dari data PEKA yang belum dipetakan</p>
                    </div>

                    {{-- Fungsi Dashboard --}}
                    <div>
                        <label for="create-fungsi-dashboard" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Fungsi Dashboard <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="create-fungsi-dashboard"
                            name="fungsi_dashboard"
                            required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                            <option value="" disabled selected>— Pilih Fungsi Dashboard —</option>
                            @foreach($fungsiList as $fn)
                                <option value="{{ $fn }}" {{ old('fungsi_dashboard') === $fn ? 'selected' : '' }}>{{ $fn }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Tujuan mapping di Dashboard HSSE</p>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" id="btn-cancel-create" class="px-4 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-white bg-[#9DBF2A] hover:bg-[#8aaa22] rounded-lg shadow-md shadow-[#9DBF2A]/30 transition-all active:scale-95">
                        Simpan Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL EDIT MAPPING
================================================================ --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" id="modal-edit-backdrop"></div>

    {{-- Modal Panel --}}
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Edit Mapping Fungsi</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Perbarui mapping fungsi yang sudah tersedia</p>
                </div>
                <button type="button" id="btn-close-edit" class="p-2 hover:bg-slate-100 rounded-lg text-slate-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form method="POST" id="form-edit" action="">
                @csrf
                @method('PUT')
                <div class="px-6 py-5 space-y-5">

                    {{-- Fungsi SIPEKA --}}
                    <div>
                        <label for="edit-fungsi-sipeka" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Fungsi PEKA <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="edit-fungsi-sipeka"
                            name="fungsi_sipeka"
                            required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                        <p class="text-xs text-slate-400 mt-1">Nilai FUNGSI asli dari data PEKA (tidak diubah)</p>
                    </div>

                    {{-- Fungsi Dashboard --}}
                    <div>
                        <label for="edit-fungsi-dashboard" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Fungsi Dashboard <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="edit-fungsi-dashboard"
                            name="fungsi_dashboard"
                            required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#9DBF2A]/50 focus:border-[#9DBF2A] transition"
                        >
                            <option value="" disabled>— Pilih Fungsi Dashboard —</option>
                            @foreach($fungsiList as $fn)
                                <option value="{{ $fn }}">{{ $fn }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" id="btn-cancel-edit" class="px-4 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-white bg-[#9DBF2A] hover:bg-[#8aaa22] rounded-lg shadow-md shadow-[#9DBF2A]/30 transition-all active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================================================================
     MODAL KONFIRMASI DELETE
================================================================ --}}
<div id="modal-delete" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
            <div class="px-6 py-5 text-center">
                <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-800 mb-1">Hapus Mapping Fungsi</h3>
                <p class="text-sm text-slate-500 mb-2" id="delete-confirm-text">
                    Apakah Anda yakin ingin menghapus mapping fungsi ini?
                </p>
                <p class="text-xs text-slate-400 mb-6">
                    Menghapus mapping tidak akan menghapus data temuan PEKA.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" id="btn-cancel-delete" class="flex-1 px-4 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="button" id="btn-confirm-delete" class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // UTILS
    // =====================================================
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.body.style.overflow = '';
    }

    // =====================================================
    // MODAL TAMBAH (Create)
    // =====================================================
    const btnOpenCreate      = document.getElementById('btn-open-create');
    const btnOpenCreateEmpty = document.getElementById('btn-open-create-empty');
    const btnCloseCreate     = document.getElementById('btn-close-create');
    const btnCancelCreate    = document.getElementById('btn-cancel-create');
    const backdropCreate     = document.getElementById('modal-create-backdrop');

    function openCreateModal()  { openModal('modal-create'); }
    function closeCreateModal() { closeModal('modal-create'); }

    if (btnOpenCreate)      btnOpenCreate.addEventListener('click', openCreateModal);
    if (btnOpenCreateEmpty) btnOpenCreateEmpty.addEventListener('click', openCreateModal);
    if (btnCloseCreate)     btnCloseCreate.addEventListener('click', closeCreateModal);
    if (btnCancelCreate)    btnCancelCreate.addEventListener('click', closeCreateModal);
    if (backdropCreate)     backdropCreate.addEventListener('click', closeCreateModal);

    // =====================================================
    // MODAL EDIT
    // =====================================================
    const btnCloseEdit       = document.getElementById('btn-close-edit');
    const btnCancelEdit      = document.getElementById('btn-cancel-edit');
    const backdropEdit       = document.getElementById('modal-edit-backdrop');
    const formEdit           = document.getElementById('form-edit');
    const editFungsiSipeka   = document.getElementById('edit-fungsi-sipeka');
    const editFungsiDashboard= document.getElementById('edit-fungsi-dashboard');

    function closeEditModal() { closeModal('modal-edit'); }

    if (btnCloseEdit)  btnCloseEdit.addEventListener('click', closeEditModal);
    if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEditModal);
    if (backdropEdit)  backdropEdit.addEventListener('click', closeEditModal);

    // Isi data ke form edit saat tombol Edit diklik
    document.querySelectorAll('.btn-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id              = this.dataset.id;
            const fungsiSipeka    = this.dataset.fungsiSipeka;
            const fungsiDashboard = this.dataset.fungsiDashboard;

            // Set action URL dengan ID record yang benar
            const baseUrl = '{{ url("/master/function-mapping") }}';
            formEdit.action = baseUrl + '/' + id;

            // Isi field
            editFungsiSipeka.value    = fungsiSipeka;
            editFungsiDashboard.value = fungsiDashboard;

            openModal('modal-edit');
        });
    });

    // =====================================================
    // MODAL DELETE CONFIRMATION
    // =====================================================
    const btnCancelDelete  = document.getElementById('btn-cancel-delete');
    const btnConfirmDelete = document.getElementById('btn-confirm-delete');
    const deleteText       = document.getElementById('delete-confirm-text');
    let   pendingDeleteForm = null;

    function closeDeleteModal() {
        closeModal('modal-delete');
        pendingDeleteForm = null;
    }

    if (btnCancelDelete) btnCancelDelete.addEventListener('click', closeDeleteModal);

    document.querySelectorAll('.form-delete').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const label = this.dataset.label;
            deleteText.textContent = 'Apakah Anda yakin ingin menghapus mapping "' + label + '"?';
            pendingDeleteForm = this;
            openModal('modal-delete');
        });
    });

    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', function () {
            if (pendingDeleteForm) {
                pendingDeleteForm.submit();
            }
        });
    }

    // =====================================================
    // BUKA MODAL CREATE OTOMATIS JIKA ADA VALIDATION ERROR
    // DAN BUKAN DARI FORM EDIT
    // =====================================================
    @if($errors->any() && old('_method') !== 'PUT')
        openCreateModal();
    @endif

    @if($errors->any() && old('_method') === 'PUT')
        // Re-open edit modal jika ada error dari update
        const oldSipeka    = "{{ old('fungsi_sipeka') }}";
        const oldDashboard = "{{ old('fungsi_dashboard') }}";

        document.querySelectorAll('.btn-edit').forEach(function (btn) {
            if (btn.dataset.fungsiSipeka === oldSipeka) {
                btn.click();
                if (editFungsiDashboard) editFungsiDashboard.value = oldDashboard;
            }
        });
    @endif

    // =====================================================
    // KEYBOARD: ESC untuk tutup semua modal
    // =====================================================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });

});
</script>
@endpush
