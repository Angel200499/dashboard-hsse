<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Pelapor Business Support</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #ffffff;
            padding: 32px 36px;
        }
        .green-bar {
            height: 4px;
            background: #9DBF2A;
            border-radius: 2px;
            margin-bottom: 18px;
        }
        /* HEADER */
        .header {
            border-bottom: 2px solid #9DBF2A;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }
        .header-row {
            width: 100%;
            border-collapse: collapse;
        }
        .header-row td { vertical-align: top; padding: 0; }
        .header-title h1 {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }
        .header-title h2 {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-top: 3px;
        }
        .header-meta {
            text-align: right;
            color: #64748b;
            font-size: 9.5px;
            line-height: 1.8;
        }
        .badge-periode {
            display: inline-block;
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
            font-size: 10px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
        /* KPI */
        .section-title {
            font-size: 9.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }
        .kpi-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin-bottom: 20px;
        }
        .kpi-grid td { width: 33.33%; padding: 0; vertical-align: top; }
        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        .kpi-card.highlight {
            background: #fefce8;
            border-color: #fbbf24;
        }
        .kpi-label {
            font-size: 8.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 6px;
        }
        .kpi-value {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1;
        }
        .kpi-card.highlight .kpi-value {
            font-size: 13px;
            color: #92400e;
        }
        .kpi-sub {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 4px;
        }
        /* TABLE */
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .rekap-table thead tr {
            background: #1e293b;
            color: #ffffff;
        }
        .rekap-table thead th {
            padding: 9px 12px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .rekap-table thead th.col-no  { width: 40px; text-align: center; }
        .rekap-table thead th.col-jml { width: 110px; text-align: right; }
        .rekap-table tbody tr { border-bottom: 1px solid #e2e8f0; }
        .rekap-table tbody tr.even { background: #f8fafc; }
        .rekap-table tbody tr.top   { background: #fefce8; }
        .rekap-table tbody td { padding: 8px 12px; color: #334155; }
        .rekap-table tbody td.col-no  { text-align: center; color: #94a3b8; font-weight: 600; }
        .rekap-table tbody td.col-jml { text-align: right; font-weight: 700; color: #0f172a; }
        .rekap-table tfoot tr { background: #1e293b; color: #ffffff; }
        .rekap-table tfoot td { padding: 9px 12px; font-weight: 700; }
        .rekap-table tfoot td.total-val { text-align: right; }
        /* EMPTY */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            border: 1px dashed #e2e8f0;
            border-radius: 6px;
            font-style: italic;
        }
        /* FOOTER */
        .doc-footer {
            margin-top: 28px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            width: 100%;
            border-collapse: collapse;
        }
        .doc-footer td { padding: 0; vertical-align: bottom; }
        .footer-left  { color: #64748b; font-size: 9px; line-height: 1.6; }
        .footer-right { color: #94a3b8; font-size: 9px; text-align: right; line-height: 1.6; }
    </style>
</head>
<body>

<div class="green-bar"></div>

{{-- HEADER --}}
<div class="header">
    <table class="header-row">
        <tr>
            <td class="header-title">
                <h1>Rekap Pelapor Temuan</h1>
                <h2>Business Support &mdash; PGE Area Lahendong</h2>
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
<div class="section-title">Daftar Pelapor</div>

@if(empty($rekapPelapor['rekap']))
    <div class="empty-state">Belum ada data pelaporan Business Support pada periode yang dipilih.</div>
@else
    <table class="rekap-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th>Nama Pelapor</th>
                <th class="col-jml">Jumlah Pelaporan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapPelapor['rekap'] as $index => $row)
                <tr class="{{ $index === 0 ? 'top' : ($index % 2 === 0 ? '' : 'even') }}">
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td>{{ $row['pelapor'] }}</td>
                    <td class="col-jml">{{ number_format($row['jumlah']) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total Keseluruhan</td>
                <td class="total-val">{{ number_format($rekapPelapor['total_pelaporan']) }}</td>
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
