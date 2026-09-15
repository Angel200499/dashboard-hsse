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

        // Validasi filter bulan (1–12) — hanya aktif jika tahun juga dipilih
        $bulanRaw      = $request->get('month');
        $bulan         = ($tahun && $bulanRaw && is_numeric($bulanRaw)
                          && (int) $bulanRaw >= 1 && (int) $bulanRaw <= 12)
                         ? (int) $bulanRaw
                         : null;

        // Filter Tahun mandiri untuk Rekap PEKA
        // Default ke tahun sekarang agar chart langsung tampil saat pertama buka
        $pekaYearRaw = $request->get('peka_year');
        $pekaYear    = ($pekaYearRaw && preg_match('/^\d{4}$/', $pekaYearRaw))
                       ? (int) $pekaYearRaw
                       : (int) now()->year;

        // -----------------------------------------------------------------
        // KPI — dihitung via SQL CASE WHEN (computed monitoring status)
        // Difilter by tahun jika ada.
        // KPI TIDAK dipengaruhi filter bulan — hanya tahun.
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
        // Charts — DashboardChartService, difilter by tahun + bulan
        // $fungsi = null → Global Dashboard (semua fungsi)
        // -----------------------------------------------------------------
        $charts = $this->chartService->getCharts(null, $tahun, $bulan, $pekaYear);

        // Filter yang dipilih dikirim ke view untuk UI
        $selectedYear     = $tahun;
        $selectedMonth    = $bulan;
        $selectedPekaYear = $pekaYear;

        return view('pages.dashboard', compact('kpi', 'charts', 'selectedYear', 'selectedMonth', 'selectedPekaYear'));
    }
}
