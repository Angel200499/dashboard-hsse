<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PEKA Report {{ $periodeLabel }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 10mm 6mm 10mm;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 8px;
            color: #1e293b;
            background: #ffffff;
            -webkit-print-color-adjust: exact;
        }

        /* PAGE CONTAINER */
        .page-container {
            width: 100%;
            height: 194mm;
            position: relative;
        }
        .page-break {
            page-break-after: always;
        }

        /* HEADER */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
            padding-bottom: 2px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-logo-left {
            width: 25%;
            text-align: left;
        }
        .header-title-center {
            width: 50%;
            text-align: center;
        }
        .header-logo-right {
            width: 25%;
            text-align: right;
        }
        .main-title {
            font-size: 20px;
            font-weight: 800;
            color: #000000;
            letter-spacing: -0.3px;
            display: inline;
        }
        .sub-title {
            font-size: 14px;
            font-weight: 400;
            color: #1f2937;
            margin-left: 6px;
            display: inline;
        }

        .header-pertamina-frame {
            display: inline-block;
            border-left: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            border-bottom-left-radius: 20px;
            padding: 2px 14px 4px 16px;
        }

        /* FOOTER */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            position: absolute;
            bottom: 0px;
            left: 0;
            height: 22px;
            background-color: #ededed;
        }
        .footer-table td {
            vertical-align: middle;
            padding: 0;
            margin: 0;
        }
        .footer-left-wrap {
            width: 35%;
            text-align: left;
            padding: 0;
            line-height: 0;
        }
        .footer-center {
            width: 30%;
            text-align: center;
            font-size: 11px;
            font-weight: 800;
            color: #000000;
            letter-spacing: 0.5px;
        }
        .footer-right {
            width: 35%;
            text-align: right;
            padding-right: 6px;
            line-height: 0;
        }

        /* GRID SYSTEM (PAGE 1) */
        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 5px;
        }
        .grid-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 0;
        }

        /* SECTION CARD (PAGE 1) — CLEAN WITHOUT CARD BORDER */
        .sec-card {
            background: transparent;
            border: none;
            padding: 0;
            height: 77mm;
            position: relative;
        }
        .sec-header {
            margin-bottom: 2px;
            height: 16px;
            line-height: 16px;
        }
        .sec-num-badge {
            display: inline-block;
            width: 14px;
            height: 14px;
            line-height: 14px;
            background-color: #000000;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            border-radius: 50%;
            text-align: center;
            vertical-align: middle;
            margin-right: 4px;
        }
        .sec-title {
            font-size: 9.5px;
            font-weight: 600;
            color: #4b5563;
            vertical-align: middle;
            display: inline-block;
        }
        .chart-img-wrap {
            width: 100%;
            height: 48mm;
            text-align: center;
            overflow: hidden;
        }
        .chart-img {
            max-width: 100%;
            max-height: 48mm;
            object-fit: contain;
        }

        /* INSIGHT BOX */
        .insight-box {
            background: #efefef;
            border-radius: 4px;
            padding: 4px 8px;
            margin-top: 3px;
            font-size: 7.2px;
            line-height: 1.35;
            color: #262626;
            min-height: 16mm;
        }
        .insight-box ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }
        .insight-box li {
            position: relative;
            padding-left: 9px;
            margin-bottom: 2px;
        }
        .insight-box li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #000000;
            font-weight: bold;
        }

        /* CLOSING RATE BADGE ON CHART 1 */
        .closing-badge-box {
            position: absolute;
            top: 14px;
            right: 6px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            padding: 2px 7px;
            text-align: center;
            z-index: 10;
        }

        /* DONUT SPLIT LAYOUT (SECTION 5) */
        .split-table {
            width: 100%;
            border-collapse: collapse;
        }
        .split-table td {
            vertical-align: middle;
            padding: 0;
        }

        /* PAGE 2 LAYOUT */
        .page2-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-top: 4px;
        }
        .page2-table td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }
        .page2-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            height: 165mm;
        }
        .page2-pill-badge {
            background-color: #0a2540;
            color: #ffffff;
            border-radius: 4px;
            padding: 4px 10px;
            margin-bottom: 5px;
            display: inline-block;
        }
        .page2-pill-num {
            display: inline-block;
            width: 13px;
            height: 13px;
            line-height: 13px;
            background: #ffffff;
            color: #0a2540;
            font-size: 8px;
            font-weight: 800;
            border-radius: 50%;
            text-align: center;
            vertical-align: middle;
            margin-right: 5px;
        }
        .page2-pill-text {
            font-size: 10px;
            font-weight: bold;
            font-style: italic;
            vertical-align: middle;
        }
        .page2-chart-title {
            text-align: center;
            font-size: 9px;
            font-style: italic;
            color: #475569;
            margin-bottom: 3px;
        }
        .page2-chart-wrap {
            width: 100%;
            height: 140mm;
            text-align: center;
        }
        .page2-chart-img {
            max-width: 100%;
            max-height: 138mm;
            object-fit: contain;
        }
    </style>
</head>
<body>

    {{-- ================================================================ --}}
    {{-- PAGE 1 — REKAP PEKA, REPORTING RATE, KATEGORI, TEMUAN, TINDAK LANJUT, KETERLIBATAN --}}
    {{-- ================================================================ --}}
    <div class="page-container">

        {{-- HEADER --}}
        <table class="header-table">
            <tr>
                <td class="header-logo-left">
                    @php
                        $danantaraPath = public_path('assets/images/logo/danantara.png');
                    @endphp
                    @if(file_exists($danantaraPath))
                        <img src="{{ $danantaraPath }}" style="height:25px;vertical-align:middle;" alt="Danantara Indonesia">
                    @else
                        <span style="font-size:12px;font-weight:900;color:#000000;">Danantara Indonesia</span>
                    @endif
                </td>
                <td class="header-title-center">
                    <span class="main-title">PEKA Report</span>
                    <span class="sub-title">{{ $periodeLabel }}</span>
                </td>
                <td class="header-logo-right">
                    @php
                        $pertaminaPath = public_path('assets/images/logo/pertamina.png');
                    @endphp
                    <div class="header-pertamina-frame">
                        @if(file_exists($pertaminaPath))
                            <img src="{{ $pertaminaPath }}" style="height:20px;vertical-align:middle;" alt="PERTAMINA">
                        @else
                            <span style="font-size:11px;font-weight:900;color:#004d25;">PERTAMINA</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- GRID ROW 1 & 2 (3 columns x 2 rows) --}}
        <table class="grid-table">
            <tr>
                {{-- SECTION 1: REKAP PEKA TAHUN 2026 --}}
                <td>
                    <div class="sec-card">
                        <div class="sec-header">
                            <span class="sec-num-badge">1</span>
                            <span class="sec-title">Rekap PEKA Tahun {{ $tahun }}</span>
                        </div>
                        <div class="closing-badge-box">
                            <div style="font-size:7px;color:#374151;font-weight:600;">Closing Rate :</div>
                            <div style="font-size:9.5px;color:#111827;font-weight:800;">{{ number_format($closingRate, 2, ',', '.') }}%</div>
                        </div>
                        <div class="chart-img-wrap">
                            @if(!empty($chartImages['chart1']))
                                <img src="{{ $chartImages['chart1'] }}" class="chart-img" alt="Chart 1">
                            @else
                                <div style="padding-top:40px;color:#94a3b8;font-style:italic;">Data Rekap PEKA</div>
                            @endif
                        </div>
                        <div class="insight-box">
                            <ul>
                                <li>{!! $insights['s1'] !!}</li>
                            </ul>
                        </div>
                    </div>
                </td>

                {{-- SECTION 2: REPORTING RATE PER FUNGSI --}}
                <td>
                    <div class="sec-card">
                        <div class="sec-header">
                            <span class="sec-num-badge">2</span>
                            <span class="sec-title">Reporting Rate per Fungsi</span>
                        </div>
                        <div class="chart-img-wrap" style="height:45mm;">
                            @if(!empty($chartImages['chart2']))
                                <img src="{{ $chartImages['chart2'] }}" class="chart-img" style="max-height:45mm;" alt="Chart 2">
                            @else
                                <div style="padding-top:40px;color:#94a3b8;font-style:italic;">Data Reporting Rate</div>
                            @endif
                        </div>
                        <div style="text-align:center;font-size:6px;color:#555;margin-top:1px;margin-bottom:2px;">
                            <span style="display:inline-block;width:6px;height:6px;background:#FFC000;vertical-align:middle;margin-right:2px;"></span>Bussiness Support &nbsp;
                            <span style="display:inline-block;width:6px;height:6px;background:#A6A6A6;vertical-align:middle;margin-right:2px;"></span>HSSE &nbsp;
                            <span style="display:inline-block;width:6px;height:6px;background:#ED7D31;vertical-align:middle;margin-right:2px;"></span>Maintenance &nbsp;
                            <span style="display:inline-block;width:6px;height:6px;background:#2B579A;vertical-align:middle;margin-right:2px;"></span>Operation &nbsp;
                            <span style="display:inline-block;width:6px;height:6px;background:#001F5B;vertical-align:middle;margin-right:2px;"></span>AREA LHD
                        </div>
                        <div class="insight-box" style="min-height:15mm;">
                            <ul>
                                <li>{!! $insights['s2_1'] !!}</li>
                                <li>{!! $insights['s2_2'] !!}</li>
                                <li>{!! $insights['s2_3'] !!}</li>
                            </ul>
                        </div>
                    </div>
                </td>

                {{-- SECTION 3: KATEGORI PEKA --}}
                <td>
                    <div class="sec-card">
                        <div class="sec-header">
                            <span class="sec-num-badge">3</span>
                            <span class="sec-title">Kategori PEKA</span>
                        </div>
                        <div class="chart-img-wrap">
                            @if(!empty($chartImages['chart3']))
                                <img src="{{ $chartImages['chart3'] }}" class="chart-img" alt="Chart 3">
                            @else
                                <div style="padding-top:40px;color:#94a3b8;font-style:italic;">Data Kategori PEKA</div>
                            @endif
                        </div>
                        <div class="insight-box">
                            <ul>
                                <li>{!! $insights['s3_1'] !!}</li>
                                <li>{!! $insights['s3_2'] !!}</li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                {{-- SECTION 4: REKAP % TEMUAN FUNGSI --}}
                <td>
                    <div class="sec-card">
                        <div class="sec-header">
                            <span class="sec-num-badge">4</span>
                            <span class="sec-title">Rekap % Temuan Fungsi</span>
                        </div>
                        <div class="chart-img-wrap">
                            @if(!empty($chartImages['chart4']))
                                <img src="{{ $chartImages['chart4'] }}" class="chart-img" alt="Chart 4">
                            @else
                                <div style="padding-top:40px;color:#94a3b8;font-style:italic;">Data Temuan Fungsi</div>
                            @endif
                        </div>
                        <div class="insight-box">
                            <ul>
                                <li>{!! $insights['s4'] !!}</li>
                            </ul>
                        </div>
                    </div>
                </td>

                {{-- SECTION 5: REKAP % PENINDAK LANJUT --}}
                <td>
                    <div class="sec-card">
                        <div class="sec-header" style="height:22px;line-height:1.15;">
                            <span class="sec-num-badge" style="vertical-align:top;margin-top:2px;">5</span>
                            <div style="display:inline-block;vertical-align:middle;">
                                <span class="sec-title" style="display:block;">Rekap % Penindak Lanjut</span>
                                <span class="sec-title" style="display:block;">Temuan</span>
                            </div>
                        </div>
                        <table class="split-table" style="height:48mm;">
                            <tr>
                                <td style="width:50%;text-align:center;vertical-align:middle;">
                                    @if(!empty($chartImages['chart5']))
                                        <img src="{{ $chartImages['chart5'] }}" style="max-width:100%;max-height:48mm;" alt="Chart 5">
                                    @else
                                        <div style="color:#94a3b8;font-style:italic;">Donut Chart</div>
                                    @endif
                                </td>
                                <td style="width:50%;padding-left:4px;vertical-align:middle;">
                                    <div class="insight-box" style="min-height:38mm;margin-top:0;">
                                        <ul>
                                            <li>{!! $insights['s5'] !!}</li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>

                {{-- SECTION 6: KETERLIBATAN DALAM OBSERVASI --}}
                <td>
                    <div class="sec-card">
                        <div class="sec-header">
                            <span class="sec-num-badge">6</span>
                            <span class="sec-title">Keterlibatan Dalam Observasi</span>
                        </div>
                        <div class="chart-img-wrap">
                            @if(!empty($chartImages['chart6']))
                                <img src="{{ $chartImages['chart6'] }}" class="chart-img" alt="Chart 6">
                            @else
                                <div style="padding-top:40px;color:#94a3b8;font-style:italic;">Data Keterlibatan</div>
                            @endif
                        </div>
                        <div class="insight-box">
                            <ul>
                                <li>{!! $insights['s6_1'] !!}</li>
                                <li>{!! $insights['s6_2'] !!}</li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- FOOTER (PAGE 1) --}}
        @php
            $footerRibbonPath = public_path('assets/images/logo/footer-pertamina-ribbon.png');
            $call135Path = public_path('assets/images/logo/pertamina-call-135.png');
        @endphp
        <table class="footer-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="footer-left-wrap">
                    @if(file_exists($footerRibbonPath))
                        <img src="{{ $footerRibbonPath }}" style="height:22px;vertical-align:bottom;display:block;" alt="www.pertamina.com">
                    @else
                        <span style="background-color:#E30613;color:#fff;padding:3px 12px;font-size:8px;font-weight:bold;">www.pertamina.com</span>
                    @endif
                </td>
                <td class="footer-center">
                    HSSE
                </td>
                <td class="footer-right">
                    @if(file_exists($call135Path))
                        <img src="{{ $call135Path }}" style="height:20px;vertical-align:middle;" alt="PERTAMINA CALL 135">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- PAGE BREAK --}}
    <div class="page-break"></div>

    {{-- ================================================================ --}}
    {{-- PAGE 2 — SECTION 7: UNSAFE ACTION & SECTION 8: UNSAFE CONDITION  --}}
    {{-- ================================================================ --}}
    <div class="page-container">

        {{-- HEADER (PAGE 2) --}}
        <table class="header-table">
            <tr>
                <td class="header-logo-left">
                    @if(file_exists($danantaraPath))
                        <img src="{{ $danantaraPath }}" style="height:25px;vertical-align:middle;" alt="Danantara Indonesia">
                    @else
                        <span style="font-size:12px;font-weight:900;color:#000000;">Danantara Indonesia</span>
                    @endif
                </td>
                <td class="header-title-center">
                    <span class="main-title">PEKA Report</span>
                    <span class="sub-title">{{ $periodeLabel }}</span>
                </td>
                <td class="header-logo-right">
                    <div class="header-pertamina-frame">
                        @if(file_exists($pertaminaPath))
                            <img src="{{ $pertaminaPath }}" style="height:20px;vertical-align:middle;" alt="PERTAMINA">
                        @else
                            <span style="font-size:11px;font-weight:900;color:#004d25;">PERTAMINA</span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- 2 COLUMNS (SECTION 7 & SECTION 8) --}}
        <table class="page2-table">
            <tr>
                {{-- SECTION 7: UNSAFE ACTION CATEGORY --}}
                <td>
                    <div class="page2-card">
                        <div class="page2-pill-badge">
                            <span class="page2-pill-num">7</span>
                            <span class="page2-pill-text">Unsafe Action Category</span>
                        </div>
                        <div class="page2-chart-title">Unsafe Action Category</div>
                        <div class="page2-chart-wrap">
                            @if(!empty($chartImages['chart7']))
                                <img src="{{ $chartImages['chart7'] }}" class="page2-chart-img" alt="Chart 7">
                            @else
                                <div style="padding-top:100px;color:#94a3b8;font-style:italic;">Data Unsafe Action</div>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- SECTION 8: UNSAFE CONDITION CATEGORY --}}
                <td>
                    <div class="page2-card">
                        <div class="page2-pill-badge">
                            <span class="page2-pill-num">8</span>
                            <span class="page2-pill-text">Unsafe Condition Category</span>
                        </div>
                        <div class="page2-chart-title">Unsafe Condition Category</div>
                        <div class="page2-chart-wrap">
                            @if(!empty($chartImages['chart8']))
                                <img src="{{ $chartImages['chart8'] }}" class="page2-chart-img" alt="Chart 8">
                            @else
                                <div style="padding-top:100px;color:#94a3b8;font-style:italic;">Data Unsafe Condition</div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- FOOTER (PAGE 2) --}}
        <table class="footer-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="footer-left-wrap">
                    @if(file_exists($footerRibbonPath))
                        <img src="{{ $footerRibbonPath }}" style="height:22px;vertical-align:bottom;display:block;" alt="www.pertamina.com">
                    @else
                        <span style="background-color:#E30613;color:#fff;padding:3px 12px;font-size:8px;font-weight:bold;">www.pertamina.com</span>
                    @endif
                </td>
                <td class="footer-center">
                    HSSE
                </td>
                <td class="footer-right">
                    @if(file_exists($call135Path))
                        <img src="{{ $call135Path }}" style="height:20px;vertical-align:middle;" alt="PERTAMINA CALL 135">
                    @endif
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
