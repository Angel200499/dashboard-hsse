@extends('layouts.app')

@section('title', 'Manajemen Temuan SIPEKA')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Manajemen Temuan SIPEKA</h1>
            <p class="text-sm text-slate-500 mt-1">Data temuan lapangan, update No. SAP, dan Keterangan Tindak Lanjut.</p>
        </div>
        
                <div class="flex items-center gap-3">
            <form action="" method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative w-full sm:w-auto">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block w-full pl-10 p-2.5 shadow-sm" placeholder="Cari area, temuan...">
                </div>

                <select name="status_filter" onchange="this.form.submit()" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm min-w-[120px]">
                    <option value="">Semua Status</option>
                    <option value="open" {{ request("status_filter") == "open" ? "selected" : "" }}>Open</option>
                    <option value="in progress" {{ request("status_filter") == "in progress" ? "selected" : "" }}>In Progress</option>
                    <option value="closed" {{ request("status_filter") == "closed" ? "selected" : "" }}>Closed</option>
                </select>

                <select name="date_filter" onchange="this.form.submit()" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm min-w-[120px]">
                    <option value="">Semua Waktu</option>
                    <option value="1_day" {{ request("date_filter") == "1_day" ? "selected" : "" }}>1 Hari Terakhir</option>
                    <option value="3_days" {{ request("date_filter") == "3_days" ? "selected" : "" }}>3 Hari Terakhir</option>
                    <option value="1_week" {{ request("date_filter") == "1_week" ? "selected" : "" }}>1 Minggu Terakhir</option>
                    <option value="1_month" {{ request("date_filter") == "1_month" ? "selected" : "" }}>1 Bulan Terakhir</option>
                </select>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <!-- Card 1: Total Temuan -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Total Temuan</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['total'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full w-fit">
                    Keseluruhan Data
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
        </div>

        <!-- Card 2: Status Open -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Open & In Progress</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['open'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-amber-600 bg-amber-50 px-2 py-1 rounded-full w-fit">
                    Butuh Tindak Lanjut
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 3: Status Closed -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden flex items-start justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500 mb-1">Status Closed</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ number_format($kpi['closed'] ?? 0) }}</h3>
                <div class="mt-2 flex items-center text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full w-fit">
                    Telah Diselesaikan
                </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] overflow-hidden">
        <div class="overflow-x-auto overflow-y-auto max-h-[600px] relative">
            <table class="w-full text-sm text-left text-slate-500 whitespace-nowrap">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200 sticky top-0 z-10 shadow-sm">
                    @php
                        function sortUrl($column) {
                            $currentSortBy = request('sort_by');
                            $currentSortDir = request('sort_dir', 'asc');
                            $newDir = ($currentSortBy === $column && $currentSortDir === 'asc') ? 'desc' : 'asc';
                            return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $newDir]);
                        }
                    @endphp
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">ID Temuan</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Tanggal</th>
                        <th scope="col" class="px-6 py-4 font-semibold min-w-[250px]">Temuan</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Fungsi</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Pelapor</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Kategori</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Unsafe Action</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Unsafe Condition</th>
                        <th scope="col" class="px-6 py-4 font-semibold">
                            <a href="{{ sortUrl('status') }}" class="flex items-center gap-1 hover:text-blue-600 transition-colors">
                                Status
                                @if(request('sort_by') === 'status')
                                    @if(request('sort_dir') === 'desc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-4 font-semibold">Assign</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Asset Owner</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Assign Date</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Target</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-blue-700 bg-blue-50">
                            <a href="{{ sortUrl('no_sap') }}" class="flex items-center gap-1 hover:text-blue-900 transition-colors">
                                No. SAP
                                @if(request('sort_by') === 'no_sap')
                                    @if(request('sort_dir') === 'desc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-4 font-semibold text-blue-700 bg-blue-50 min-w-[200px]">
                            <a href="{{ sortUrl('keterangan') }}" class="flex items-center gap-1 hover:text-blue-900 transition-colors">
                                Keterangan Tindak Lanjut
                                @if(request('sort_by') === 'keterangan')
                                    @if(request('sort_dir') === 'desc')
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    @else
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                    @endif
                                @else
                                    <svg class="w-3 h-3 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                                @endif
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-4 font-semibold">Close By</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Close Fungsi</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Verify By</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Verify Date</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Foto Temuan</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Foto Close</th>
                        <th scope="col" class="px-6 py-4 font-semibold">User Status</th>
                                                <th scope="col" class="px-6 py-4 font-semibold text-right sticky right-0 bg-slate-50">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($findings as $index => $finding)
                        @php
                            $data = $finding->data_sipeka ?? [];
                        @endphp
                        <tr class="bg-white border-b border-slate-100 hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">{{ $findings->firstItem() + $index }}</td>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $finding->id_temuan }}</td>
                            <td class="px-6 py-4">{{ $data['tanggal'] ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-normal min-w-[250px]">{{ $data['temuan'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['fungsi'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['pelapor'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['kategori'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['unsafe_action'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['unsafe_conditon'] ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @php 
                                    $computedStatus = strtolower($finding->monitoring_status);
                                @endphp
                                @if($computedStatus === 'closed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Closed</span>
                                @elseif($computedStatus === 'in progress')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">In Progress</span>
                                @elseif($computedStatus === 'open')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Open</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">{{ $finding->monitoring_status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $data['assign'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['asset_owner'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['assigndate'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['target'] ?? '-' }}</td>
                            <td class="px-6 py-4 font-mono font-bold text-blue-700 bg-blue-50/50">
                                {{ $finding->no_notifikasi_sap ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-700 whitespace-normal min-w-[200px] bg-blue-50/50">
                                {{ $finding->keterangan_tindak_lanjut ?? '-' }}
                            </td>
                            <td class="px-6 py-4">{{ $data['closeby'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['closefungsi'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['verifyby'] ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $data['verifydate'] ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if(!empty($data['fototemuan']))
                                    <a href="{{ $data['fototemuan'] }}" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
                                @else - @endif
                            </td>
                            <td class="px-6 py-4">
                                @if(!empty($data['fotoclose']))
                                    <a href="{{ $data['fotoclose'] }}" target="_blank" class="text-blue-600 hover:underline">Lihat</a>
                                @else - @endif
                            </td>
                            <td class="px-6 py-4">{{ $data['userstatus'] ?? '-' }}</td>
                                                        <td class="px-6 py-4 text-right sticky right-0 bg-white border-l border-slate-100 shadow-[-4px_0_6px_-2px_rgba(0,0,0,0.05)] whitespace-nowrap">
                                <a href="{{ route('findings.show', $finding->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium bg-slate-100 text-slate-700 rounded hover:bg-slate-200 transition-colors mr-2">Detail</a>
                                
                                @if(auth()->user()->canEditFinding($finding))
                                <button type="button" 
                                    onclick="openUpdateModal('{{ $finding->id }}', '{{ $finding->no_notifikasi_sap }}', '{{ addslashes($finding->keterangan_tindak_lanjut) }}')" 
                                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium bg-[#9DBF2A] text-white rounded hover:bg-[#8ca825] transition-colors uppercase tracking-wide">Update</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="24" class="px-6 py-8 text-center text-slate-500">
                                Belum ada data SIPEKA. Silakan import file Excel melalui Dashboard.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $findings->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800">Update Tindak Lanjut</h3>
            <button onclick="closeUpdateModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="updateForm" method="POST" action="">
            @csrf
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">No. Notifikasi SAP</label>
                    <input type="text" name="no_notifikasi_sap" id="modal_sap" class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5" placeholder="Masukkan No SAP...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Keterangan Tindak Lanjut</label>
                    <textarea name="keterangan_tindak_lanjut" id="modal_keterangan" rows="4" class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2.5" placeholder="Tuliskan progress tindak lanjut..."></textarea>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="closeUpdateModal()" class="px-4 py-2.5 text-sm font-medium text-slate-500 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-700 rounded-lg transition-colors uppercase tracking-wide">CANCEL</button>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-[#9DBF2A] hover:bg-[#8ca825] rounded-lg transition-colors uppercase tracking-wide">SAVE</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUpdateModal(id, sap, keterangan) {
        document.getElementById('updateModal').classList.remove('hidden');
        document.getElementById('modal_sap').value = sap || '';
        document.getElementById('modal_keterangan').value = keterangan || '';
        
        // Setup form action route
        const form = document.getElementById('updateForm');
        form.action = `/findings/${id}/update`;
    }

    function closeUpdateModal() {
        document.getElementById('updateModal').classList.add('hidden');
    }
</script>
@endsection
