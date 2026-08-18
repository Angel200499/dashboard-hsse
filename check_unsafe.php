<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = App\Models\SipekaFinding::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_action')) IN ('Kondisi aman', 'Kondisi tidak aman', 'Tindakan aman', 'Tindakan tidak aman')")
->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_conditon')) IN ('Kondisi aman', 'Kondisi tidak aman', 'Tindakan aman', 'Tindakan tidak aman')")
->first();
if ($r) echo json_encode($r->data_sipeka, JSON_PRETTY_PRINT);
else echo "None found.\n";
