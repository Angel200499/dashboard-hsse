<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SipekaFinding;

class SipekaFindingController extends Controller
{
    public function index(Request $request)
    {
        $query = SipekaFinding::query();
        
        $user = auth()->user();
        if ($user->role === 'Admin Function' || $user->role === 'Manager Function') {
            $query->where('data_sipeka->fungsi', $user->fungsi);
        }
        
        if ($request->filled('search')) {
            $search = $request->get('search');
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
        
        $findings = $query->get();
        
        return view('pages.findings.index', compact('findings'));
    }

    public function update(Request $request, $id)
    {
        $finding = SipekaFinding::findOrFail($id);
        
        $user = auth()->user();
        if ($user->role !== 'Admin HSSE') {
            $data = is_array($finding->data_sipeka) ? $finding->data_sipeka : json_decode($finding->data_sipeka, true);
            $fungsi = $data['fungsi'] ?? '';
            
            if ($user->role !== 'Admin Function' || $user->fungsi !== $fungsi) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengubah data fungsi ini.');
            }
        }

        $request->validate([
            'no_notifikasi_sap' => 'nullable|string|max:255',
            'keterangan_tindak_lanjut' => 'nullable|string',
        ]);
        
        $finding->no_notifikasi_sap = $request->input('no_notifikasi_sap');
        $finding->keterangan_tindak_lanjut = $request->input('keterangan_tindak_lanjut');
        $finding->save();

        return redirect()->back()->with('success', 'Data Tindak Lanjut berhasil diperbarui!');
    }

    public function show($id)
    {
        $finding = SipekaFinding::findOrFail($id);
        
        $user = auth()->user();
        if ($user->role === 'Admin Function' || $user->role === 'Manager Function') {
            $data = is_array($finding->data_sipeka) ? $finding->data_sipeka : json_decode($finding->data_sipeka, true);
            $fungsi = $data['fungsi'] ?? '';
            
            if ($user->fungsi !== $fungsi) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat data fungsi ini.');
            }
        }
        
        return view('pages.findings.show', compact('finding'));
    }

    public function export(Request $request)
    {
        $fungsi = $request->get('fungsi');
        $user = auth()->user();
        
        // Security check
        if ($user->role === 'Admin Function' || $user->role === 'Manager Function') {
            if ($fungsi && $fungsi !== $user->fungsi) {
                abort(403, 'Unauthorized');
            }
            $fungsi = $user->fungsi; // Force to user's function
        }
        
        $fileName = 'Temuan_SIPEKA_' . ($fungsi ?: 'All') . '_' . date('Y-m-d') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SipekaFindingsExport($fungsi), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $fungsi = $request->get('fungsi');
        $user = auth()->user();
        
        // Security check
        if ($user->role === 'Admin Function' || $user->role === 'Manager Function') {
            if ($fungsi && $fungsi !== $user->fungsi) {
                abort(403, 'Unauthorized');
            }
            $fungsi = $user->fungsi;
        }
        
        if ($fungsi) {
            $findings = SipekaFinding::where('data_sipeka->fungsi', $fungsi)->get();
        } else {
            $findings = SipekaFinding::all();
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.findings_pdf', compact('findings', 'fungsi'))
                ->setPaper('a4', 'landscape');
                
        $fileName = 'Temuan_SIPEKA_' . ($fungsi ?: 'All') . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($fileName);
    }
}
