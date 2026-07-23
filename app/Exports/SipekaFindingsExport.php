<?php

namespace App\Exports;

use App\Models\SipekaFinding;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

/**
 * SipekaFindingsExport
 *
 * Business Rule #7:
 *   Export adalah hasil Dashboard — bukan raw Excel SIPEKA.
 *   Kolom no_notifikasi_sap dan keterangan_tindak_lanjut diambil dari DB
 *   (nilai terbaru yang sudah diinput manual Admin), bukan dari data_sipeka JSON.
 *   Menyertakan kolom "Monitoring Status" (Computed: Open/In Progress/Closed).
 *
 * Support filter:
 *   - By Fungsi
 *   - By Computed Monitoring Status
 *   - By Date Range
 */
class SipekaFindingsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithStyles,
    WithColumnWidths
{
    public function __construct(
        private readonly ?string $fungsi       = null,
        private readonly ?string $statusFilter = null,
        private readonly ?string $dateFilter   = null,
        private readonly ?string $yearFilter   = null,
        private readonly ?string $search       = null
    ) {}

    public function collection()
    {
        $queryService = app(\App\Services\FindingQueryService::class);
        $query = SipekaFinding::query();

        // Filter by fungsi
        if ($this->fungsi) {
            $query->where('data_sipeka->fungsi', 'like', "%{$this->fungsi}%");
        }

        // Gunakan FindingQueryService untuk memastikan semua filter IDENTIK dengan tabel
        $request = new \Illuminate\Http\Request([
            'status_filter' => $this->statusFilter,
            'date_filter'   => $this->dateFilter,
            'year'          => $this->yearFilter,
            'search'        => $this->search,
        ]);

        $queryService->applyFilters($query, $request);

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Map satu SipekaFinding ke satu baris Excel.
     * no_notifikasi_sap dan keterangan_tindak_lanjut diambil dari DB (Business Rule #7).
     * Monitoring Status = Computed (Business Rule #1).
     */
    public function map($finding): array
    {
        $data = $finding->data_sipeka ?? [];

        return [
            $finding->id_temuan,
            $data['tanggal']       ?? '-',
            $data['temuan']        ?? '-',
            $data['fungsi']        ?? '-',
            $data['pelapor']       ?? '-',
            $data['kategori']      ?? '-',
            $data['unsafe_action'] ?? '-',
            $data['unsafe_conditon'] ?? '-',   // intentional typo sesuai Excel SIPEKA
            $data['status']        ?? '-',     // Status SIPEKA original
            $finding->monitoring_status,       // Computed Monitoring Status
            $data['assign']        ?? '-',
            $data['asset_owner']   ?? '-',
            $data['assigndate']    ?? '-',
            $data['target']        ?? '-',
            $finding->no_notifikasi_sap        ?? '-',   // Dari DB (hasil update manual)
            $finding->keterangan_tindak_lanjut ?? '-',   // Dari DB (hasil update manual)
            $data['closeby']       ?? '-',
            $data['closefungsi']   ?? '-',
            $data['verifyby']      ?? '-',
            $data['verifydate']    ?? '-',
            $data['fototemuan']    ?? '-',   // URL string (Business Rule #5)
            $data['fotoclose']     ?? '-',   // URL string (Business Rule #5)
            $data['userstatus']    ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'ID Temuan',
            'Tanggal',
            'Temuan',
            'Fungsi',
            'Pelapor',
            'Kategori',
            'Unsafe Action',
            'Unsafe Condition',
            'Status SIPEKA',
            'Monitoring Status',   // Kolom tambahan — Computed Business Rule
            'Assign',
            'Asset Owner',
            'Assign Date',
            'Target',
            'No. SAP',
            'Keterangan Tindak Lanjut',
            'Close By',
            'Close Fungsi',
            'Verify By',
            'Verify Date',
            'Foto Temuan',
            'Foto Close',
            'User Status',
        ];
    }

    public function title(): string
    {
        return 'Temuan SIPEKA ' . ($this->fungsi ?? 'All');
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row bold + background
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A5F'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,  // ID Temuan
            'B' => 12,  // Tanggal
            'C' => 40,  // Temuan
            'D' => 16,  // Fungsi
            'E' => 20,  // Pelapor
            'F' => 18,  // Kategori
            'G' => 30,  // Unsafe Action
            'H' => 30,  // Unsafe Condition
            'I' => 14,  // Status SIPEKA
            'J' => 16,  // Monitoring Status
            'O' => 20,  // No. SAP
            'P' => 40,  // Keterangan
        ];
    }
}
