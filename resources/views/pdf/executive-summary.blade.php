<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PEKA Report {{ $periodeLabel }}</title>
    {{--
        LAYOUT PDF — mengikuti referensi mentor (slide 16:9, 823 x 463 pt).
        Seluruh elemen diposisikan absolut dengan satuan pt (1 pt = 1 px pada gambar referensi),
        sehingga posisi/ukuran dapat dibandingkan langsung dengan referensi.
        Ukuran kertas diset di ExecutiveSummaryPdfService::generate() -> [0, 0, 823, 463].
    --}}
    <style>
        @page {
            margin: 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #262626;
            background: #ffffff;
        }

        /* PAGE CONTAINER (slide) */
        .page {
            position: relative;
            width: 823pt;
            height: 462pt;
            overflow: hidden;
        }
        .page-break {
            page-break-after: always;
        }
        .abs {
            position: absolute;
        }

        /* HEADER */
        .logo-left {
            position: absolute;
            left: 8pt;
            top: 5pt;
            width: 72pt;
            height: 20.3pt;
        }
        .logo-right {
            position: absolute;
            left: 690pt;
            top: 0;
            width: 133pt;
            height: 37.5pt;
        }
        .title-block {
            position: absolute;
            left: 250pt;
            top: 6pt;
            width: 400pt;
            text-align: center;
            white-space: nowrap;
        }
        .main-title {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 20.5pt;
            font-weight: bold;
            color: #000000;
        }
        .sub-title {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 13.5pt;
            font-weight: normal;
            color: #000000;
            margin-left: 3pt;
        }

        /* NOMOR BAGIAN: lingkaran gelap, angka putih */
        .badge {
            position: absolute;
            width: 15pt;
            height: 15pt;
            line-height: 15pt;
            background-color: #000000;
            color: #ffffff;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9pt;
            font-weight: bold;
            border-radius: 50%;
            text-align: center;
        }
        .badge-lg {
            width: 16pt;
            height: 16pt;
            line-height: 16pt;
            font-size: 9.5pt;
        }

        /* JUDUL GRAFIK */
        .sec-title {
            position: absolute;
            text-align: center;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            font-weight: normal;
            color: #595959;
            line-height: 11.5pt;
        }

        /* GAMBAR GRAFIK */
        .chart-img {
            position: absolute;
        }

        /* KOTAK KESIMPULAN (abu-abu) */
        .callout {
            position: absolute;
            background: #ebebeb;
            border-radius: 2pt;
            padding: 4.5pt 6pt 4.5pt 9pt;
            font-size: 8.5pt;
            line-height: 11.5pt;
            color: #1a1a1a;
        }
        .callout ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }
        .callout li {
            position: relative;
            padding-left: 11pt;
        }
        .callout li:before {
            content: "\2022";
            position: absolute;
            left: 0;
            top: 0;
            color: #000000;
        }
        .callout-narrow {
            padding: 9pt 5pt 6pt 11pt;
            line-height: 12pt;
            font-size: 8.5pt;
        }
        .callout-narrow li {
            padding-left: 14pt;
        }

        /* CLOSING RATE (di dalam grafik 1) */
        .closing-box {
            position: absolute;
            left: 206pt;
            top: 68pt;
            width: 70pt;
            height: 30pt;
            background: #ffffff;
            border: 0.7pt solid #bfbfbf;
            text-align: center;
            font-size: 8.5pt;
            font-weight: bold;
            line-height: 11.5pt;
            padding-top: 3pt;
            color: #000000;
        }

        /* LEGENDA CUSTOM GRAFIK 2 */
        .legend-line {
            position: absolute;
            text-align: center;
            font-size: 6.2pt;
            color: #595959;
            white-space: nowrap;
        }
        .legend-sq {
            display: inline-block;
            width: 5pt;
            height: 5pt;
            vertical-align: middle;
            margin-right: 2pt;
        }

        /* FOOTER */
        .footer-band {
            position: absolute;
            left: 0;
            top: 428pt;
            width: 823pt;
            height: 27pt;
            background: #f0f0f0;
        }
        .footer-ribbon {
            position: absolute;
            left: 0;
            top: 428pt;
            width: 135pt;
            height: 27pt;
        }
        .footer-center {
            position: absolute;
            left: 394pt;
            top: 436pt;
            width: 70pt;
            text-align: center;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            font-weight: bold;
            color: #000000;
        }
        .footer-135 {
            position: absolute;
            left: 773pt;
            top: 432pt;
            width: 32pt;
            height: 20pt;
        }

        /* PAGE 2 */
        .pill {
            position: absolute;
            height: 28pt;
            line-height: 22pt;
            background-color: #0b2a5b;
            color: #ffffff;
            font-family: Helvetica, Arial, sans-serif;
            font-size: 12.5pt;
            font-weight: bold;
            font-style: italic;
            padding-left: 6pt;
            padding-right: 6pt;
            white-space: nowrap;
        }
        .chart-frame {
            position: absolute;
            border: 0.8pt solid #d9d9d9;
            background: #ffffff;
        }
    </style>
</head>
<body>
@php
    $danantaraPath       = public_path('assets/images/logo/danantara.png');
    $pertaminaFramedPath = public_path('assets/images/logo/pertamina-framed.png');
    $pertaminaPath       = public_path('assets/images/logo/pertamina.png');
    $footerRibbonPath    = public_path('assets/images/logo/footer-pertamina-ribbon.png');
    $call135Path         = public_path('assets/images/logo/pertamina-call-135.png');
@endphp

    {{-- ================================================================ --}}
    {{-- PAGE 1 — REKAP PEKA, REPORTING RATE, KATEGORI, TEMUAN, TINDAK LANJUT, KETERLIBATAN --}}
    {{-- ================================================================ --}}
    <div class="page page-break">

        {{-- HEADER --}}
        @if(file_exists($danantaraPath))
            <img src="{{ $danantaraPath }}" class="logo-left" alt="Danantara Indonesia">
        @else
            <div class="abs" style="left:8pt;top:8pt;font-size:12pt;font-weight:bold;">Danantara Indonesia</div>
        @endif
        <div class="title-block">
            <span class="main-title">PEKA Report</span><span class="sub-title">{{ $periodeLabel }}</span>
        </div>
        @if(file_exists($pertaminaFramedPath))
            <img src="{{ $pertaminaFramedPath }}" class="logo-right" alt="PERTAMINA">
        @elseif(file_exists($pertaminaPath))
            <img src="{{ $pertaminaPath }}" style="position:absolute;left:700pt;top:8pt;height:24pt;" alt="PERTAMINA">
        @else
            <div class="abs" style="left:720pt;top:10pt;font-size:11pt;font-weight:bold;color:#004d25;">PERTAMINA</div>
        @endif

        {{-- ============ SECTION 1: REKAP PEKA ============ --}}
        <div class="badge" style="left:12.5pt;top:40.5pt;">1</div>
        <div class="sec-title" style="left:19pt;top:41pt;width:240pt;font-size:8.8pt;">Rekap PEKA Tahun {{ $tahun }}</div>
        @if(!empty($chartImages['chart1']))
            <img src="{{ $chartImages['chart1'] }}" class="chart-img" style="left:18pt;top:58pt;width:262pt;height:130pt;" alt="Chart 1">
        @else
            <div class="abs" style="left:18pt;top:110pt;width:262pt;text-align:center;color:#94a3b8;font-style:italic;">Data Rekap PEKA</div>
        @endif
        <div class="closing-box">
            Closing Rate :<br>{{ number_format($closingRate, 2, ',', '.') }}%
        </div>
        <div class="callout" style="left:28pt;top:198pt;width:252pt;min-height:31pt;">
            <ul><li>{!! $insights['s1'] !!}</li></ul>
        </div>

        {{-- ============ SECTION 2: REPORTING RATE PER FUNGSI ============ --}}
        <div class="badge" style="left:312.5pt;top:49.5pt;">2</div>
        <div class="sec-title" style="left:335pt;top:44pt;width:200pt;font-size:10.5pt;">Reporting Rate per Fungsi</div>
        @if(!empty($chartImages['chart2']))
            <img src="{{ $chartImages['chart2'] }}" class="chart-img" style="left:305pt;top:66pt;width:245pt;height:112pt;" alt="Chart 2">
        @else
            <div class="abs" style="left:305pt;top:110pt;width:245pt;text-align:center;color:#94a3b8;font-style:italic;">Data Reporting Rate</div>
        @endif
        <div class="legend-line" style="left:305pt;top:180pt;width:245pt;">
            <span class="legend-sq" style="background:#FFC000;"></span>Bussiness Support &nbsp;
            <span class="legend-sq" style="background:#A6A6A6;"></span>HSSE &nbsp;
            <span class="legend-sq" style="background:#ED7D31;"></span>Maintenance &nbsp;
            <span class="legend-sq" style="background:#2B579A;"></span>Operation &nbsp;
            <span class="legend-sq" style="background:#001F5B;"></span>AREA LHD
        </div>
        <div class="callout" style="left:318pt;top:200pt;width:227pt;min-height:44pt;padding-top:5.5pt;">
            <ul>
                <li>{!! $insights['s2_1'] !!}</li>
                <li>{!! $insights['s2_2'] !!}</li>
                <li>{!! $insights['s2_3'] !!}</li>
            </ul>
        </div>

        {{-- ============ SECTION 3: KATEGORI PEKA ============ --}}
        <div class="badge" style="left:586.5pt;top:46.5pt;">3</div>
        <div class="sec-title" style="left:610pt;top:44pt;width:115pt;font-size:9.8pt;">Kategori PEKA</div>
        @if(!empty($chartImages['chart3']))
            <img src="{{ $chartImages['chart3'] }}" class="chart-img" style="left:588pt;top:62pt;width:232pt;height:96pt;" alt="Chart 3">
        @else
            <div class="abs" style="left:588pt;top:100pt;width:232pt;text-align:center;color:#94a3b8;font-style:italic;">Data Kategori PEKA</div>
        @endif
        <div class="callout" style="left:583pt;top:163pt;width:197pt;min-height:30pt;">
            <ul>
                <li>{!! $insights['s3_1'] !!}</li>
                <li>{!! $insights['s3_2'] !!}</li>
            </ul>
        </div>

        {{-- ============ SECTION 4: REKAP % TEMUAN FUNGSI ============ --}}
        <div class="badge" style="left:12.5pt;top:235.5pt;">4</div>
        <div class="sec-title" style="left:39pt;top:245pt;width:200pt;">Rekap % Temuan Fungsi</div>
        @if(!empty($chartImages['chart4']))
            <img src="{{ $chartImages['chart4'] }}" class="chart-img" style="left:15pt;top:261pt;width:228pt;height:124pt;" alt="Chart 4">
        @else
            <div class="abs" style="left:15pt;top:310pt;width:228pt;text-align:center;color:#94a3b8;font-style:italic;">Data Temuan Fungsi</div>
        @endif
        <div class="callout" style="left:17pt;top:389pt;width:221pt;min-height:30pt;">
            <ul><li>{!! $insights['s4'] !!}</li></ul>
        </div>

        {{-- ============ SECTION 5: REKAP % PENINDAK LANJUT ============ --}}
        <div class="badge" style="left:257pt;top:258.5pt;">5</div>
        <div class="sec-title" style="left:282pt;top:256pt;width:126pt;font-size:10pt;">Rekap % Penindak Lanjut<br>Temuan</div>
        @if(!empty($chartImages['chart5']))
            <img src="{{ $chartImages['chart5'] }}" class="chart-img" style="left:262pt;top:284pt;width:178pt;height:142pt;" alt="Chart 5">
        @else
            <div class="abs" style="left:276pt;top:340pt;width:160pt;text-align:center;color:#94a3b8;font-style:italic;">Donut Chart</div>
        @endif
        <div class="callout callout-narrow" style="left:428pt;top:278pt;width:100pt;min-height:122pt;">
            <ul><li>{!! $insights['s5'] !!}</li></ul>
        </div>

        {{-- ============ SECTION 6: KETERLIBATAN DALAM OBSERVASI ============ --}}
        <div class="badge" style="left:586.5pt;top:207.5pt;">6</div>
        <div class="sec-title" style="left:610pt;top:217pt;width:200pt;font-size:10pt;">Keterlibatan Dalam Observasi</div>
        @if(!empty($chartImages['chart6']))
            <img src="{{ $chartImages['chart6'] }}" class="chart-img" style="left:583pt;top:232pt;width:238pt;height:126pt;" alt="Chart 6">
        @else
            <div class="abs" style="left:583pt;top:290pt;width:238pt;text-align:center;color:#94a3b8;font-style:italic;">Data Keterlibatan</div>
        @endif
        <div class="callout" style="left:580pt;top:357pt;width:207pt;min-height:56pt;font-size:8pt;line-height:10.5pt;">
            <ul>
                <li>{!! $insights['s6_1'] !!}</li>
                <li>{!! $insights['s6_2'] !!}</li>
            </ul>
        </div>

        {{-- FOOTER (PAGE 1) --}}
        <div class="footer-band"></div>
        @if(file_exists($footerRibbonPath))
            <img src="{{ $footerRibbonPath }}" class="footer-ribbon" alt="www.pertamina.com">
        @else
            <div class="abs" style="left:0;top:428pt;width:135pt;height:27pt;background:#E30613;color:#fff;font-size:8pt;font-weight:bold;line-height:27pt;padding-left:12pt;">www.pertamina.com</div>
        @endif
        <div class="footer-center">HSSE</div>
        @if(file_exists($call135Path))
            <img src="{{ $call135Path }}" class="footer-135" alt="PERTAMINA CALL 135">
        @endif
    </div>

    {{-- ================================================================ --}}
    {{-- PAGE 2 — SECTION 7: UNSAFE ACTION & SECTION 8: UNSAFE CONDITION  --}}
    {{-- ================================================================ --}}
    <div class="page">

        {{-- HEADER (PAGE 2) --}}
        @if(file_exists($danantaraPath))
            <img src="{{ $danantaraPath }}" class="logo-left" alt="Danantara Indonesia">
        @else
            <div class="abs" style="left:8pt;top:8pt;font-size:12pt;font-weight:bold;">Danantara Indonesia</div>
        @endif
        <div class="title-block">
            <span class="main-title">PEKA Report</span><span class="sub-title">{{ $periodeLabel }}</span>
        </div>
        @if(file_exists($pertaminaFramedPath))
            <img src="{{ $pertaminaFramedPath }}" class="logo-right" alt="PERTAMINA">
        @elseif(file_exists($pertaminaPath))
            <img src="{{ $pertaminaPath }}" style="position:absolute;left:700pt;top:8pt;height:24pt;" alt="PERTAMINA">
        @else
            <div class="abs" style="left:720pt;top:10pt;font-size:11pt;font-weight:bold;color:#004d25;">PERTAMINA</div>
        @endif

        {{-- ============ SECTION 7: UNSAFE ACTION CATEGORY ============ --}}
        <div class="badge badge-lg" style="left:12pt;top:60pt;">7</div>
        <div class="pill" style="left:36pt;top:54pt;width:150pt;">Unsafe Action Category</div>
        <div class="chart-frame" style="left:40pt;top:92pt;width:335pt;height:326pt;"></div>
        @if(!empty($chartImages['chart7']))
            <img src="{{ $chartImages['chart7'] }}" class="chart-img" style="left:41pt;top:93pt;width:333pt;height:324pt;" alt="Chart 7">
        @else
            <div class="abs" style="left:40pt;top:240pt;width:335pt;text-align:center;color:#94a3b8;font-style:italic;">Data Unsafe Action</div>
        @endif

        {{-- ============ SECTION 8: UNSAFE CONDITION CATEGORY ============ --}}
        <div class="badge badge-lg" style="left:432pt;top:60pt;">8</div>
        <div class="pill" style="left:456pt;top:54pt;width:164pt;">Unsafe Condition Category</div>
        <div class="chart-frame" style="left:455pt;top:92pt;width:345pt;height:326pt;"></div>
        @if(!empty($chartImages['chart8']))
            <img src="{{ $chartImages['chart8'] }}" class="chart-img" style="left:456pt;top:93pt;width:343pt;height:324pt;" alt="Chart 8">
        @else
            <div class="abs" style="left:455pt;top:240pt;width:345pt;text-align:center;color:#94a3b8;font-style:italic;">Data Unsafe Condition</div>
        @endif

        {{-- FOOTER (PAGE 2) --}}
        <div class="footer-band"></div>
        @if(file_exists($footerRibbonPath))
            <img src="{{ $footerRibbonPath }}" class="footer-ribbon" alt="www.pertamina.com">
        @else
            <div class="abs" style="left:0;top:428pt;width:135pt;height:27pt;background:#E30613;color:#fff;font-size:8pt;font-weight:bold;line-height:27pt;padding-left:12pt;">www.pertamina.com</div>
        @endif
        <div class="footer-center">HSSE</div>
        @if(file_exists($call135Path))
            <img src="{{ $call135Path }}" class="footer-135" alt="PERTAMINA CALL 135">
        @endif
    </div>

</body>
</html>
