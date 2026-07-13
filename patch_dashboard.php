<?php
$indexFile = "c:/laragon/www/dashboard-hsse/resources/views/pages/findings/index.blade.php";
$dashboardFile = "c:/laragon/www/dashboard-hsse/resources/views/pages/dashboard-fungsi.blade.php";
$indexContent = file_get_contents($indexFile);
$dashboardContent = file_get_contents($dashboardFile);
preg_match("/<table class=\"w-full text-sm text-left text-slate-500 whitespace-nowrap\">(.*?)<\/table>/s", $indexContent, $tableMatch);
$tableHtml = $tableMatch[0];
$tableHtml = str_replace("\$findings as \$index", "\$findingsPaginated as \$index", $tableHtml);
$tableHtml = str_replace("\$findings->firstItem()", "\$findingsPaginated->firstItem()", $tableHtml);
$dashboardContent = preg_replace("/<table class=\"w-full text-sm text-left text-slate-500 whitespace-nowrap\">(.*?)<\/table>/s", $tableHtml, $dashboardContent);
preg_match("/<!-- Update Modal -->(.*?)@endsection/s", $indexContent, $modalMatch);
$modalHtml = $modalMatch[1];
$dashboardContent = str_replace("@endsection", "\n<!-- Update Modal -->" . $modalHtml . "\n@endsection", $dashboardContent);
file_put_contents($dashboardFile, $dashboardContent);
echo "Dashboard updated.\n";

