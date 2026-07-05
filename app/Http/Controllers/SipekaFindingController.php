<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SipekaFinding;

class SipekaFindingController extends Controller
{
    public function index(Request $request)
    {
        $query = SipekaFinding::query();
        
        // Simple search example if needed later
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('id_temuan', 'like', "%{$search}%")
                  ->orWhere('data_sipeka', 'like', "%{$search}%");
        }
        
        $findings = $query->paginate(10);
        
        return view('pages.findings.index', compact('findings'));
    }

    public function update(Request $request, $id)
    {
        $finding = SipekaFinding::findOrFail($id);
        
        $user = auth()->user();
        if ($user->role !== 'Admin HSSE') {
            if ($user->role !== 'Admin Function' || $user->fungsi !== $finding->fungsi) {
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
        return view('pages.findings.show', compact('finding'));
    }
}
