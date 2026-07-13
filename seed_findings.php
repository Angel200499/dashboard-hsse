<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SipekaFinding;

SipekaFinding::truncate();

$dummyData = [
    [
        'id_temuan' => 'SIPEKA-2026-001',
        'no_notifikasi_sap' => 'SAP-90001234',
        'keterangan_tindak_lanjut' => 'Material sedang dalam pemesanan, dijadwalkan tiba minggu depan.',
        'data_sipeka' => [
            'tanggal' => date('Y-m-d', strtotime('-2 days')),
            'temuan' => 'Pipa air pendingin mengalami kebocoran kecil di area Pompa Utama.',
            'fungsi' => 'Operation',
            'pelapor' => 'Budi Santoso',
            'kategori' => 'Asset Integrity',
            'unsafe_action' => '-',
            'unsafe_conditon' => 'Pipa berkarat dan bocor',
            'status' => 'Open',
            'assign' => 'Tim Maintenance Mekanik',
            'asset_owner' => 'Operation Manager',
            'assigndate' => date('Y-m-d', strtotime('-1 days')),
            'target' => date('Y-m-d', strtotime('+3 days')),
            'closeby' => '-',
            'closefungsi' => '-',
            'verifyby' => '-',
            'verifydate' => '-',
            'userstatus' => 'In Progress',
            'fototemuan' => 'https://images.unsplash.com/photo-1581092580497-e0d23cbdf1dc?q=80&w=600&auto=format&fit=crop',
            'fotoclose' => ''
        ]
    ],
    [
        'id_temuan' => 'SIPEKA-2026-002',
        'no_notifikasi_sap' => 'SAP-90001235',
        'keterangan_tindak_lanjut' => 'Area telah dibersihkan dan dipasangi rambu peringatan.',
        'data_sipeka' => [
            'tanggal' => date('Y-m-d', strtotime('-5 days')),
            'temuan' => 'Tumpahan oli di lantai area Workshop 2.',
            'fungsi' => 'Maintenance',
            'pelapor' => 'Agus Pratama',
            'kategori' => 'Environment',
            'unsafe_action' => 'Tidak membersihkan sisa pekerjaan',
            'unsafe_conditon' => 'Lantai licin',
            'status' => 'Closed',
            'assign' => 'Tim Facility',
            'asset_owner' => 'Workshop Spv',
            'assigndate' => date('Y-m-d', strtotime('-5 days')),
            'target' => date('Y-m-d', strtotime('-4 days')),
            'closeby' => 'Joko',
            'closefungsi' => 'Facility',
            'verifyby' => 'HSSE Officer',
            'verifydate' => date('Y-m-d', strtotime('-3 days')),
            'userstatus' => 'Completed',
            'fototemuan' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?q=80&w=600&auto=format&fit=crop',
            'fotoclose' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?q=80&w=600&auto=format&fit=crop'
        ]
    ],
    [
        'id_temuan' => 'SIPEKA-2026-003',
        'no_notifikasi_sap' => '',
        'keterangan_tindak_lanjut' => '',
        'data_sipeka' => [
            'tanggal' => date('Y-m-d'),
            'temuan' => 'Kabel terkelupas di area kontrol panel utama.',
            'fungsi' => 'HSSE',
            'pelapor' => 'Siti Aminah',
            'kategori' => 'Electrical Safety',
            'unsafe_action' => '-',
            'unsafe_conditon' => 'Kabel terbuka berpotensi korsleting',
            'status' => 'Open',
            'assign' => 'Tim Electrical',
            'asset_owner' => 'Electrical Engineer',
            'assigndate' => date('Y-m-d'),
            'target' => date('Y-m-d', strtotime('+1 days')),
            'closeby' => '-',
            'closefungsi' => '-',
            'verifyby' => '-',
            'verifydate' => '-',
            'userstatus' => 'New',
            'fototemuan' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?q=80&w=600&auto=format&fit=crop',
            'fotoclose' => ''
        ]
    ]
];

foreach ($dummyData as $data) {
    SipekaFinding::create($data);
}

echo "Successfully inserted 3 dummy Sipeka Findings.";
