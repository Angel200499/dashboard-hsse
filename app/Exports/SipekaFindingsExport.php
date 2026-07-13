<?php

namespace App\Exports;

use App\Models\SipekaFinding;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SipekaFindingsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $fungsi;

    public function __construct($fungsi = null)
    {
        $this->fungsi = $fungsi;
    }

    public function collection()
    {
        if ($this->fungsi) {
            return SipekaFinding::where('data_sipeka->fungsi', $this->fungsi)->get();
        }
        return SipekaFinding::all();
    }

    public function map($finding): array
    {
        $data = $finding->data_sipeka ?? [];
        return [
            $finding->id_temuan,
            $data['tanggal'] ?? '-',
            $data['temuan'] ?? '-',
            $data['fungsi'] ?? '-',
            $data['pelapor'] ?? '-',
            $data['kategori'] ?? '-',
            $data['unsafe_action'] ?? '-',
            $data['unsafe_conditon'] ?? '-',
            $data['status'] ?? '-',
            $data['assign'] ?? '-',
            $data['asset_owner'] ?? '-',
            $data['assigndate'] ?? '-',
            $data['target'] ?? '-',
            $finding->no_notifikasi_sap ?? '-',
            $finding->keterangan_tindak_lanjut ?? '-',
            $data['closeby'] ?? '-',
            $data['closefungsi'] ?? '-',
            $data['verifyby'] ?? '-',
            $data['verifydate'] ?? '-',
            $data['fototemuan'] ?? '-',
            $data['fotoclose'] ?? '-',
            $data['userstatus'] ?? '-'
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
            'Status',
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
            'User Status'
        ];
    }
}
