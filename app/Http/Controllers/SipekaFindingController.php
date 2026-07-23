<?php

namespace App\Http\Controllers;

use App\Models\FindingHistory;
use App\Models\SipekaFinding;
use App\Services\ExportService;
use App\Services\FindingQueryService;
use App\Http\Requests\UpdateFindingRequest;
use Illuminate\Http\Request;

class SipekaFindingController extends Controller
{
    public function __construct(
        private readonly FindingQueryService $queryService,
        private readonly ExportService       $exportService
    ) {}

    /**
     * Daftar temuan dengan Search + Filter + Sort + Pagination bersamaan.
     * Role scope diterapkan otomatis (Function user hanya lihat fungsinya).
     */
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = SipekaFinding::query();

        // Terapkan scope berdasarkan role + fungsi (Business Rule #8)
        $this->queryService->applyRoleScope($query, $user);

        // Hitung KPI khusus fungsi ini (atau keseluruhan jika Admin HSSE)
        $kpiBase = clone $query;
        
        $total = (clone $kpiBase)->count();
        $closed = (clone $kpiBase)->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'closed'")->count();
        $inProgress = (clone $kpiBase)->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'open'")
                                      ->whereNotNull('no_notifikasi_sap')
                                      ->where('no_notifikasi_sap', '!=', '')
                                      ->count();
        $open = max(0, $total - $closed - $inProgress);
        
        $kpi = [
            'total' => $total,
            'open' => $open,
            'closed' => $closed
        ];

        // Terapkan search, filter, sort, paginate via service
        $findings = $this->queryService->paginate($query, $request);

        return view('pages.findings.index', compact('findings', 'kpi'));
    }

    /**
     * Detail satu finding.
     */
    public function show(int $id)
    {
        $finding = SipekaFinding::findOrFail($id);
        $user    = auth()->user();

        // Authorization: Role + Fungsi (Business Rule #8)
        if (!$user->canViewFinding($finding)) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat data fungsi ini.');
        }

        return view('pages.findings.show', compact('finding'));
    }

    /**
     * Update No. Notifikasi SAP dan Keterangan Tindak Lanjut.
     * Menyimpan audit trail ke finding_histories.
     */
    public function update(UpdateFindingRequest $request, int $id)
    {
        $finding = SipekaFinding::findOrFail($id);
        $user    = auth()->user();

        // Authorization: hanya Admin HSSE atau Admin Function fungsinya (Business Rule #8)
        if (!$user->canEditFinding($finding)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data fungsi ini.');
        }

        // Simpan nilai lama sebelum update (untuk audit trail)
        $oldNoSap        = $finding->no_notifikasi_sap;
        $oldKeterangan   = $finding->keterangan_tindak_lanjut;

        // Update field yang diperbolehkan
        $finding->no_notifikasi_sap        = $request->input('no_notifikasi_sap');
        $finding->keterangan_tindak_lanjut = $request->input('keterangan_tindak_lanjut');
        $finding->save();

        // Rekam riwayat perubahan (Business Rule #13)
        FindingHistory::create([
            'finding_id'                   => $finding->id,
            'updated_by'                   => $user->id,
            'old_no_notifikasi_sap'        => $oldNoSap,
            'new_no_notifikasi_sap'        => $finding->no_notifikasi_sap,
            'old_keterangan_tindak_lanjut' => $oldKeterangan,
            'new_keterangan_tindak_lanjut' => $finding->keterangan_tindak_lanjut,
        ]);

        return redirect()->back()->with('success', 'Data Tindak Lanjut berhasil diperbarui!');
    }

    /**
     * Export Excel — hasil Dashboard (Business Rule #7).
     * Menyertakan no_sap, keterangan, dan Monitoring Status.
     */
    public function export(Request $request)
    {
        $fungsi = $this->exportService->resolveExportFungsi($request->get('fungsi'));

        return $this->exportService->toExcel(
            fungsi:       $fungsi,
            statusFilter: $request->get('status_filter'),
            dateFilter:   $request->get('date_filter'),
            yearFilter:   $request->get('year'),
            search:       $request->get('search')
        );
    }

    /**
     * Export PDF.
     */
    public function exportPdf(Request $request)
    {
        $fungsi = $this->exportService->resolveExportFungsi($request->get('fungsi'));

        return $this->exportService->toPdf(
            fungsi:       $fungsi,
            statusFilter: $request->get('status_filter'),
            dateFilter:   $request->get('date_filter'),
            yearFilter:   $request->get('year'),
            search:       $request->get('search')
        );
    }
}
