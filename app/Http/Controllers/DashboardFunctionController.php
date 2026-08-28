<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SipekaFinding;
use App\Models\MasterFunctionMapping;
use App\Services\DashboardChartService;
use App\Services\FindingQueryService;
use Illuminate\Support\Facades\DB;

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
}
