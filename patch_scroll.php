<?php

$sipekaCtrl = "c:/laragon/www/dashboard-hsse/app/Http/Controllers/SipekaFindingController.php";
$dashCtrl = "c:/laragon/www/dashboard-hsse/app/Http/Controllers/DashboardFunctionController.php";

// Patch SipekaFindingController
$ctrl1 = file_get_contents($sipekaCtrl);

$filterBlock1 = <<<'EOD'
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
        
        $findings = $query->get();
EOD;

$ctrl1 = preg_replace('/\$findings = \$query->paginate\(10\);/', $filterBlock1, $ctrl1);
file_put_contents($sipekaCtrl, $ctrl1);


// Patch DashboardFunctionController
$ctrl2 = file_get_contents($dashCtrl);

$filterBlock2 = <<<'EOD'
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

        $findingsPaginated = $query->get();
EOD;

$ctrl2 = preg_replace('/\$findingsPaginated = \$query->paginate\(10\);/', $filterBlock2, $ctrl2);
file_put_contents($dashCtrl, $ctrl2);


// Patch the views
$indexView = "c:/laragon/www/dashboard-hsse/resources/views/pages/findings/index.blade.php";
$dashView = "c:/laragon/www/dashboard-hsse/resources/views/pages/dashboard-fungsi.blade.php";

function patchView($file, $varName) {
    $content = file_get_contents($file);
    
    // Add date_filter select before search input
    $filterSelect = <<<'EOD'
            <select name="date_filter" class="bg-white border border-slate-300 text-slate-900 text-sm rounded-xl focus:ring-[#9DBF2A] focus:border-[#9DBF2A] block p-2.5 shadow-sm">
                <option value="">Semua Waktu</option>
                <option value="1_day" {{ request("date_filter") == "1_day" ? "selected" : "" }}>1 Hari Terakhir</option>
                <option value="3_days" {{ request("date_filter") == "3_days" ? "selected" : "" }}>3 Hari Terakhir</option>
                <option value="1_week" {{ request("date_filter") == "1_week" ? "selected" : "" }}>1 Minggu Terakhir</option>
                <option value="1_month" {{ request("date_filter") == "1_month" ? "selected" : "" }}>1 Bulan Terakhir</option>
            </select>
            <div class="absolute inset-y-0
EOD;
    $content = str_replace('<div class="absolute inset-y-0', $filterSelect, $content);
    
    // Remove Pagination links
    // First, let's just delete it regardless of line endings
    $content = preg_replace('/<div class="px-6 py-4 border-t border-slate-200 bg-white">\s*\{\{\s*\$' . $varName . '->links\(\)\s*\}\}\s*<\/div>/is', '', $content);
    
    // Fix firstItem() error since it is now a Collection not a LengthAwarePaginator
    $content = str_replace('{{ $' . $varName . '->firstItem() + $index }}', '{{ $index + 1 }}', $content);
    $content = str_replace('{{$' . $varName . '->firstItem() + $index}}', '{{ $index + 1 }}', $content);
    
    // Make table scrollable
    $content = str_replace('<div class="overflow-x-auto">', '<div class="overflow-x-auto overflow-y-auto max-h-[600px] relative">', $content);
    $content = str_replace('<thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">', '<thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200 sticky top-0 z-10 shadow-sm">', $content);
    
    file_put_contents($file, $content);
}

patchView($indexView, "findings");
patchView($dashView, "findingsPaginated");

echo "Patch complete.\n";
