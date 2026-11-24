<?php

namespace Tests\Feature;

use App\Models\SipekaFinding;
use App\Models\User;
use Tests\TestCase;

class DetailTemuanViewTest extends TestCase
{
    protected function getOrCreateUser(string $username, string $role, ?string $fungsi = null): User
    {
        return User::firstOrCreate(
            ['username' => $username],
            [
                'name'     => ucfirst(str_replace('_', ' ', $username)),
                'password' => bcrypt('password'),
                'role'     => $role,
                'fungsi'   => $fungsi,
            ]
        );
    }

    public function test_detail_temuan_page_renders_successfully_with_all_sections_and_fields(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse_detail', 'Admin HSSE', 'HSSE');
        $finding = SipekaFinding::first();

        $this->assertNotNull($finding, 'Database should have at least one finding');

        $response = $this->actingAs($adminHsse)->get(route('findings.show', $finding->id));
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Header elements
        $response->assertSee('Detail Temuan');
        $response->assertSee('ID: ' . $finding->id_temuan);
        $response->assertSee(route('findings.index'));

        // 2. Section 1: Informasi Temuan
        $response->assertSee('Informasi Temuan');
        $response->assertSee('Deskripsi Temuan');
        $response->assertSee('Tanggal Temuan');
        $response->assertSee('Fungsi');
        $response->assertSee('Pelapor');
        $response->assertSee('Kategori');
        $response->assertSee('Unsafe Action');
        $response->assertSee('Unsafe Condition');

        // 3. Section 2: Tindak Lanjut & SAP
        $response->assertSee('Tindak Lanjut & SAP', false);
        $response->assertSee('No. Notifikasi SAP');
        $response->assertSee('Keterangan Tindak Lanjut');

        // 4. Section 3: Penugasan
        $response->assertSee('Penugasan');
        $response->assertSee('Assign Ke');
        $response->assertSee('Asset Owner');
        $response->assertSee('Assign Date');
        $response->assertSee('Target');

        // 5. Section 4: Validasi & Closing
        $response->assertSee('Validasi & Closing', false);
        $response->assertSee('Close By');
        $response->assertSee('Close Fungsi');
        $response->assertSee('Verify By');
        $response->assertSee('Verify Date');
        $response->assertSee('User Status');

        // 6. Section 5: Galeri Foto
        $response->assertSee('Galeri Foto');
        $response->assertSee('Foto Temuan (Sebelum)');
        $response->assertSee('Foto Close (Sesudah)');

        // 7. Two-column desktop grid structure
        $this->assertStringContainsString('lg:grid-cols-2', $content);
    }

    public function test_detail_temuan_empty_data_renders_dash_placeholder(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse_detail', 'Admin HSSE', 'HSSE');
        
        // Buat dummy finding dengan data kosong
        $emptyFinding = SipekaFinding::create([
            'id_temuan' => 'TEST-999999',
            'data_sipeka' => [],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $response = $this->actingAs($adminHsse)->get(route('findings.show', $emptyFinding->id));
            $response->assertStatus(200);

            // Cek placeholder "-"
            $response->assertSee('-');
            $response->assertSee('Tidak ada foto');
        } finally {
            $emptyFinding->delete();
        }
    }

    public function test_detail_temuan_excel_serial_date_is_formatted_correctly(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse_detail', 'Admin HSSE', 'HSSE');
        
        // Buat dummy finding dengan target serial date Excel 46260 (2026-08-26)
        $excelDateFinding = SipekaFinding::create([
            'id_temuan' => 'TEST-EXCEL-DATE',
            'data_sipeka' => [
                'target' => 46260,
                'assigndate' => 46036,
                'status' => 'Open',
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $response = $this->actingAs($adminHsse)->get(route('findings.show', $excelDateFinding->id));
            $response->assertStatus(200);

            // Target 46260 harus terformat menjadi 2026-08-26
            $response->assertSee('2026-08-26');
            // Assign Date 46036 harus terformat menjadi 2026-01-14
            $response->assertSee('2026-01-14');
            // Jangan menampilkan angka serial mentah 46260 di tampilan tanggal
            $response->assertDontSee('>46260<', false);
        } finally {
            $excelDateFinding->delete();
        }
    }
}
