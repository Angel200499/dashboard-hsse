@extends('layouts.app')

@section('title', 'Detail Temuan - ' . $finding->id_temuan)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('findings.index') }}" class="inline-flex items-center justify-center p-2 text-slate-400 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Detail Temuan</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1 ml-12">ID: <span class="font-mono text-slate-700">{{ $finding->id_temuan }}</span></p>
        </div>
        
        @php
            $data = $finding->data_sipeka ?? [];
            $status = strtolower($data['status'] ?? '');
        @endphp
        
        <div class="flex items-center gap-3">
            @if($status === 'open')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-amber-100 text-amber-800 border border-amber-200 shadow-sm">Status: OPEN</span>
            @elseif($status === 'closed')
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 border border-green-200 shadow-sm">Status: CLOSED</span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-slate-100 text-slate-800 border border-slate-200 shadow-sm">Status: {{ $data['status'] ?? '-' }}</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column (Main Info) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Section 1: Informasi Temuan -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Informasi Temuan
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="sm:col-span-2">
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Deskripsi Temuan</h4>
                            <p class="text-sm font-medium text-slate-900 bg-slate-50 p-4 rounded-lg border border-slate-100 whitespace-pre-wrap">{{ $data['temuan'] ?? '-' }}</p>
                        </div>
                        
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal Temuan</h4>
                            <p class="text-sm font-medium text-slate-900">{{ $data['tanggal'] ?? '-' }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Fungsi</h4>
                            <p class="text-sm font-medium text-slate-900">{{ $data['fungsi'] ?? '-' }}</p>
                        </div>
                        
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Pelapor</h4>
                            <p class="text-sm font-medium text-slate-900">{{ $data['pelapor'] ?? '-' }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Kategori</h4>
                            <p class="text-sm font-medium text-slate-900">{{ $data['kategori'] ?? '-' }}</p>
                        </div>
                        
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Unsafe Action</h4>
                            <p class="text-sm font-medium text-slate-900">{{ $data['unsafe_action'] ?? '-' }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Unsafe Condition</h4>
                            <p class="text-sm font-medium text-slate-900">{{ $data['unsafe_conditon'] ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Progress & SAP (Editable via Modal on index) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden relative">
                <div class="absolute inset-x-0 top-0 h-1 bg-blue-500"></div>
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Tindak Lanjut & SAP
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">No. Notifikasi SAP</h4>
                            <p class="text-lg font-mono font-bold text-blue-700 bg-blue-50 p-3 rounded-lg inline-block">{{ $finding->no_notifikasi_sap ?? '-' }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Keterangan Tindak Lanjut</h4>
                            <p class="text-sm font-medium text-slate-900 bg-slate-50 p-4 rounded-lg border border-slate-100 whitespace-pre-wrap">{{ $finding->keterangan_tindak_lanjut ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column (Sidebar Info) -->
        <div class="space-y-6">
            
            <!-- Section 3: Informasi Penugasan -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Penugasan
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Assign Ke</h4>
                        <p class="text-sm font-medium text-slate-900">{{ $data['assign'] ?? '-' }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Asset Owner</h4>
                        <p class="text-sm font-medium text-slate-900">{{ $data['asset_owner'] ?? '-' }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Assign Date</h4>
                        <p class="text-sm font-medium text-slate-900">{{ $data['assigndate'] ?? '-' }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Target Penyelesaian</h4>
                        <p class="text-sm font-medium text-slate-900">{{ $data['target'] ?? '-' }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Section 4: Validasi & Penutupan -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Validasi & Closing
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Close By</h4>
                        <p class="text-sm font-medium text-slate-900">{{ $data['closeby'] ?? '-' }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Close Fungsi</h4>
                        <p class="text-sm font-medium text-slate-900">{{ $data['closefungsi'] ?? '-' }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Verify By</h4>
                        <p class="text-sm font-medium text-slate-900">{{ $data['verifyby'] ?? '-' }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Verify Date</h4>
                        <p class="text-sm font-medium text-slate-900">{{ $data['verifydate'] ?? '-' }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">User Status</h4>
                        <p class="text-sm font-medium text-slate-900">{{ $data['userstatus'] ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Section 5: Galeri Foto -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Galeri Foto
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Foto Temuan (Sebelum)</h4>
                        @if(!empty($data['fototemuan']))
                            <a href="{{ $data['fototemuan'] }}" target="_blank" class="block group relative rounded-lg overflow-hidden border border-slate-200">
                                <div class="aspect-video bg-slate-100 flex flex-col items-center justify-center text-slate-500 group-hover:bg-slate-200 transition-colors">
                                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                    <span class="text-sm font-medium">Buka URL Foto Temuan</span>
                                </div>
                            </a>
                        @else
                            <div class="aspect-video bg-slate-50 rounded-lg border border-slate-200 border-dashed flex items-center justify-center text-slate-400 text-sm">
                                Tidak ada foto
                            </div>
                        @endif
                    </div>
                    
                    <div>
                        <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Foto Close (Sesudah)</h4>
                        @if(!empty($data['fotoclose']))
                            <a href="{{ $data['fotoclose'] }}" target="_blank" class="block group relative rounded-lg overflow-hidden border border-slate-200">
                                <div class="aspect-video bg-slate-100 flex flex-col items-center justify-center text-slate-500 group-hover:bg-slate-200 transition-colors">
                                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                    <span class="text-sm font-medium">Buka URL Foto Close</span>
                                </div>
                            </a>
                        @else
                            <div class="aspect-video bg-slate-50 rounded-lg border border-slate-200 border-dashed flex items-center justify-center text-slate-400 text-sm">
                                Tidak ada foto
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
