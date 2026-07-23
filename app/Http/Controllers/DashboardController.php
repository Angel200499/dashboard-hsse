<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SipekaFinding;
use App\Services\DashboardChartService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardChartService $chartService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        // Redirect Function roles ke Dashboard Fungsi mereka
        if ($user->role === 'Admin Function' || $user->role === 'Manager Function') {
            return redirect()->route('dashboard.fungsi', $user->fungsi);
        }

        // Validasi filter tahun (4 digit, angka saja)
        $tahunRaw = $request->get('year');
        $tahun    = ($tahunRaw && preg_match('/^\d{4}$/', $tahunRaw)) ? (int) $tahunRaw : null;

        // -----------------------------------------------------------------
        // KPI — dihitung via SQL CASE WHEN (computed monitoring status)
        // Difilter by tahun jika ada
        // -----------------------------------------------------------------
        $baseKpi = SipekaFinding::query();
        if ($tahun) {
            $baseKpi->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ?",
                ["%{$tahun}%"]
            );
        }

        $total = (clone $baseKpi)->count();

        $closed = (clone $baseKpi)->whereRaw(
            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'closed'"
        )->count();

        $inProgress = (clone $baseKpi)->whereRaw(
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
        // Charts — DashboardChartService, difilter by tahun jika ada
        // $fungsi = null → Global Dashboard (semua fungsi)
        // -----------------------------------------------------------------
        $charts = $this->chartService->getCharts(null, $tahun);

        // Tahun yang dipilih dikirim ke view untuk ditampilkan di filter UI
        $selectedYear = $tahun;

        return view('pages.dashboard', compact('kpi', 'charts', 'selectedYear'));
    }
}
