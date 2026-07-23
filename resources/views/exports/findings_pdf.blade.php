<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Temuan SIPEKA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .header-info {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h2>Laporan Temuan SIPEKA</h2>
    <div class="header-info">
        <p><strong>Tanggal Export:</strong> {{ date('d-m-Y H:i') }}</p>
        <p><strong>Fungsi:</strong> {{ $fungsi ?: 'Semua Fungsi' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>ID Temuan</th>
                <th>Tanggal</th>
                <th>Temuan</th>
                <th>Fungsi</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>No SAP</th>
                <th>Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($findings as $index => $finding)
                @php
                    $data = $finding->data_sipeka ?? [];
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $finding->id_temuan }}</td>
                    <td>{{ $data['tanggal'] ?? '-' }}</td>
                    <td>{{ $data['temuan'] ?? '-' }}</td>
                    <td>{{ $data['fungsi'] ?? '-' }}</td>
                    <td>{{ $data['kategori'] ?? '-' }}</td>
                    <td>{{ $finding->monitoring_status }}</td>
                    <td>{{ $finding->no_notifikasi_sap ?? '-' }}</td>
                    <td>{{ $finding->keterangan_tindak_lanjut ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
