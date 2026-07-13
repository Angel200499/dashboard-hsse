<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SipekaFinding;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role === 'Admin Function' || $user->role === 'Manager Function') {
            return redirect()->route('dashboard.fungsi', $user->fungsi);
        }

        $findings = SipekaFinding::all();
        $fungsiList = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];

        // 1. KPIs
        $kpi = [
            'total' => $findings->count(),
            'open' => $findings->filter(function($item) {
                return strtolower($item->data_sipeka['status'] ?? '') === 'open';
            })->count(),
            'closed' => $findings->filter(function($item) {
                return strtolower($item->data_sipeka['status'] ?? '') === 'closed';
            })->count(),
        ];

        // Chart 1: Jumlah Pelaporan per Fungsi (Pie)
        $chartFungsi = [];
        foreach($fungsiList as $f) {
            $chartFungsi[$f] = $findings->where('data_sipeka.fungsi', $f)->count();
        }

        // Chart 2: Reporting Rate per Fungsi (Horizontal Bar)
        // Mock headcounts to calculate rate (Findings / Headcount * 100) or just use findings count
        // For demonstration to match the image scale (1.23, 3.53 etc), we'll mock a rate.
        $headcounts = ['Operation' => 50, 'Maintenance' => 30, 'HSSE' => 15, 'Business Support' => 20];
        $chartReportingRate = [];
        $totalFindings = 0; $totalHC = 0;
        foreach($fungsiList as $f) {
            $count = $chartFungsi[$f];
            $hc = $headcounts[$f];
            $chartReportingRate[$f] = $hc > 0 ? round($count / $hc, 2) : 0;
            $totalFindings += $count;
            $totalHC += $hc;
        }
        $areaLhdRate = $totalHC > 0 ? round($totalFindings / $totalHC, 2) : 0;
        $chartReportingRate['AREA LHD'] = $areaLhdRate;

        // Chart 3: Kategori PEKA (Pie)
        $kategoriList = ['Safe Action', 'Safe Condition', 'Unsafe Action', 'Unsafe Condition'];
        $chartKategori = [];
        foreach($kategoriList as $k) {
            $chartKategori[$k] = $findings->filter(function($item) use ($k) {
                return strtolower(trim($item->data_sipeka['kategori'] ?? '')) === strtolower($k);
            })->count();
        }

        // Chart 4: Keterlibatan dalam Observasi (Stacked)
        // Mock involvement percentages based on unique reporters
        $chartKeterlibatan = [];
        foreach($fungsiList as $f) {
            $fFindings = $findings->where('data_sipeka.fungsi', $f);
            $uniqueReporters = $fFindings->pluck('data_sipeka.pelapor')->filter()->unique()->count();
            $hc = $headcounts[$f];
            $invRate = $hc > 0 ? round(($uniqueReporters / $hc) * 100) : 0;
            if($invRate > 100) $invRate = 100; // Cap at 100%
            $chartKeterlibatan[$f] = $invRate;
        }

        // Chart 5: Rekap Persentase Temuan per Fungsi (Stacked)
        $chartPersentaseFungsi = [];
        foreach($fungsiList as $f) {
            $fFindings = $findings->where('data_sipeka.fungsi', $f);
            $total = $fFindings->count();
            $closed = $fFindings->filter(function($item) {
                return strtolower($item->data_sipeka['status'] ?? '') === 'closed';
            })->count();
            $closedPct = $total > 0 ? round(($closed / $total) * 100, 1) : 0;
            $openPct = $total > 0 ? (100 - $closedPct) : 0;
            $chartPersentaseFungsi[$f] = [
                'closed' => $closedPct,
                'open' => $openPct
            ];
        }

        // Chart 6: Rekap Persentase Penindak Lanjut (Donut)
        // Image shows % distribution by function for those that HAVE SAP Notification
        $sapFilledFindings = $findings->filter(function($item) {
            return !empty(trim($item->no_notifikasi_sap));
        });
        $totalSap = $sapFilledFindings->count();
        $chartTindakLanjut = [];
        foreach($fungsiList as $f) {
            $count = $sapFilledFindings->where('data_sipeka.fungsi', $f)->count();
            $chartTindakLanjut[$f] = $totalSap > 0 ? round(($count / $totalSap) * 100) : 0;
        }

        // Chart 7: Unsafe Action Category
        $uaCategories = [
            'Failure to Follow Procedure',
            'Using Improper PPE',
            'Improper Position for Task',
            'Improper Placement',
            'Operating Out of Standard',
            'Using Defective Tools/Equipments'
        ];
        $chartUnsafeAction = [];
        $totalUA = 0;
        foreach($uaCategories as $cat) {
            $count = $findings->filter(function($item) use ($cat) {
                return strpos(strtolower($item->data_sipeka['unsafe_action'] ?? ''), strtolower($cat)) !== false;
            })->count();
            $chartUnsafeAction[$cat] = $count;
            $totalUA += $count;
        }

        // Chart 8: Unsafe Condition Category
        $ucCategories = [
            'Inadequate PPE',
            'Poor Housekeeping',
            'Inadequate Integrity of Equipment',
            'Restricted Space of Action',
            'Inadequate Condition of Floor/Surface',
            'Incorrect Material',
            'Inadequate Operation Mode',
            'Inadequate Guards/Barriers',
            'Improper Measurement',
            'Defective Tools/Equipments',
            'Incorrect Tools/Equipments',
            'Inadequate Warning System'
        ];
        $chartUnsafeCondition = [];
        $totalUC = 0;
        foreach($ucCategories as $cat) {
            $count = $findings->filter(function($item) use ($cat) {
                return strpos(strtolower($item->data_sipeka['unsafe_conditon'] ?? ''), strtolower($cat)) !== false;
            })->count();
            $chartUnsafeCondition[$cat] = $count;
            $totalUC += $count;
        }

        $charts = [
            'fungsi' => $chartFungsi,
            'reporting' => $chartReportingRate,
            'kategori' => $chartKategori,
            'keterlibatan' => $chartKeterlibatan,
            'persentase_fungsi' => $chartPersentaseFungsi,
            'tindak_lanjut' => $chartTindakLanjut,
            'unsafe_action' => ['data' => $chartUnsafeAction, 'total' => $totalUA],
            'unsafe_condition' => ['data' => $chartUnsafeCondition, 'total' => $totalUC],
        ];

        return view('pages.dashboard', compact('kpi', 'charts'));
    }
}
