<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SipekaFinding;

class DashboardFunctionController extends Controller
{
    public function index($nama_fungsi = null)
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

        $findingsPaginated = SipekaFinding::where('data_sipeka->fungsi', $fungsi)->paginate(10);

        return view('pages.dashboard-fungsi', compact('kpi', 'findingsPaginated', 'fungsi'));
    }
}
