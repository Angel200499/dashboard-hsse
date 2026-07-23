<?php

namespace App\Services;

use App\Models\SipekaFinding;
use App\Exports\SipekaFindingsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ExportService
 *
 * Menangani logika export data SIPEKA ke Excel atau PDF.
 *
 * Business Rule #7:
 *   Export bukan raw Excel asli, melainkan hasil Dashboard.
 *   Artinya kolom no_notifikasi_sap dan keterangan_tindak_lanjut
 *   harus mengambil dari DB (nilai terbaru yang sudah diupdate manual),
 *   bukan dari data_sipeka JSON (nilai original dari Excel import).
 *
 * Export juga menyertakan kolom "Monitoring Status" (Computed).
 */
class ExportService
{
    /**
     * Export ke file Excel (.xlsx).
     *
     * @param  string|null $fungsi         Filter berdasarkan fungsi (null = semua)
     * @param  string|null $statusFilter   Filter computed status: open|in_progress|closed
     * @param  string|null $dateFilter     Filter tanggal: 1_day|3_days|1_week|1_month
     * @return BinaryFileResponse
     */
    public function toExcel(
        ?string $fungsi = null,
        ?string $statusFilter = null,
        ?string $dateFilter = null,
        ?string $yearFilter = null,
        ?string $search = null
    ): BinaryFileResponse {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        
        $fileName = 'Temuan_SIPEKA_'
            . ($fungsi ? str_replace(' ', '_', $fungsi) . '_' : 'All_')
            . now()->format('Y-m-d')
            . '.xlsx';

        return Excel::download(
            new SipekaFindingsExport($fungsi, $statusFilter, $dateFilter, $yearFilter, $search),
            $fileName
        );
    }

    /**
     * Export ke file PDF.
     *
     * @param  string|null $fungsi
     * @return \Illuminate\Http\Response
     */
    public function toPdf(
        ?string $fungsi = null,
        ?string $statusFilter = null,
        ?string $dateFilter = null,
        ?string $yearFilter = null,
        ?string $search = null
    ): \Illuminate\Http\Response
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '300');
        
        // Use FindingQueryService to apply filters identical to the table
        $queryService = app(\App\Services\FindingQueryService::class);
        $query = SipekaFinding::query();

        if ($fungsi) {
            $query->where('data_sipeka->fungsi', 'like', "%{$fungsi}%");
        }

        // Create a fake request to reuse applyFilters logic cleanly
        $request = new Request([
            'status_filter' => $statusFilter,
            'date_filter'   => $dateFilter,
            'year'          => $yearFilter,
            'search'        => $search,
        ]);
        
        $queryService->applyFilters($query, $request);

        $findings = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'exports.findings_pdf',
            compact('findings', 'fungsi')
        )->setPaper('a4', 'landscape');

        $fileName = 'Temuan_SIPEKA_'
            . ($fungsi ? str_replace(' ', '_', $fungsi) . '_' : 'All_')
            . now()->format('Y-m-d')
            . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * Validasi hak akses export berdasarkan Role + Fungsi.
     *
     * - Admin HSSE / Manager HSSE → boleh export semua atau by fungsi
     * - Admin Function / Manager Function → hanya boleh export fungsinya sendiri
     *
     * Mengembalikan fungsi yang tervalidasi (dipakai sebagai filter export).
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function resolveExportFungsi(?string $requestedFungsi): ?string
    {
        $user = Auth::user();

        if ($user->isHsseRole()) {
            // HSSE roles: bebas export semua atau filter by fungsi
            return $requestedFungsi ?: null;
        }

        // Function roles: paksa export hanya fungsinya sendiri
        if ($requestedFungsi && $requestedFungsi !== $user->fungsi) {
            abort(403, 'Anda hanya dapat mengekspor data fungsi Anda sendiri.');
        }

        return $user->fungsi;
    }
}
