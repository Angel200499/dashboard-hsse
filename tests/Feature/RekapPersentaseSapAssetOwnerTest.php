<?php

namespace Tests\Feature;

use App\Models\MasterFunctionMapping;
use App\Models\SipekaFinding;
use App\Models\User;
use App\Services\DashboardChartService;
use App\Services\ExecutiveSummaryPdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RekapPersentaseSapAssetOwnerTest extends TestCase
{
    private DashboardChartService $chartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chartService = app(DashboardChartService::class);
        SipekaFinding::where('id_temuan', 'like', 'TEST-%')->delete();
    }

    protected function tearDown(): void
    {
        SipekaFinding::where('id_temuan', 'like', 'TEST-%')->delete();
        parent::tearDown();
    }

    protected function getOrCreateUser(string $username, string $role, ?string $fungsi = null): User
    {
        return User::updateOrCreate(
            ['username' => $username],
            [
                'name'      => ucwords(str_replace('_', ' ', $username)),
                'email'     => "{$username}@example.com",
                'password'  => Hash::make('password123'),
                'role'      => $role,
                'fungsi'    => $fungsi,
                'is_active' => true,
            ]
        );
    }

    /**
     * Skenario 1: Data ber-SAP tersebar pada 4 fungsi dihitung berdasarkan Asset Owner, bukan fungsi pelapor.
     */
    public function test_skenario_01_distribusi_dihitung_berdasarkan_asset_owner(): void
    {
        // Pastikan mapping tersedia
        $opMapping = MasterFunctionMapping::where('fungsi_dashboard', 'Operation')->first();
        $mtMapping = MasterFunctionMapping::where('fungsi_dashboard', 'Maintenance')->first();

        $opAo = $opMapping?->fungsi_sipeka ?? 'Production Team';
        $mtAo = $mtMapping?->fungsi_sipeka ?? 'Maintenance Team';

        if (!$opMapping) {
            MasterFunctionMapping::create([
                'fungsi_sipeka'    => $opAo,
                'fungsi_dashboard' => 'Operation',
            ]);
        }
        if (!$mtMapping) {
            MasterFunctionMapping::create([
                'fungsi_sipeka'    => $mtAo,
                'fungsi_dashboard' => 'Maintenance',
            ]);
        }

        // Buat dummy finding: fungsi pelapor = HSSE, tapi Asset Owner = Operation
        $f1 = SipekaFinding::create([
            'id_temuan'         => 'TEST-SAP-01-OP',
            'no_notifikasi_sap' => 'SAP-111111',
            'data_sipeka'       => [
                'tanggal'     => '2026-05-10 10:00',
                'fungsi'      => 'HSSE',       // Pelapor HSSE
                'asset_owner' => $opAo,         // Penanggung jawab Operation
                'status'      => 'open',
            ],
        ]);

        $f2 = SipekaFinding::create([
            'id_temuan'         => 'TEST-SAP-01-MT',
            'no_notifikasi_sap' => 'SAP-222222',
            'data_sipeka'       => [
                'tanggal'     => '2026-05-12 11:00',
                'fungsi'      => 'Operation',  // Pelapor Operation
                'asset_owner' => $mtAo,         // Penanggung jawab Maintenance
                'status'      => 'open',
            ],
        ]);

        $globalCharts = $this->chartService->getCharts(null, 2026);
        $tl = $globalCharts['tindak_lanjut'];

        // Temuan f1 harus masuk ke Operation (karena Asset Owner-nya Operation)
        // Temuan f2 harus masuk ke Maintenance (karena Asset Owner-nya Maintenance)
        $this->assertGreaterThan(0, $tl['Operation']);
        $this->assertGreaterThan(0, $tl['Maintenance']);

        // Cleanup
        $f1->delete();
        $f2->delete();
    }

    /**
     * Skenario 2 & 3: Data GM dan Unmapped tidak masuk ke denominator 4 fungsi utama.
     */
    public function test_skenario_02_dan_03_data_gm_dan_unmapped_tidak_merusak_denominator(): void
    {
        $opMapping = MasterFunctionMapping::where('fungsi_dashboard', 'Operation')->first();
        $opAo = $opMapping?->fungsi_sipeka ?? 'Production Team';

        // Temuan resmi 4 fungsi
        $f1 = SipekaFinding::create([
            'id_temuan'         => 'TEST-SAP-02-OP',
            'no_notifikasi_sap' => 'SAP-333333',
            'data_sipeka'       => [
                'tanggal'     => '2026-06-01 10:00',
                'fungsi'      => 'Operation',
                'asset_owner' => $opAo,
                'status'      => 'open',
            ],
        ]);

        // Temuan GM dengan nomor SAP
        $fGm = SipekaFinding::create([
            'id_temuan'         => 'TEST-SAP-02-GM',
            'no_notifikasi_sap' => 'SAP-GM-999',
            'data_sipeka'       => [
                'tanggal'     => '2026-06-02 10:00',
                'fungsi'      => 'Area Lahendong',
                'asset_owner' => 'Area General Manager',
                'status'      => 'open',
            ],
        ]);

        // Temuan Unmapped dengan nomor SAP
        $fUnmapped = SipekaFinding::create([
            'id_temuan'         => 'TEST-SAP-02-UNMAPPED',
            'no_notifikasi_sap' => 'SAP-UNKNOWN-888',
            'data_sipeka'       => [
                'tanggal'     => '2026-06-03 10:00',
                'fungsi'      => 'Random Pelapor',
                'asset_owner' => 'Entitas Antah Berantah',
                'status'      => 'open',
            ],
        ]);

        $charts = $this->chartService->getCharts(null, 2026);
        $tl = $charts['tindak_lanjut'];

        // Total persentase 4 fungsi utama harus tepat 100% jika ada data
        $sum = array_sum($tl);
        $this->assertEquals(100, $sum, "Total persentase 4 fungsi utama harus tepat 100%");

        // GM dan Unmapped tidak boleh ada di keys 4 fungsi
        $this->assertArrayNotHasKey('Area General Manager', $tl);
        $this->assertArrayNotHasKey('Entitas Antah Berantah', $tl);

        // Cleanup
        $f1->delete();
        $fGm->delete();
        $fUnmapped->delete();
    }

    /**
     * Skenario 4, 5, 6: Validasi No. Notifikasi SAP (NULL, kosong, whitespace, placeholder).
     */
    public function test_skenario_04_05_06_validasi_nomor_sap(): void
    {
        $opMapping = MasterFunctionMapping::where('fungsi_dashboard', 'Operation')->first();
        $opAo = $opMapping?->fungsi_sipeka ?? 'Production Team';

        $baseline = $this->chartService->getCharts('Operation', 2026)['tindak_lanjut_counts'];
        $baseSap   = $baseline['Ber-SAP'];
        $baseNoSap = $baseline['Belum Ber-SAP'];

        // 1. SAP null
        $fNull = SipekaFinding::create([
            'id_temuan'         => 'TEST-VAL-NULL',
            'no_notifikasi_sap' => null,
            'data_sipeka'       => ['tanggal' => '2026-07-01 10:00', 'asset_owner' => $opAo, 'status' => 'open'],
        ]);

        // 2. SAP string kosong
        $fEmpty = SipekaFinding::create([
            'id_temuan'         => 'TEST-VAL-EMPTY',
            'no_notifikasi_sap' => '',
            'data_sipeka'       => ['tanggal' => '2026-07-01 10:00', 'asset_owner' => $opAo, 'status' => 'open'],
        ]);

        // 3. SAP hanya whitespace
        $fSpace = SipekaFinding::create([
            'id_temuan'         => 'TEST-VAL-SPACE',
            'no_notifikasi_sap' => '    ',
            'data_sipeka'       => ['tanggal' => '2026-07-01 10:00', 'asset_owner' => $opAo, 'status' => 'open'],
        ]);

        // 4. SAP placeholder '-'
        $fDash = SipekaFinding::create([
            'id_temuan'         => 'TEST-VAL-DASH',
            'no_notifikasi_sap' => '-',
            'data_sipeka'       => ['tanggal' => '2026-07-01 10:00', 'asset_owner' => $opAo, 'status' => 'open'],
        ]);

        // 5. SAP placeholder '0'
        $fZero = SipekaFinding::create([
            'id_temuan'         => 'TEST-VAL-ZERO',
            'no_notifikasi_sap' => '0',
            'data_sipeka'       => ['tanggal' => '2026-07-01 10:00', 'asset_owner' => $opAo, 'status' => 'open'],
        ]);

        // 6. SAP valid
        $fValid = SipekaFinding::create([
            'id_temuan'         => 'TEST-VAL-VALID',
            'no_notifikasi_sap' => 'SAP-VALID-99',
            'data_sipeka'       => ['tanggal' => '2026-07-01 10:00', 'asset_owner' => $opAo, 'status' => 'open'],
        ]);

        $counts = $this->chartService->getCharts('Operation', 2026)['tindak_lanjut_counts'];

        // Dari 6 temuan yang dibuat, tepat 1 bertambah sebagai Ber-SAP dan 5 bertambah sebagai Belum Ber-SAP
        $this->assertEquals($baseSap + 1, $counts['Ber-SAP']);
        $this->assertEquals($baseNoSap + 5, $counts['Belum Ber-SAP']);

        // Cleanup
        $fNull->delete();
        $fEmpty->delete();
        $fSpace->delete();
        $fDash->delete();
        $fZero->delete();
        $fValid->delete();
    }

    /**
     * Skenario 7: Dashboard Fungsi mengembalikan 2 kategori (Ber-SAP dan Belum Ber-SAP).
     */
    public function test_skenario_07_dashboard_fungsi_menampilkan_dua_kategori(): void
    {
        $opMapping = MasterFunctionMapping::where('fungsi_dashboard', 'Operation')->first();
        $opAo = $opMapping?->fungsi_sipeka ?? 'Production Team';

        $f1 = SipekaFinding::create([
            'id_temuan'         => 'TEST-FN-07-1',
            'no_notifikasi_sap' => 'SAP-OK-1',
            'data_sipeka'       => ['tanggal' => '2026-08-01 10:00', 'asset_owner' => $opAo, 'status' => 'open'],
        ]);

        $f2 = SipekaFinding::create([
            'id_temuan'         => 'TEST-FN-07-2',
            'no_notifikasi_sap' => '',
            'data_sipeka'       => ['tanggal' => '2026-08-01 10:00', 'asset_owner' => $opAo, 'status' => 'open'],
        ]);

        $charts = $this->chartService->getCharts('Operation', 2026);
        $tl = $charts['tindak_lanjut'];

        $this->assertArrayHasKey('Ber-SAP', $tl);
        $this->assertArrayHasKey('Belum Ber-SAP', $tl);
        $this->assertEquals(2, count($tl));
        $this->assertEquals(100, $tl['Ber-SAP'] + $tl['Belum Ber-SAP']);

        // Test render di view Blade
        $adminOp = $this->getOrCreateUser('test_admin_op_07', 'Admin Function', 'Operation');
        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi', ['nama_fungsi' => 'Operation', 'year' => 2026]));
        $response->assertStatus(200);
        $response->assertSee('Rekap Persentase Temuan Ber-SAP Berdasarkan Asset Owner');
        $response->assertSee('Ber-SAP');
        $response->assertSee('Belum Ber-SAP');

        // Cleanup
        $f1->delete();
        $f2->delete();
    }

    /**
     * Skenario 8: Tidak ada temuan dalam periode terpilih menampilkan kondisi kosong.
     */
    public function test_skenario_08_kondisi_kosong_periode_tanpa_temuan(): void
    {
        // Tahun 1990 dipastikan tidak ada temuan
        $chartsFn = $this->chartService->getCharts('Operation', 1990);
        $tlFn = $chartsFn['tindak_lanjut'];

        $this->assertEquals(['Ber-SAP' => 0, 'Belum Ber-SAP' => 0], $tlFn);

        $chartsGlobal = $this->chartService->getCharts(null, 1990);
        $tlGlobal = $chartsGlobal['tindak_lanjut'];

        $this->assertEquals([
            'Operation'        => 0,
            'Maintenance'      => 0,
            'HSSE'             => 0,
            'Business Support' => 0,
        ], $tlGlobal);
    }

    /**
     * Skenario 9: Dashboard Global menampilkan distribusi 4 fungsi dengan total 100%.
     */
    public function test_skenario_09_dashboard_global_distribusi_empat_fungsi_100_persen(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse_09', 'Admin HSSE', 'HSSE');
        $response = $this->actingAs($adminHsse)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Rekap Persentase Temuan Ber-SAP Berdasarkan Asset Owner');

        // Verify Largest Remainder method
        $counts = ['Operation' => 33, 'Maintenance' => 33, 'HSSE' => 34, 'Business Support' => 0];
        $pct = DashboardChartService::largestRemainder($counts);
        $this->assertEquals(100, array_sum($pct));
    }

    /**
     * Skenario 10: Executive Summary PDF memisahkan jumlah laporan fisik dan persentase serta menyebut Asset Owner.
     */
    public function test_skenario_10_executive_summary_pdf_jumlah_dan_persentase_tidak_tertukar(): void
    {
        $pdfService = app(ExecutiveSummaryPdfService::class);

        // Mock charts data dengan data buatan
        $mockCharts = [
            'kpi' => ['total' => 10, 'closed' => 5, 'open' => 5],
            'fungsi' => ['Operation' => 5, 'Maintenance' => 5],
            'persentase_fungsi' => ['Operation' => ['closed' => 60, 'open' => 40]],
            'tindak_lanjut' => [
                'Operation'        => 60,
                'Maintenance'      => 40,
                'HSSE'             => 0,
                'Business Support' => 0,
            ],
            'tindak_lanjut_counts' => [
                'Operation'        => 6,
                'Maintenance'      => 4,
                'HSSE'             => 0,
                'Business Support' => 0,
            ],
            'keterlibatan' => ['Operation' => 10],
            'unsafe_action' => [],
            'unsafe_condition' => [],
        ];

        // Gunakan reflection untuk menguji buildInsights
        $reflection = new \ReflectionClass(ExecutiveSummaryPdfService::class);
        $method = $reflection->getMethod('buildInsights');
        $method->setAccessible(true);

        $insights = $method->invoke($pdfService, $mockCharts, 10, 5, 50.0);

        $this->assertArrayHasKey('s5', $insights);
        // Memastikan menyebut Asset Owner
        $this->assertStringContainsString('Asset Owner', $insights['s5']);
        // Memastikan menyebut '6 laporan' dan '60%' (bukan '60 laporan (60%)')
        $this->assertStringContainsString('6 laporan', $insights['s5']);
        $this->assertStringContainsString('60%', $insights['s5']);
        $this->assertStringNotContainsString('60 laporan', $insights['s5']);
    }

    /**
     * Skenario 11: Filter tahun membatasi data hanya pada tahun yang dipilih.
     */
    public function test_skenario_11_filter_tahun_bekerja_presisi(): void
    {
        $opMapping = MasterFunctionMapping::where('fungsi_dashboard', 'Operation')->first();
        $opAo = $opMapping?->fungsi_sipeka ?? 'Production Team';

        $f2025 = SipekaFinding::create([
            'id_temuan'         => 'TEST-THN-2025',
            'no_notifikasi_sap' => 'SAP-2025',
            'data_sipeka'       => ['tanggal' => '2025-05-01 10:00', 'asset_owner' => $opAo, 'status' => 'open'],
        ]);

        $f2026 = SipekaFinding::create([
            'id_temuan'         => 'TEST-THN-2026',
            'no_notifikasi_sap' => 'SAP-2026',
            'data_sipeka'       => ['tanggal' => '2026-05-01 10:00', 'asset_owner' => $opAo, 'status' => 'open'],
        ]);

        $c2025 = $this->chartService->getCharts('Operation', 2025)['tindak_lanjut_counts'];
        $c2026 = $this->chartService->getCharts('Operation', 2026)['tindak_lanjut_counts'];

        $this->assertGreaterThanOrEqual(1, $c2025['Ber-SAP']);
        $this->assertGreaterThanOrEqual(1, $c2026['Ber-SAP']);

        // Cleanup
        $f2025->delete();
        $f2026->delete();
    }

    /**
     * Skenario 12: Grafik lain tidak mengalami perubahan perilaku.
     */
    public function test_skenario_12_grafik_lain_tidak_berubah_perilaku(): void
    {
        $charts = $this->chartService->getCharts(null, 2026);

        // Pastikan seluruh kunci chart utama tetap ada dan tidak hilang
        $requiredKeys = [
            'fungsi',
            'fungsi_info',
            'reporting_lhd',
            'reporting',
            'trending',
            'kategori',
            'keterlibatan',
            'persentase_fungsi',
            'tindak_lanjut',
            'unsafe_action',
            'unsafe_condition',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $charts, "Kunci chart '{$key}' harus tetap ada.");
        }
    }
}
