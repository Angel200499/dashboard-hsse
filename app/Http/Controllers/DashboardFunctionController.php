<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SipekaFinding;

class DashboardFunctionController extends Controller
{
    public function index(\Illuminate\Http\Request $request, $nama_fungsi = null)
    {
        $user = auth()->user();
        
        // Determine which function to show
        if ($user->role === 'Admin HSSE' || $user->role === 'Manager HSSE') {
            // Can see any function dashboard
            $fungsi = $nama_fungsi ?? $user->fungsi;
        } else {
            // Function users can only see their own function
            $fungsi = $user->fungsi;
            if ($nama_fungsi && strtolower($nama_fungsi) !== strtolower($fungsi)) {
                abort(403, 'Anda hanya dapat mengakses Dashboard Fungsi Anda sendiri.');
            }
        }

        // Standardize function name formatting if needed (Operation, Maintenance, HSSE, Business Support)
        $validFunctions = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
        
        // Find matching case
        $matched = false;
        foreach($validFunctions as $vf) {
            if (strtolower($vf) === strtolower($fungsi)) {
                $fungsi = $vf;
                $matched = true;
                break;
            }
        }
        
        if (!$matched && $fungsi) {
            abort(404, 'Fungsi tidak ditemukan.');
        }

        $findings = SipekaFinding::where('data_sipeka->fungsi', $fungsi)->get();

        $kpi = [
            'total' => $findings->count(),
            'open' => 0,
            'closed' => 0,
        ];

        foreach ($findings as $finding) {
            $status = strtolower($finding->data_sipeka['status'] ?? '');
            if ($status === 'open') {
                $kpi['open']++;
            } elseif ($status === 'closed') {
                $kpi['closed']++;
            }
        }

                $query = SipekaFinding::where("data_sipeka->fungsi", $fungsi);
        
        if ($request->filled("search")) {
            $search = $request->get("search");
            $query->where(function($q) use ($search) {
                $q->where('id_temuan', 'like', "%{$search}%")
                  ->orWhere('no_notifikasi_sap', 'like', "%{$search}%")
                  ->orWhere('keterangan_tindak_lanjut', 'like', "%{$search}%")
                  ->orWhere('data_sipeka->temuan', 'like', "%{$search}%")
                  ->orWhere('data_sipeka->fungsi', 'like', "%{$search}%")
                  ->orWhere('data_sipeka->pelapor', 'like', "%{$search}%")
                  ->orWhere('data_sipeka->kategori', 'like', "%{$search}%")
                  ->orWhere('data_sipeka->unsafe_action', 'like', "%{$search}%")
                  ->orWhere('data_sipeka->unsafe_conditon', 'like', "%{$search}%")
                  ->orWhere('data_sipeka->status', 'like', "%{$search}%");
            });
        }

                if ($request->has("date_filter") && $request->get("date_filter") != "") {
            $filter = $request->get("date_filter");
            $date = null;
            if ($filter == "1_day") $date = now()->subDay();
            elseif ($filter == "3_days") $date = now()->subDays(3);
            elseif ($filter == "1_week") $date = now()->subWeek();
            elseif ($filter == "1_month") $date = now()->subMonth();

            if ($date) {
                $query->where("created_at", ">=", $date);
            }
        }

        // Filter by Status
        if ($request->filled('status_filter')) {
            $status = $request->get('status_filter');
            
            if ($status === 'in progress') {
                $query->where('data_sipeka->status', 'like', '%open%')
                      ->whereNotNull('no_notifikasi_sap')->where('no_notifikasi_sap', '!=', '')
                      ->whereNotNull('keterangan_tindak_lanjut')->where('keterangan_tindak_lanjut', '!=', '');
            } elseif ($status === 'open') {
                $query->where('data_sipeka->status', 'like', '%open%')
                      ->where(function($q) {
                          $q->whereNull('no_notifikasi_sap')->orWhere('no_notifikasi_sap', '')
                            ->orWhereNull('keterangan_tindak_lanjut')->orWhere('keterangan_tindak_lanjut', '');
                      });
            } elseif ($status === 'closed') {
                $query->where('data_sipeka->status', 'like', '%closed%');
            }
        }

        // Sorting Logic
        $sortBy = $request->get('sort_by');
        $sortDir = $request->get('sort_dir', 'asc');
        
        if ($sortBy === 'status') {
            $query->orderBy('data_sipeka->status', $sortDir);
        } elseif ($sortBy === 'no_sap') {
            $query->orderBy('no_notifikasi_sap', $sortDir);
        } elseif ($sortBy === 'keterangan') {
            $query->orderBy('keterangan_tindak_lanjut', $sortDir);
        } else {
            $query->latest(); // Default order
        }

        $findingsPaginated = $query->get();

        return view('pages.dashboard-fungsi', compact('kpi', 'findingsPaginated', 'fungsi'));
    }
}
