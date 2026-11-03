<?php
$pluginJs = "
{
    beforeDatasetsDraw: function(chart) {
        var keys = Object.keys(chart);
        var proto = Object.getOwnPropertyNames(Object.getPrototypeOf(chart));
        throw new Error('keys: ' + keys.join(', ') + ' | proto: ' + proto.join(', '));
    }
}
";

$cfg = [
    'type' => 'bar',
    'data' => ['labels' => ['A'], 'datasets' => [['data' => [1]]]]
];
$json = json_encode($cfg);
$chartStr = substr($json, 0, -1) . ", plugins: [" . $pluginJs . "]}";

$postData = [
    'chart' => $chartStr,
    'width' => 262,
    'height' => 130,
];
$ch = curl_init('https://quickchart.io/chart');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);
file_put_contents('scratch/qc_proto.png', $res);
