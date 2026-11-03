<?php
$pluginJs = "
{
    id: 'testPlugin',
    beforeDatasetsDraw: function(chart) {
        var s = chart.scales['x-axis-0'];
        var allProps = [];
        for (var k in s) allProps.push(k);
        var curr = Object.getPrototypeOf(s);
        while (curr) {
            allProps = allProps.concat(Object.getOwnPropertyNames(curr));
            curr = Object.getPrototypeOf(curr);
        }
        throw new Error('scale props: ' + allProps.filter(p => p.indexOf('Pixel') !== -1 || p.indexOf('pixel') !== -1).join(', '));
    }
}
";

$cfg = ['type' => 'bar', 'data' => ['labels' => ['Jan'], 'datasets' => [['data' => [1]]]]];
$json = json_encode($cfg);
$chartStr = substr($json, 0, -1) . ", plugins: [" . $pluginJs . "]}";

$postData = ['chart' => $chartStr, 'width' => 262, 'height' => 130];
$ch = curl_init('https://quickchart.io/chart');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);
file_put_contents('scratch/test_scale_pixel.png', $res);
