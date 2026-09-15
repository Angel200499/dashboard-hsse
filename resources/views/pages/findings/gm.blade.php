@extends('layouts.app')

@section('title', 'Temuan GM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Temuan GM</h1>
                <span class="px-2.5 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-md border border-blue-200">
                    SA / Superadmin
                </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">Daftar temuan yang termasuk dalam kelompok khusus GM.</p>
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
                                <a href="{{ route('findings.show', $finding->id) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium bg-slate-100 text-slate-700 rounded hover:bg-slate-200 transition-colors">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="24" class="px-6 py-8 text-center text-slate-500">
                                <p class="text-sm font-medium text-slate-900 mb-1">Tidak Ada Data</p>
                                <p class="text-sm text-slate-500 max-w-sm mx-auto">
                                    Belum ada data temuan untuk kelompok GM.
                                </p>
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
@endsection
