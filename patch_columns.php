<?php
$dashboardCtrlFile = "c:/laragon/www/dashboard-hsse/app/Http/Controllers/DashboardFunctionController.php";
$ctrl = file_get_contents($dashboardCtrlFile);

$ctrl = str_replace("public function index(\$nama_fungsi = null)", "public function index(\Illuminate\Http\Request \$request, \$nama_fungsi = null)", $ctrl);

$searchBlock = <<<EOD
        \$query = SipekaFinding::where("data_sipeka->fungsi", \$fungsi);
        
        if (\$request->has("search")) {
            \$search = \$request->get("search");
            \$query->where(function(\$q) use (\$search) {
                \$q->where("id_temuan", "like", "%{\$search}%")
                  ->orWhere("data_sipeka", "like", "%{\$search}%");
            });
        }

        \$findingsPaginated = \$query->paginate(10);
EOD;

$ctrl = preg_replace("/\\\$findingsPaginated = SipekaFinding::where\(\'data_sipeka->fungsi\', \\\$fungsi\)->paginate\(10\);/", $searchBlock, $ctrl);
file_put_contents($dashboardCtrlFile, $ctrl);

$indexView = "c:/laragon/www/dashboard-hsse/resources/views/pages/findings/index.blade.php";
$dashView = "c:/laragon/www/dashboard-hsse/resources/views/pages/dashboard-fungsi.blade.php";

function fixTable($file) {
    $content = file_get_contents($file);
    
    $headerSap = '<th scope="col" class="px-6 py-4 font-semibold text-blue-700 bg-blue-50">No. SAP</th>';
    $headerTindak = '<th scope="col" class="px-6 py-4 font-semibold text-blue-700 bg-blue-50 min-w-[200px]">Tindak Lanjut</th>';
    
    $content = str_replace($headerTindak . "\n", "", $content);
    $content = str_replace($headerTindak, "", $content);
    $content = str_replace($headerSap, $headerSap . "\n                        " . $headerTindak, $content);
    
    $rowSap = '<td class="px-6 py-4 font-mono font-bold text-blue-700 bg-blue-50/50">
                                {{ $finding->no_notifikasi_sap ?? \'-\' }}
                            </td>';
    $rowTindak = '<td class="px-6 py-4 text-slate-700 whitespace-normal min-w-[200px] bg-blue-50/50">
                                {{ $finding->keterangan_tindak_lanjut ?? \'-\' }}
                            </td>';
                            
    $content = str_replace($rowTindak . "\n", "", $content);
    $content = str_replace($rowTindak, "", $content);
    $content = str_replace($rowSap, $rowSap . "\n                            " . $rowTindak, $content);
    
    file_put_contents($file, $content);
}

fixTable($indexView);
fixTable($dashView);

$dashViewContent = file_get_contents($dashView);
$searchFormHtml = <<<EOD
        <div class="flex items-center gap-3">
            <form action="" method="GET" class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block w-full pl-10 p-2.5 shadow-sm" placeholder="Cari area, temuan...">
            </form>
            <button class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 shadow-sm">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter
            </button>
        </div>
EOD;

$dashViewContent = str_replace(
    "</div>\n    </div>\n\n    <!-- KPIs -->", 
    "</div>\n\n" . $searchFormHtml . "\n    </div>\n\n    <!-- KPIs -->", 
    $dashViewContent
);
// In case of carriage return line endings
$dashViewContent = str_replace(
    "</div>\r\n    </div>\r\n\r\n    <!-- KPIs -->", 
    "</div>\r\n\r\n" . $searchFormHtml . "\r\n    </div>\r\n\r\n    <!-- KPIs -->", 
    $dashViewContent
);

file_put_contents($dashView, $dashViewContent);

$indexViewContent = file_get_contents($indexView);
$indexViewContent = preg_replace("/<div class=\"flex items-center gap-3\">.*?<\/button>\s+<\/div>/s", $searchFormHtml, $indexViewContent);
file_put_contents($indexView, $indexViewContent);

echo "All done.\n";
