<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SipekaFinding;
use Illuminate\Support\Facades\DB;

$searchUpper = 'Barry';
$queryUpper = SipekaFinding::query()->where('data_sipeka->pelapor', 'like', "%{$searchUpper}%");

$searchLower = 'barry';
$queryLower = SipekaFinding::query()->where('data_sipeka->pelapor', 'like', "%{$searchLower}%");

echo "Search 'Barry': " . $queryUpper->count() . PHP_EOL;
echo "Search 'barry': " . $queryLower->count() . PHP_EOL;

// Cek dengan LOWER() 
$queryLowerFunc = SipekaFinding::query()->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))) LIKE ?", ["%{$searchLower}%"]);
echo "Search 'barry' with LOWER(): " . $queryLowerFunc->count() . PHP_EOL;
