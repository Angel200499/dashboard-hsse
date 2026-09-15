<?php

namespace App\Http\Controllers;

use App\Models\SipekaFinding;
use App\Models\MasterFunctionMapping;
use App\Services\FindingQueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GmFindingController extends Controller
{
    public function __construct(
        private readonly FindingQueryService $queryService
    ) {}

    /**
     * Daftar temuan khusus kelompok GM (Superadmin only).
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Authorization: Hanya Admin HSSE (Superadmin)
        if ($user->role !== 'Admin HSSE') {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat halaman ini.');
        }

        $query = SipekaFinding::query();

        // 2. Filter data berdasarkan kelompok_khusus = 'GM'
        $gmSipekaValues = MasterFunctionMapping::getGmSipekaValues();
        
        if (!empty($gmSipekaValues)) {
            $query->whereIn(
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"),
                $gmSipekaValues
            );
        } else {
            // Fallback jika tidak ada data mapping GM sama sekali (kembalikan empty set)
            $query->whereRaw("1 = 0");
        }

        // 3. Terapkan search, filter, sort, paginate via service (sama seperti Monitoring Temuan)
        $findings = $this->queryService->paginate($query, $request);

        return view('pages.findings.gm', compact('findings'));
    }
}
