<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SipekaFinding;
use App\Models\MasterFunctionMapping;
use App\Services\DashboardChartService;
use App\Services\FindingQueryService;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardFunctionController extends Controller
{
    public function __construct(
        private readonly DashboardChartService $chartService,
        private readonly FindingQueryService   $queryService
    ) {}

    public function index(Request $request, ?string $nama_fungsi = null)
    {
        $user = auth()->user();

        // -----------------------------------------------------------------
        // Tentukan fungsi yang akan ditampilkan
        // -----------------------------------------------------------------
        if ($user->isHsseRole()) {
            // Admin HSSE / Manager HSSE bisa akses semua fungsi via URL
            $fungsi = $nama_fungsi ?? $user->fungsi;
        } else {
            // Admin Function / Manager Function hanya bisa akses fungsi sendiri
            $fungsi = $user->fungsi;
            if ($nama_fungsi && strtolower($nama_fungsi) !== strtolower($fungsi)) {
                abort(403, 'Anda hanya dapat mengakses Dashboard Fungsi Anda sendiri.');
            }
        }

        // Normalisasi nama fungsi (case-insensitive matching)
        $validFunctions = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
        $matched        = false;
        foreach ($validFunctions as $vf) {
            if (strtolower($vf) === strtolower($fungsi)) {
                $fungsi  = $vf;
                $matched = true;
                break;
            }
        }

        if (!$matched && $fungsi) {
            abort(404, 'Fungsi tidak ditemukan.');
        }

        // -----------------------------------------------------------------
        // Validasi filter tahun
        // -----------------------------------------------------------------
        $tahunRaw     = $request->get('year');
        $tahun        = ($tahunRaw && preg_match('/^\d{4}$/', $tahunRaw)) ? (int) $tahunRaw : null;
        $selectedYear = $tahun;

        // -----------------------------------------------------------------
        // Mapping-aware filter helper
        // Jika mapping tersedia → whereIn; jika belum → fallback LIKE
        // -----------------------------------------------------------------
        $sipValues  = MasterFunctionMapping::getSipekaValues($fungsi);
        $applyFungsiFilter = function ($query) use ($fungsi, $sipValues) {
            if (!empty($sipValues)) {
                $query->whereIn(
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"),
                    $sipValues
                );
            } else {
                // Fallback: mapping belum diinput Admin HSSE
                $query->where('data_sipeka->fungsi', 'like', "%{$fungsi}%");
            }
            return $query;
        };

        // -----------------------------------------------------------------
        // KPI — difilter by $fungsi + $tahun (jika ada)
        // KPI TIDAK berubah saat user search/filter tabel (query terpisah)
        // -----------------------------------------------------------------
        $kpiBase = $applyFungsiFilter(SipekaFinding::query());

        if ($tahun) {
            $kpiBase->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ?",
                ["%{$tahun}%"]
            );
        }

        $total = (clone $kpiBase)->count();

        $closed = (clone $kpiBase)->whereRaw(
            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'closed'"
        )->count();

        $inProgress = (clone $kpiBase)->whereRaw(
            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'open'"
        )->whereNotNull('no_notifikasi_sap')
         ->where('no_notifikasi_sap', '!=', '')
         ->count();

        $open = max(0, $total - $closed - $inProgress);

        $kpi = [
            'total'       => $total,
            'open'        => $open,
            'in_progress' => $inProgress,
            'closed'      => $closed,
        ];

        // -----------------------------------------------------------------
        // Charts — DashboardChartService yang sama dengan Global Dashboard
        // Difilter by $fungsi + $tahun → struktur $charts IDENTIK
        // -----------------------------------------------------------------
        $charts = $this->chartService->getCharts($fungsi, $tahun);

        // -----------------------------------------------------------------
        // Tabel — FindingQueryService
        // Search + Filter Status + Filter Tahun + Sort + Pagination bersamaan
        // Difilter by $fungsi terlebih dahulu menggunakan mapping-aware filter
        // -----------------------------------------------------------------
        $tableQuery        = $applyFungsiFilter(SipekaFinding::query());
        $findingsPaginated = $this->queryService->paginate($tableQuery, $request);

        return view('pages.dashboard-fungsi', compact(
            'kpi',
            'charts',
            'findingsPaginated',
            'fungsi',
            'selectedYear'
        ));
    }

    // -----------------------------------------------------------------
    // EXPORT PDF — Rekap Pelapor Business Support
    // -----------------------------------------------------------------

    /**
     * Generate dan download PDF Rekap Pelapor Business Support.
     *
     * Endpoint ini dikunci ke fungsi Business Support saja.
     * Data diambil dari service yang sama dengan dashboard agar konsisten.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function exportBusinessSupportReporterPdf(Request $request)
    {
        // Otorisasi: hanya user fungsi Business Support (Admin & Manager)
        $user = auth()->user();
        if (strtolower($user->fungsi) !== 'business support') {
            abort(403, 'Anda tidak memiliki akses ke rekap Business Support.');
        }

        $rekapPeriode = (string) $request->get('rekap_periode', '');
        $rekapBulan   = (int) $request->get('rekap_bulan', now()->month);
        $rekapTahun   = (int) $request->get('rekap_tahun', now()->year);
        $rekapSearch  = (string) $request->get('rekap_search', '');
        $rekapSearch  = trim($rekapSearch);

        $rekapPelapor = $this->chartService->getBusinessSupportReporterRecap(
            $rekapPeriode,
            $rekapBulan,
            $rekapTahun,
            $rekapSearch
        );

        $pdf = Pdf::loadView('pdf.business-support-rekap-pelapor', [
            'rekapPelapor'  => $rekapPelapor,
            'rekapPeriode'  => $rekapPeriode,
            'rekapBulan'    => $rekapBulan,
            'rekapTahun'    => $rekapTahun,
            'rekapSearch'   => $rekapSearch,
            'generatedAt'   => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        // Nama file dinamis berdasarkan periode
        $filename = $this->buildPdfFilename($rekapPeriode, $rekapBulan, $rekapTahun);

        return $pdf->download($filename);
    }

    // -----------------------------------------------------------------
    // HALAMAN REKAP PELAPOR — Business Support (halaman tersendiri)
    // -----------------------------------------------------------------

    /**
     * Halaman Rekap Pelapor Business Support.
     *
     * Accessible oleh:
     *   - Admin HSSE / Manager HSSE (global access)
     *   - Admin Function Business Support
     *   - Manager Function Business Support
     *
     * Tidak accessible oleh fungsi lain.
     */
    public function rekapPelapor(Request $request)
    {
        $user = auth()->user();

        // Otorisasi: hanya user fungsi Business Support (Admin & Manager)
        if (strtolower($user->fungsi) !== 'business support') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $rekapPeriode = (string) $request->get('rekap_periode', '');
        $rekapBulan   = (int) $request->get('rekap_bulan', now()->month);
        $rekapTahun   = (int) $request->get('rekap_tahun', now()->year);
        $rekapSearch  = (string) $request->get('rekap_search', '');
        $rekapSearch  = trim($rekapSearch);

        $rekapPelapor = $this->chartService->getBusinessSupportReporterRecap(
            $rekapPeriode,
            $rekapBulan,
            $rekapTahun,
            $rekapSearch
        );

        return view('pages.business-support.rekap-pelapor', compact(
            'rekapPelapor',
            'rekapPeriode',
            'rekapBulan',
            'rekapTahun',
            'rekapSearch'
        ));
    }

    /**
     * Bangun nama file PDF yang informatif berdasarkan periode.
     */
    private function buildPdfFilename(string $periode, int $bulan, int $tahun): string
    {
        if (empty($periode)) {
            return 'rekap-pelapor-business-support-semua-waktu.pdf';
        }

        if ($periode === 'per_bulan') {
            $paddedBulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            return "rekap-pelapor-business-support-{$tahun}-{$paddedBulan}.pdf";
        }

        $suffix = match ($periode) {
            '1_day'   => '1-hari-terakhir',
            '3_days'  => '3-hari-terakhir',
            '1_week'  => '1-minggu-terakhir',
            '1_month' => '1-bulan-terakhir',
            default   => 'semua-waktu',
        };

        return "rekap-pelapor-business-support-{$suffix}.pdf";
    }
}

