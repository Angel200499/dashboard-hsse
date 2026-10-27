<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Pelapor {{ $fungsi }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            background: #ffffff;
            padding: 18px 22px;
        }
        .green-bar {
            height: 4px;
            background: #9DBF2A;
            border-radius: 2px;
            margin-bottom: 12px;
        }
        /* HEADER */
        .header {
            border-bottom: 2px solid #9DBF2A;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-row { width: 100%; border-collapse: collapse; }
        .header-row td { vertical-align: top; padding: 0; }
        .header-title h1 {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }
        .header-title h2 {
            font-size: 10px;
            font-weight: 600;
            color: #475569;
            margin-top: 3px;
        }
        .header-meta {
            text-align: right;
            color: #64748b;
            font-size: 8px;
            line-height: 1.8;
        }
        .badge-periode {
            display: inline-block;
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
            font-size: 8px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
            margin-top: 6px;
        }
        /* KPI */
        .section-title {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }
        .kpi-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 12px;
        }
        .kpi-grid td { width: 33.33%; padding: 0; vertical-align: top; }
        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
        }
        .kpi-card.highlight { background: #fefce8; border-color: #fbbf24; }
        .kpi-label {
            font-size: 7px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 3px;
        }
        .kpi-value { font-size: 18px; font-weight: 700; color: #0f172a; line-height: 1; }
        .kpi-card.highlight .kpi-value { font-size: 11px; color: #92400e; }
        .kpi-sub { font-size: 7px; color: #94a3b8; margin-top: 3px; }
        /* TABLE */
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
        }
        .rekap-table thead tr { background: #1e293b; color: #ffffff; }
        .rekap-table thead th {
            padding: 5px 4px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
        }
        .rekap-table thead th.col-nama { text-align: left; }
        .rekap-table thead th.col-fungsi { text-align: left; }
        .rekap-table thead th.col-num { text-align: right; }
        .rekap-table thead th.col-nopek { text-align: center; }
        .rekap-table tbody tr { border-bottom: 1px solid #e2e8f0; }
        .rekap-table tbody tr.even { background: #f8fafc; }
        .rekap-table tbody td { padding: 4px 4px; color: #334155; }
        .rekap-table tbody td.col-no  { text-align: center; color: #94a3b8; font-weight: 600; width: 22px; }
        .rekap-table tbody td.col-fungsi { text-align: left; }
        .rekap-table tbody td.col-nama { text-align: left; font-weight: 600; }
        .rekap-table tbody td.col-nopek { text-align: center; font-family: monospace; font-size: 7px; color: #475569; }
        .rekap-table tbody td.col-num { text-align: right; }
        .rekap-table tbody td.zero { color: #cbd5e1; }
        .rekap-table tfoot tr { background: #1e293b; color: #ffffff; }
        .rekap-table tfoot td { padding: 5px 4px; font-weight: 700; }
        .rekap-table tfoot td.total-label { text-align: left; }
        .rekap-table tfoot td.total-val { text-align: right; }
        .badge-fungsi {
            background: #f3f9e0;
            color: #4a6a0a;
            font-size: 7px;
            font-weight: 700;
            padding: 1px 4px;
            border-radius: 3px;
        }
        /* EMPTY */
        .empty-state {
            text-align: center;
            padding: 24px;
            color: #94a3b8;
            border: 1px dashed #e2e8f0;
            border-radius: 6px;
            font-style: italic;
        }
        /* FOOTER */
        .doc-footer {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            width: 100%;
            border-collapse: collapse;
        }
        .doc-footer td { padding: 0; vertical-align: bottom; }
        .footer-left  { color: #64748b; font-size: 7px; line-height: 1.6; }
        .footer-right { color: #94a3b8; font-size: 7px; text-align: right; line-height: 1.6; }
    </style>
</head>
<body>

<div class="green-bar"></div>

{{-- HEADER --}}
<div class="header">
    <table class="header-row">
        <tr>
            <td class="header-title">
                <h1>Rekap Pelapor Temuan — Bulanan</h1>
                <h2>{{ $fungsi }} &mdash; PGE Area Lahendong</h2>
            </td>
            <td class="header-meta">
                <div>Dashboard Monitoring HSSE</div>
                <div>PGE Area Lahendong</div>
                <div>Dicetak: {{ $generatedAt }}</div>
            </td>
        </tr>
    </table>
    <div>
        <span class="badge-periode">Periode: {{ $rekapPelapor['periode_label'] }}</span>
        @if(!empty($rekapSearch))
            <span class="badge-periode" style="background:#fff1f2;border-color:#fca5a5;color:#991b1b;margin-left:6px;">
                Filter: &ldquo;{{ $rekapSearch }}&rdquo;
            </span>
        @endif
    </div>
</div>

{{-- KPI SUMMARY --}}
<div class="section-title">Ringkasan</div>
<table class="kpi-grid">
    <tr>
        <td>
            <div class="kpi-card">
                <div class="kpi-label">Total Pelaporan</div>
                <div class="kpi-value">{{ number_format($rekapPelapor['total_pelaporan']) }}</div>
                <div class="kpi-sub">Jumlah temuan unik</div>
            </div>
        </td>
        <td>
            <div class="kpi-card">
                <div class="kpi-label">Total Pelapor</div>
                <div class="kpi-value">{{ number_format($rekapPelapor['total_pelapor']) }}</div>
                <div class="kpi-sub">Pelapor unik</div>
            </div>
        </td>
        <td>
            <div class="kpi-card highlight">
                <div class="kpi-label">Pelapor Terbanyak</div>
                <div class="kpi-value">{{ $rekapPelapor['pelapor_terbanyak']['nama'] }}</div>
                <div class="kpi-sub">{{ number_format($rekapPelapor['pelapor_terbanyak']['jumlah']) }} laporan</div>
            </div>
        </td>
    </tr>
</table>

{{-- TABLE --}}
<div class="section-title">Rekap Laporan Per Bulan</div>

@if(empty($rekapPelapor['rekap']))
    <div class="empty-state">Belum ada data pelaporan {{ $fungsi }} pada periode yang dipilih.</div>
@else
    <table class="rekap-table">
        <thead>
            <tr>
                <th style="width:20px;text-align:center;">No</th>
                <th class="col-fungsi" style="width:75px;">Fungsi</th>
                <th class="col-nama">Nama</th>
                <th class="col-nopek" style="width:55px;">NOPEK</th>
                <th class="col-num" style="width:28px;">Jan</th>
                <th class="col-num" style="width:28px;">Feb</th>
                <th class="col-num" style="width:28px;">Mar</th>
                <th class="col-num" style="width:28px;">Apr</th>
                <th class="col-num" style="width:28px;">Mei</th>
                <th class="col-num" style="width:28px;">Jun</th>
                <th class="col-num" style="width:28px;">Jul</th>
                <th class="col-num" style="width:28px;">Agu</th>
                <th class="col-num" style="width:28px;">Sep</th>
                <th class="col-num" style="width:28px;">Okt</th>
                <th class="col-num" style="width:28px;">Nov</th>
                <th class="col-num" style="width:28px;">Des</th>
                <th class="col-num" style="width:34px;background:#5a7018;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapPelapor['rekap'] as $index => $row)
                <tr class="{{ $index % 2 !== 0 ? 'even' : '' }}">
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td class="col-fungsi"><span class="badge-fungsi">{{ $fungsi }}</span></td>
                    <td class="col-nama">{{ $row['pelapor'] }}</td>
                    <td class="col-nopek">{{ $row['nopek'] ?? '-' }}</td>
                    @for($m = 1; $m <= 12; $m++)
                        @php $v = $row['monthly'][$m] ?? 0; @endphp
                        <td class="col-num {{ $v === 0 ? 'zero' : '' }}">{{ $v > 0 ? number_format($v) : '0' }}</td>
                    @endfor
                    <td class="col-num" style="font-weight:700;background:#eef4d0;color:#2d4a07;">{{ number_format($row['jumlah_total']) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="total-label">Total Keseluruhan</td>
                @for($m = 1; $m <= 12; $m++)
                    <td class="total-val">{{ number_format($rekapPelapor['monthly_totals'][$m] ?? 0) }}</td>
                @endfor
                <td class="total-val" style="background:#5a7018;">{{ number_format($rekapPelapor['total_pelaporan']) }}</td>
            </tr>
        </tfoot>
    </table>
@endif

{{-- FOOTER --}}
<table class="doc-footer">
    <tr>
        <td class="footer-left">
            <strong>Dashboard Monitoring Progress Tindak Lanjut Temuan Lapangan dan Audit</strong><br>
            HSSE PGE Area Lahendong
        </td>
        <td class="footer-right">
            Dokumen digenerate otomatis oleh sistem<br>
            {{ $generatedAt }}
        </td>
    </tr>
</table>

</body>
</html>
