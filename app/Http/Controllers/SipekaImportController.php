<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SipekaFindingsImport;

class SipekaImportController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'sipeka_file' => 'required|mimes:xlsx,xls,csv|max:10240', // max 10MB
        ]);

        try {
            Excel::import(new SipekaFindingsImport, $request->file('sipeka_file'));
            
            return redirect()->back()->with('success', 'Data SIPEKA berhasil disinkronisasi!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengunggah file: ' . $e->getMessage());
        }
    }
}
