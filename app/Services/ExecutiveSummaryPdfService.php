<?php

namespace App\Services;

use App\Models\SipekaFinding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class ExecutiveSummaryPdfService
{
    public function __construct(
        private readonly DashboardChartService $chartService
    ) {}

    /**
     * Generate Executive Summary PDF (2 Pages) matching Mentor's Reference.
     */
    public function generate(int $tahun = 2026, ?int $bulan = null)
    {
        // 1. Validasi & normalisasi periode
        $maxBulan = SipekaFinding::maxBulanTahun($tahun);
        if ($bulan === null) {
            $bulan = $maxBulan ?? (int) now()->month;
        } elseif ($maxBulan !== null && $bulan > $maxBulan) {
            $bulan = $maxBulan;
        }

        $bulanNama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $namaBulan = $bulanNama[$bulan] ?? 'Bulan ' . $bulan;
        $periodeLabel = "ytd. {$namaBulan} {$tahun}";

        // 2. Ambil data chart YTD dari DashboardChartService
        $charts = $this->chartService->getChartsYtd(null, $tahun, $bulan);

        // 3. Ambil KPI global YTD untuk periode ini (Januari s/d $bulan)
        $totalTemuan = (int) ($charts['trending']['annual_total'] ?? 0);
        $totalClosed = (int) ($charts['trending']['closed_total'] ?? 0);
        $closingRate = (float) ($charts['trending']['closing_rate'] ?? 0);

        // 4. Hitung Insights aktual untuk Section 1 - 6
        $insights = $this->buildInsights($charts, $totalTemuan, $totalClosed, $closingRate);

        // 5. Generate Chart Images via QuickChart (parallel curl_multi)
        $chartImages = $this->buildChartImages($charts, $tahun, $bulan, $closingRate, $insights['avgKet'] ?? 30);

        // 6. Siapkan data untuk Blade template
        $data = [
            'tahun'        => $tahun,
            'bulan'        => $bulan,
            'namaBulan'    => $namaBulan,
            'periodeLabel' => $periodeLabel,
            'charts'       => $charts,
            'insights'     => $insights,
            'chartImages'  => $chartImages,
            'closingRate'  => $closingRate,
            'totalTemuan'  => $totalTemuan,
            'totalClosed'  => $totalClosed,
        ];

        // 7. Render PDF via DomPDF
        $pdf = Pdf::loadView('pdf.executive-summary', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true);

        return $pdf;
    }

    /**
     * Hitung insights dari data aktual tanpa dummy text.
     */
    private function buildInsights(array $charts, int $totalTemuan, int $totalClosed, float $closingRate): array
    {
        // Section 1: Rekap PEKA
        $s1 = "Jumlah Pelaporan PEKA {$totalTemuan} laporan dengan <i>closing rate " . number_format($closingRate, 2, ',', '.') . "%</i> ({$totalClosed} laporan)";

        // Section 2: Reporting Rate
        $rrLhd = $charts['reporting_lhd']['rate'] ?? 0;
        $rrData = $charts['reporting']['data'] ?? [];
        $maxRrFungsi = '-';
        $maxRrVal = 0;
        $allAboveOne = true;
        foreach ($rrData as $f => $val) {
            if ($f === 'AREA LHD') continue;
            if ($val !== null && $val > $maxRrVal) {
                $maxRrVal = $val;
                $maxRrFungsi = $f;
            }
            if ($val === null || $val < 1) {
                $allAboveOne = false;
            }
        }
        $s2_1 = "<i>Reporting Rate (RR) Area LHD</i> " . number_format($rrLhd, 2, ',', '.');
        $s2_2 = "<i>RR tertinggi di Fungsi {$maxRrFungsi}</i> " . number_format($maxRrVal, 2, ',', '.');
        $s2_3 = $allAboveOne ? "Seluruh Fungsi memiliki nilai RR > 1" : "Beberapa Fungsi memiliki nilai RR < 1";

        // Section 3: Kategori PEKA
        $kat = $charts['kategori'] ?? [];
        $posTotal = ($kat['Safe Action'] ?? 0) + ($kat['Safe Condition'] ?? 0);
        $negTotal = ($kat['Unsafe Action'] ?? 0) + ($kat['Unsafe Condition'] ?? 0);
        $katGrand = $posTotal + $negTotal;
        $posPct = $katGrand > 0 ? round(($posTotal / $katGrand) * 100, 2) : 0;
        $negPct = $katGrand > 0 ? round(($negTotal / $katGrand) * 100, 2) : 0;
        $s3_1 = "Jumlah laporan positif " . number_format($posPct, 2, ',', '.') . " %";
        $s3_2 = "Jumlah laporan negatif " . number_format($negPct, 2, ',', '.') . " %";

        // Section 4: Rekap % Temuan Fungsi
        $ptFungsi = $charts['persentase_fungsi'] ?? [];
        $maxCloseFungsi = '-';
        $maxClosePct = 0;
        foreach ($ptFungsi as $f => $v) {
            $closed = $v['closed'] ?? 0;
            if ($closed > $maxClosePct) {
                $maxClosePct = $closed;
                $maxCloseFungsi = $f;
            }
        }
        $s4 = "% Closing Temuan Aset Owner terbanyak di Fungsi {$maxCloseFungsi} (" . number_format($maxClosePct, 1, ',', '.') . "%)";

        // Section 5: Rekap % Penindak Lanjut
        $tl = $charts['tindak_lanjut'] ?? [];
        $tlTotal = array_sum($tl);
        if ($tlTotal === 0) {
            $s5 = "Dari sisi jumlah tindak lanjut, belum ada temuan berstatus SAP untuk periode ini.";
        } else {
            arsort($tl);
            $topFungsi = array_slice($tl, 0, 2, true);
            $top1Name = array_keys($topFungsi)[0] ?? '-';
            $top1Val = $topFungsi[$top1Name] ?? 0;
            $top1Pct = round(($top1Val / $tlTotal) * 100);
            $top2Name = array_keys($topFungsi)[1] ?? '-';
            $top2Val = $topFungsi[$top2Name] ?? 0;
            $top2Pct = round(($top2Val / $tlTotal) * 100);
            $s5 = "Dari sisi jumlah tindak lanjut, Fungsi {$top1Name} terbanyak melakukan tindak lanjut dengan {$top1Val} laporan ({$top1Pct}%) dan {$top2Name} dengan {$top2Val} laporan ({$top2Pct}%)";
        }

        // Section 6: Keterlibatan
        $ket = $charts['keterlibatan'] ?? [];
        $maxKetFungsi = '-';
        $maxKetVal = 0;
        $ketSum = 0;
        $ketCount = 0;
        foreach ($ket as $f => $val) {
            if ($val !== null) {
                if ($val > $maxKetVal) {
                    $maxKetVal = $val;
                    $maxKetFungsi = $f;
                }
                $ketSum += $val;
                $ketCount++;
            }
        }
        $avgKet = $ketCount > 0 ? round($ketSum / $ketCount) : 0;
        $s6_1 = "Dilihat dari orang yang pernah melapor, keterlibatan pelaporan PEKA tertinggi di Fungsi {$maxKetFungsi} sebesar " . round($maxKetVal) . "%";
        $s6_2 = "Secara average keterlibatan pelaporan PEKA di angka {$avgKet}%";

        return [
            's1'     => $s1,
            's2_1'   => $s2_1,
            's2_2'   => $s2_2,
            's2_3'   => $s2_3,
            's3_1'   => $s3_1,
            's3_2'   => $s3_2,
            's4'     => $s4,
            's5'     => $s5,
            's6_1'   => $s6_1,
            's6_2'   => $s6_2,
            'avgKet' => $avgKet,
        ];
    }

    /**
     * Build QuickChart configurations for all 8 charts and fetch images concurrently.
     */
    private function buildChartImages(array $charts, int $tahun, int $bulan, float $closingRate, int $avgKet = 30): array
    {
        $configs = [];

        // -------------------------------------------------------------
        // Chart 1: Rekap PEKA (Trending Temuan Bulanan Stacked + Line)
        // -------------------------------------------------------------
        $trendMonths = $charts['trending']['months'] ?? [];
        $c1Labels = [];
        $c1SafeAction = [];
        $c1SafeCondition = [];
        $c1UnsafeAction = [];
        $c1UnsafeCondition = [];
        $c1Total = [];

        $mIdx = 0;
        foreach ($trendMonths as $item) {
            $mIdx++;
            $c1Labels[] = $item['month'] ?? '';

            if ($mIdx > $bulan) {
                // Bulan setelah cutoff YTD: tetap tampilkan sumbu label, tapi data kosong
                $c1SafeAction[] = 0;
                $c1SafeCondition[] = 0;
                $c1UnsafeAction[] = 0;
                $c1UnsafeCondition[] = 0;
                $c1Total[] = null;
            } else {
                $sa = (int) ($item['safe_action'] ?? 0);
                $sc = (int) ($item['safe_condition'] ?? 0);
                $ua = (int) ($item['unsafe_action'] ?? 0);
                $uc = (int) ($item['unsafe_condition'] ?? 0);
                $tot = (int) ($item['total'] ?? ($sa + $sc + $ua + $uc));

                $c1SafeAction[] = $sa;
                $c1SafeCondition[] = $sc;
                $c1UnsafeAction[] = $ua;
                $c1UnsafeCondition[] = $uc;
                $c1Total[] = $tot > 0 ? $tot : null;
            }
        }

        $configs['chart1'] = [
            'w' => 420, 'h' => 205,
            'cfg' => [
                'type' => 'bar',
                'data' => [
                    'labels' => $c1Labels,
                    'datasets' => [
                        [
                            'type' => 'bar',
                            'label' => 'Safe Action',
                            'data' => $c1SafeAction,
                            'backgroundColor' => '#5AA2D7',
                            'stack' => 'stack0',
                        ],
                        [
                            'type' => 'bar',
                            'label' => 'Safe Condition',
                            'data' => $c1SafeCondition,
                            'backgroundColor' => '#FFF2CC',
                            'borderColor' => '#FFE599',
                            'borderWidth' => 1,
                            'stack' => 'stack0',
                        ],
                        [
                            'type' => 'bar',
                            'label' => 'Unsafe Action',
                            'data' => $c1UnsafeAction,
                            'backgroundColor' => '#F8CBAD',
                            'borderColor' => '#F4B084',
                            'borderWidth' => 1,
                            'stack' => 'stack0',
                        ],
                        [
                            'type' => 'bar',
                            'label' => 'Unsafe Condition',
                            'data' => $c1UnsafeCondition,
                            'backgroundColor' => '#A9D18E',
                            'borderColor' => '#8EA9DB',
                            'borderWidth' => 1,
                            'stack' => 'stack0',
                        ],
                        [
                            'type' => 'line',
                            'label' => 'Total',
                            'data' => $c1Total,
                            'borderColor' => '#C00000',
                            'backgroundColor' => '#C00000',
                            'fill' => false,
                            'borderWidth' => 2,
                            'pointRadius' => 3,
                            'pointBackgroundColor' => '#C00000',
                            'yAxisID' => 'y',
                        ],
                    ]
                ],
                'options' => [
                    'legend' => [
                        'position' => 'bottom',
                        'labels' => ['fontSize' => 6.5, 'boxWidth' => 7, 'padding' => 2]
                    ],
                    'scales' => [
                        'xAxes' => [['stacked' => true, 'gridLines' => ['display' => false], 'ticks' => ['fontSize' => 7]]],
                        'yAxes' => [[
                            'id' => 'y',
                            'stacked' => true,
                            'scaleLabel' => [
                                'display' => true,
                                'labelString' => 'Temuan',
                                'fontSize' => 8,
                                'fontStyle' => 'bold',
                                'fontColor' => '#555555'
                            ],
                            'ticks' => ['beginAtZero' => true, 'fontSize' => 7]
                        ]]
                    ],
                    'plugins' => ['datalabels' => ['display' => false]]
                ]
            ]
        ];

        // -------------------------------------------------------------
        // Chart 2: Reporting Rate per Fungsi (Horizontal Bar with JS functions)
        // -------------------------------------------------------------
        $rrDataMap = $charts['reporting']['data'] ?? [];
        $rrLhdRate = $charts['reporting_lhd']['rate'] ?? ($rrDataMap['AREA LHD'] ?? 0);
        $rrLabels = ['AREA LHD', 'Operation', 'Maintenance', 'HSSE', 'Bussiness Support'];
        $rrData = [
            round((float) $rrLhdRate, 2),
            round((float) ($rrDataMap['Operation'] ?? 0), 2),
            round((float) ($rrDataMap['Maintenance'] ?? 0), 2),
            round((float) ($rrDataMap['HSSE'] ?? 0), 2),
            round((float) ($rrDataMap['Business Support'] ?? ($rrDataMap['Bussiness Support'] ?? 0)), 2)
        ];
        $maxVal = max($rrData);
        $xMax = ceil(max(6, $maxVal + 1));
        $rrDataJson = json_encode($rrData);

        $configs['chart2'] = [
            'w' => 380, 'h' => 200,
            'cfg' => "{
                type: 'horizontalBar',
                data: {
                    labels: ['AREA LHD', 'Operation', 'Maintenance', 'HSSE', 'Bussiness Support'],
                    datasets: [{
                        data: {$rrDataJson},
                        backgroundColor: ['#001F5B', '#2B579A', '#ED7D31', '#A6A6A6', '#FFC000'],
                        borderWidth: 0
                    }]
                },
                options: {
                    legend: { display: false },
                    scales: {
                        xAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: {$xMax},
                                stepSize: 1,
                                fontSize: 7.5,
                                callback: (val) => Number(val).toFixed(2).replace('.', ',')
                            },
                            gridLines: { color: '#f1f5f9', display: true }
                        }],
                        yAxes: [{
                            ticks: { fontSize: 7.5, fontColor: '#475569' },
                            gridLines: { display: false }
                        }]
                    },
                    plugins: {
                        datalabels: {
                            display: true,
                            anchor: 'end',
                            align: 'right',
                            color: '#1e293b',
                            font: { size: 7.5, weight: 'bold' },
                            formatter: (val) => Number(val).toFixed(2).replace('.', ',')
                        }
                    },
                    annotation: {
                        annotations: [{
                            type: 'line',
                            mode: 'vertical',
                            scaleID: 'x-axis-0',
                            value: 1.0,
                            borderColor: '#dc2626',
                            borderWidth: 1.5
                        }]
                    }
                }
            }"
        ];

        // -------------------------------------------------------------
        // Chart 3: Kategori PEKA (Pie Chart with direct % values via JS)
        // -------------------------------------------------------------
        $kat = $charts['kategori'] ?? [];
        $katRawValues = [
            (int) ($kat['Safe Action'] ?? 0),
            (int) ($kat['Safe Condition'] ?? 0),
            (int) ($kat['Unsafe Action'] ?? 0),
            (int) ($kat['Unsafe Condition'] ?? 0)
        ];
        $katSum = array_sum($katRawValues);
        $katPctValues = array_map(fn($v) => $katSum > 0 ? round(($v / $katSum) * 100) : 0, $katRawValues);
        $katDataJson = json_encode($katPctValues);

        $configs['chart3'] = [
            'w' => 380, 'h' => 200,
            'cfg' => "{
                type: 'pie',
                data: {
                    labels: ['Safe Action', 'Safe Condition', 'Unsafe Action', 'Unsafe Condition'],
                    datasets: [{
                        data: {$katDataJson},
                        backgroundColor: ['#2B579A', '#ED7D31', '#A6A6A6', '#FFC000'],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    legend: {
                        position: 'right',
                        labels: { fontSize: 7.5, boxWidth: 8, padding: 4 }
                    },
                    plugins: {
                        datalabels: {
                            color: ['#ffffff', '#ffffff', '#ffffff', '#1e293b'],
                            font: { size: 9, weight: 'bold' },
                            formatter: (val) => val > 0 ? val + '%' : ''
                        }
                    }
                }
            }"
        ];

        // -------------------------------------------------------------
        // Chart 4: Rekap % Temuan Fungsi (Stacked 100% Bar with % labels)
        // -------------------------------------------------------------
        $pt = $charts['persentase_fungsi'] ?? [];
        $ptLabels = ['Operation', 'Maintenance', 'HSSE', 'Bussiness Support'];
        $ptClosed = [];
        $ptOpen = [];
        foreach ($ptLabels as $f) {
            $fKey = ($f === 'Bussiness Support') ? 'Business Support' : $f;
            $c = round((float) ($pt[$fKey]['closed'] ?? ($pt[$f]['closed'] ?? 0)));
            $ptClosed[] = $c;
            $ptOpen[] = max(0, 100 - $c);
        }
        $ptClosedJson = json_encode($ptClosed);
        $ptOpenJson = json_encode($ptOpen);

        $configs['chart4'] = [
            'w' => 380, 'h' => 200,
            'cfg' => "{
                type: 'bar',
                data: {
                    labels: ['Operation', 'Maintenance', 'HSSE', 'Bussiness Support'],
                    datasets: [
                        {
                            label: 'Closed',
                            data: {$ptClosedJson},
                            backgroundColor: '#548235',
                            datalabels: {
                                display: true,
                                color: '#ffffff',
                                anchor: 'center',
                                align: 'center',
                                font: { size: 8, weight: 'bold' },
                                formatter: (val) => val + '%'
                            }
                        },
                        {
                            label: 'TOTAL',
                            data: {$ptOpenJson},
                            backgroundColor: '#FFC000',
                            datalabels: { display: false }
                        }
                    ]
                },
                options: {
                    legend: {
                        position: 'bottom',
                        labels: { fontSize: 7.5, boxWidth: 8, padding: 4 }
                    },
                    scales: {
                        xAxes: [{ stacked: true, gridLines: { display: false }, ticks: { fontSize: 7.5 } }],
                        yAxes: [{
                            stacked: true,
                            ticks: {
                                max: 100,
                                beginAtZero: true,
                                fontSize: 7.5,
                                callback: (val) => val + '%'
                            }
                        }]
                    }
                }
            }"
        ];

        // -------------------------------------------------------------
        // Chart 5: Rekap % Penindak Lanjut (Donut Chart with outlabels)
        // -------------------------------------------------------------
        $tl = $charts['tindak_lanjut'] ?? [];
        $tlTotal = array_sum($tl);
        if ($tlTotal === 0) {
            // Tidak ada data SAP — tampilkan donut kosong dengan label placeholder
            $configs['chart5'] = [
                'w' => 260, 'h' => 200,
                'cfg' => [
                    'type' => 'doughnut',
                    'data' => [
                        'labels' => ['Tidak ada data SAP'],
                        'datasets' => [[
                            'data' => [1],
                            'backgroundColor' => ['#e2e8f0'],
                            'borderColor' => '#ffffff',
                            'borderWidth' => 2
                        ]]
                    ],
                    'options' => [
                        'cutoutPercentage' => 60,
                        'legend' => [
                            'display' => true,
                            'position' => 'bottom',
                            'labels' => ['fontSize' => 7, 'boxWidth' => 8, 'padding' => 3]
                        ],
                        'plugins' => ['datalabels' => ['display' => false]]
                    ]
                ]
            ];
        } else {
            $colorMap = [
                'Maintenance'       => '#ED7D31',
                'HSSE'              => '#A6A6A6',
                'Business Support'  => '#FFC000',
                'Bussiness Support' => '#FFC000',
                'Operation'         => '#4472C4',
            ];
            $tlLabels = [];
            $tlValues = [];
            $tlColors = [];
            foreach ($tl as $fname => $cnt) {
                if ($cnt > 0) {
                    $tlLabels[] = ($fname === 'Business Support') ? 'Bussiness Support' : $fname;
                    $tlValues[] = $cnt;
                    $tlColors[] = $colorMap[$fname] ?? '#94a3b8';
                }
            }

            $tlLabelsJson = json_encode($tlLabels);
            $tlValuesJson = json_encode($tlValues);
            $tlColorsJson = json_encode($tlColors);

            $configs['chart5'] = [
                'w' => 260, 'h' => 200,
                'cfg' => "{
                    type: 'outlabeledDoughnut',
                    data: {
                        labels: {$tlLabelsJson},
                        datasets: [{
                            data: {$tlValuesJson},
                            backgroundColor: {$tlColorsJson},
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        cutoutPercentage: 55,
                        plugins: {
                            legend: false,
                            outlabels: {
                                text: '%l %p',
                                color: '#1e293b',
                                stretch: 15,
                                font: { size: 8, weight: 'bold' },
                                lineColor: '#94a3b8',
                                lineWidth: 1,
                                padding: 2,
                                backgroundColor: '#ffffff',
                                borderColor: '#cbd5e1',
                                borderWidth: 1,
                                borderRadius: 3
                            }
                        }
                    }
                }"
            ];
        }

        // -------------------------------------------------------------
        // Chart 6: Keterlibatan Dalam Observasi (Stacked 100% Bar with % labels)
        // -------------------------------------------------------------
        $ket = $charts['keterlibatan'] ?? [];
        $ketLabels = ['Operation', 'Maintenance', 'HSSE', 'Bussiness Support'];
        $ketData = [];
        $remData = [];
        $ketSum = 0;
        $ketCount = 0;
        foreach ($ketLabels as $f) {
            $fKey = ($f === 'Bussiness Support') ? 'Business Support' : $f;
            $val = $ket[$fKey] ?? ($ket[$f] ?? 0);
            $c = $val !== null ? round(min(100, max(0, $val))) : 0;
            $ketData[] = $c;
            $remData[] = 100 - $c;
            if ($val !== null) {
                $ketSum += $val;
                $ketCount++;
            }
        }
        $avgKetVal = $ketCount > 0 ? round($ketSum / $ketCount) : $avgKet;
        $ketDataJson = json_encode($ketData);
        $remDataJson = json_encode($remData);

        $configs['chart6'] = [
            'w' => 380, 'h' => 200,
            'cfg' => "{
                type: 'bar',
                data: {
                    labels: ['Operation', 'Maintenance', 'HSSE', 'Bussiness Support'],
                    datasets: [
                        {
                            label: 'Keterlibatan',
                            data: {$ketDataJson},
                            backgroundColor: '#ED7D31',
                            datalabels: {
                                display: true,
                                color: '#ffffff',
                                anchor: 'center',
                                align: 'center',
                                font: { size: 8, weight: 'bold' },
                                formatter: (val) => val + '%'
                            }
                        },
                        {
                            label: 'Jumlah',
                            data: {$remDataJson},
                            backgroundColor: '#4472C4',
                            datalabels: { display: false }
                        }
                    ]
                },
                options: {
                    legend: {
                        position: 'bottom',
                        labels: { fontSize: 7.5, boxWidth: 8, padding: 4 }
                    },
                    scales: {
                        xAxes: [{ stacked: true, gridLines: { display: false }, ticks: { fontSize: 7.5 } }],
                        yAxes: [{
                            stacked: true,
                            ticks: {
                                max: 100,
                                beginAtZero: true,
                                fontSize: 7.5,
                                callback: (val) => val + '%'
                            }
                        }]
                    },
                    annotation: {
                        annotations: [{
                            type: 'line',
                            mode: 'horizontal',
                            scaleID: 'y-axis-0',
                            value: {$avgKetVal},
                            borderColor: '#dc2626',
                            borderWidth: 1.5
                        }]
                    }
                }
            }"
        ];

        // -------------------------------------------------------------
        // Chart 7: Unsafe Action Category (Horizontal Bar — 6 Kategori)
        // Urutan mengikuti referensi mentor — JANGAN sort by value
        // -------------------------------------------------------------
        $uaRaw   = $charts['unsafe_action'] ?? [];
        $uaRawData  = $uaRaw['data'] ?? [];
        $uaTotal    = $uaRaw['total'] ?? array_sum($uaRawData);

        // Urutan tetap sesuai referensi mentor
        $uaFixedOrder = [
            'Tidak mengikuti Prosedur / Failure to follow procedure',
            'Tidak menggunakan APD yang standard / Using improper PPE',
            'Posisi kerja yang tidak tepat / Improper position for task',
            'Penempatan tidak sesuai / Improper Placement',
            'Mengoperasikan diluar standar / operating out of standard',
            'Menggunakan peralatan yang tidak standard/rusak / Using defective tools/equipments',
        ];

        // Susun nilai mengikuti urutan tetap, fallback 0 jika tidak ada
        $uaValues  = [];
        $uaRawLabels = [];
        foreach ($uaFixedOrder as $cat) {
            $found = 0;
            foreach ($uaRawData as $key => $val) {
                if (stripos($key, explode(' / ', $cat)[0]) !== false ||
                    stripos($cat, explode(' / ', $key)[0] ?? $key) !== false ||
                    strtolower(trim($key)) === strtolower(trim($cat))) {
                    $found = (int) $val;
                    break;
                }
            }
            $uaValues[]    = $found;
            $uaRawLabels[] = $cat;
        }
        $uaPct = array_map(fn($v) => $uaTotal > 0 ? round(($v / $uaTotal) * 100, 1) : 0, $uaValues);

        // Format labels menjadi 2 baris jika ada ' / '
        $uaLabels = array_map(function($lbl) {
            if (str_contains($lbl, ' / ')) {
                return explode(' / ', $lbl, 2);
            }
            return $lbl;
        }, $uaRawLabels);

        $configs['chart7'] = [
            'w' => 520, 'h' => 340,
            'cfg' => [
                'type' => 'horizontalBar',
                'data' => [
                    'labels' => $uaLabels,
                    'datasets' => [[
                        'label' => 'Series1',
                        'data' => $uaPct,
                        'backgroundColor' => '#ED7D31',
                        'borderWidth' => 0
                    ]]
                ],
                'options' => [
                    'layout' => ['padding' => ['left' => 15, 'right' => 20]],
                    'legend' => [
                        'position' => 'bottom',
                        'labels' => ['fontSize' => 8, 'boxWidth' => 10, 'padding' => 4]
                    ],
                    'scales' => [
                        'xAxes' => [[
                            'ticks' => ['beginAtZero' => true, 'max' => 80, 'fontSize' => 8],
                            'gridLines' => ['color' => '#f1f5f9']
                        ]],
                        'yAxes' => [[
                            'ticks' => ['fontSize' => 7.5],
                            'gridLines' => ['display' => false]
                        ]]
                    ],
                    'plugins' => ['datalabels' => ['display' => false]]
                ]
            ]
        ];

        // -------------------------------------------------------------
        // Chart 8: Unsafe Condition Category (Horizontal Bar — 12 Kategori)
        // Urutan mengikuti referensi mentor — JANGAN sort by value
        // -------------------------------------------------------------
        $ucRaw     = $charts['unsafe_condition'] ?? [];
        $ucRawData = $ucRaw['data'] ?? [];
        $ucTotal   = $ucRaw['total'] ?? array_sum($ucRawData);

        // Urutan tetap sesuai referensi mentor
        $ucFixedOrder = [
            'Rambu-rambu yang tidak cukup / Inadequate warning system',
            'Peralatan yang tidak sesuai / Incorrect tools/equipments',
            'Peralatan yang rusak / Defective tools/equipments',
            'Pengukuran yang tidak tepat / Improper measurement',
            'Pengaman yang tidak cukup / Inadequate Guards/Barriers',
            'Mode operasi yang tidak layak / Inadequate operation mode',
            'Material yang tidak tepat / Incorrect material',
            'Kondisi lantai/permukaan tidak layak / Inadequate condition of floor/surface',
            'Keterbatasan ruangan untuk kerja / Restricted space of action',
            'Integritas peralatan yang tidak layak / Inadequate integrity of equipment',
            'Housekeeping yang tidak baik / Poor house keeping order',
            'APD yang tidak cukup / Inadequate PPE',
        ];

        // Susun nilai mengikuti urutan tetap, fallback 0 jika tidak ada
        $ucValues    = [];
        $ucRawLabels = [];
        foreach ($ucFixedOrder as $cat) {
            $found = 0;
            $catShort = explode(' / ', $cat)[0] ?? $cat;
            foreach ($ucRawData as $key => $val) {
                $keyShort = explode(' / ', $key)[0] ?? $key;
                if (strtolower(trim($key)) === strtolower(trim($cat)) ||
                    stripos($key, $catShort) !== false ||
                    stripos($cat, $keyShort) !== false) {
                    $found = (int) $val;
                    break;
                }
            }
            $ucValues[]    = $found;
            $ucRawLabels[] = $cat;
        }
        $ucPct = array_map(fn($v) => $ucTotal > 0 ? round(($v / $ucTotal) * 100, 1) : 0, $ucValues);

        // Format labels menjadi 2 baris jika ada ' / '
        $ucLabels = array_map(function($lbl) {
            if (str_contains($lbl, ' / ')) {
                return explode(' / ', $lbl, 2);
            } elseif (str_contains($lbl, '/')) {
                return explode('/', $lbl, 2);
            }
            return $lbl;
        }, $ucRawLabels);

        $configs['chart8'] = [
            'w' => 520, 'h' => 340,
            'cfg' => [
                'type' => 'horizontalBar',
                'data' => [
                    'labels' => $ucLabels,
                    'datasets' => [[
                        'label' => 'Series1',
                        'data' => $ucPct,
                        'backgroundColor' => '#ED7D31',
                        'borderWidth' => 0
                    ]]
                ],
                'options' => [
                    'layout' => ['padding' => ['left' => 15, 'right' => 20]],
                    'legend' => [
                        'position' => 'bottom',
                        'labels' => ['fontSize' => 8, 'boxWidth' => 10, 'padding' => 4]
                    ],
                    'scales' => [
                        'xAxes' => [[
                            'ticks' => ['beginAtZero' => true, 'max' => 40, 'fontSize' => 8],
                            'gridLines' => ['color' => '#f1f5f9']
                        ]],
                        'yAxes' => [[
                            'ticks' => ['fontSize' => 6.8],
                            'gridLines' => ['display' => false]
                        ]]
                    ],
                    'plugins' => ['datalabels' => ['display' => false]]
                ]
            ]
        ];

        // Fetch all chart images in parallel via curl_multi
        return $this->fetchParallelCharts($configs);
    }

    /**
     * Parallel fetch via curl_multi with base64 conversion via POST.
     */
    private function fetchParallelCharts(array $configs): array
    {
        $mh = curl_multi_init();
        $handles = [];

        foreach ($configs as $key => $item) {
            $w = $item['w'];
            $h = $item['h'];
            $cfg = $item['cfg'];

            $postData = [
                'chart'            => $cfg,
                'width'            => $w,
                'height'           => $h,
                'devicePixelRatio' => 2,
                'backgroundColor'  => 'transparent',
            ];

            $ch = curl_init('https://quickchart.io/chart');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_multi_add_handle($mh, $ch);
            $handles[$key] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while ($running > 0);

        $images = [];
        foreach ($handles as $key => $ch) {
            $raw = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode === 200 && !empty($raw)) {
                $images[$key] = 'data:image/png;base64,' . base64_encode($raw);
            } else {
                Log::warning("QuickChart failed for {$key}, HTTP code: {$httpCode}");
                $images[$key] = null;
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);

        return $images;
    }
}
