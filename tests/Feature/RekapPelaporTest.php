<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MasterFunctionMapping;
use App\Models\SipekaFinding;
use App\Services\DashboardChartService;
use Illuminate\Support\Facades\DB;

class RekapPelaporTest extends TestCase
{
    /**
     * 1. Data Rekap Pelapor terisolasi per fungsi dan COUNT(DISTINCT id_temuan) akurat.
     */
    public function test_rekap_pelapor_data_and_counts_per_fungsi(): void
    {
        $service = app(DashboardChartService::class);

        foreach (MasterFunctionMapping::DASHBOARD_FUNGSI_LIST as $fungsi) {
            $rekapData = $service->getFunctionReporterRecap($fungsi);
            $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);

            // Verifikasi jumlah pelaporan sesuai COUNT(DISTINCT id_temuan) di DB
            $directCount = SipekaFinding::query()
                ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues)
                ->count(DB::raw("DISTINCT id_temuan"));

            $this->assertEquals($directCount, $rekapData['total_pelaporan'], "Total pelaporan {$fungsi} harus sama dengan DISTINCT id_temuan");

            // Verifikasi sum bulanan sama dengan total
            $sumMonthly = array_sum($rekapData['monthly_totals']);
            $this->assertEquals($rekapData['total_pelaporan'], $sumMonthly, "Sum bulanan {$fungsi} harus sama dengan total pelaporan");

            // Verifikasi setiap baris pelapor konsisten dengan breakdown bulanannya
            foreach ($rekapData['rekap'] as $row) {
                $this->assertEquals($row['jumlah_total'], array_sum($row['monthly']), "Total baris pelapor {$row['pelapor']} harus sama dengan sum bulannya");
            }
        }

        // Business Support backward compatibility
        $bsLegacy = $service->getBusinessSupportReporterRecap();
        $this->assertEquals(92, $bsLegacy['total_pelapor'], "Business Support backward compatibility: 92 pelapor");
        $this->assertEquals(3200, $bsLegacy['total_pelaporan'], "Business Support backward compatibility: 3200 total temuan");
    }

    /**
     * 2. Filter periode dan pencarian nama pelapor berfungsi.
     */
    public function test_rekap_pelapor_period_and_search_filters(): void
    {
        $service = app(DashboardChartService::class);

        // Filter per bulan
        $febRecap = $service->getFunctionReporterRecap('Operation', 'per_bulan', 2, 2026);
        $this->assertEquals($febRecap['total_pelaporan'], $febRecap['monthly_totals'][2]);

        // Filter search
        $searchResult = $service->getFunctionReporterRecap('Operation', '', 0, 0, 'Bayu');
        $this->assertNotEmpty($searchResult['rekap']);
        foreach ($searchResult['rekap'] as $r) {
            $this->assertStringContainsStringIgnoringCase('Bayu', $r['pelapor']);
        }
    }

    /**
     * 3. NOPEK terisi jika tersedia dan '-' jika tidak tersedia (tidak ada dummy).
     */
    public function test_nopek_resolution_and_no_dummy_data(): void
    {
        $service = app(DashboardChartService::class);

        $opRecap = $service->getFunctionReporterRecap('Operation');
        $bayu = collect($opRecap['rekap'])->firstWhere('pelapor', 'Aji Bayu Kurniawan');
        $this->assertNotNull($bayu);
        $this->assertEquals('19020356', $bayu['nopek']);

        $hsseRecap = $service->getFunctionReporterRecap('HSSE');
        $iqbal = collect($hsseRecap['rekap'])->firstWhere('pelapor', 'Muhammad Iqbal');
        $this->assertNotNull($iqbal);
        $this->assertEquals('88001727', $iqbal['nopek']);

        // Pelapor tanpa NOPEK harus '-'
        $hasDash = collect($opRecap['rekap'])->contains(fn ($r) => $r['nopek'] === '-');
        $this->assertTrue($hasDash);
    }

    /**
     * 4. Hak Akses: Admin & Manager Operation hanya dapat melihat Operation.
     */
    public function test_authorization_operation_roles(): void
    {
        foreach (['admin_op', 'mgr_operation'] as $uname) {
            $user = User::where('username', $uname)->first();
            $this->assertNotNull($user);

            // Boleh akses Operation
            $resAllowed = $this->actingAs($user)->get('/dashboard/fungsi/operation/rekap-pelapor');
            $resAllowed->assertStatus(200);

            // Dilarang akses fungsi lain
            $this->actingAs($user)->get('/dashboard/fungsi/maintenance/rekap-pelapor')->assertStatus(403);
            $this->actingAs($user)->get('/dashboard/fungsi/hsse/rekap-pelapor')->assertStatus(403);
            $this->actingAs($user)->get('/dashboard/fungsi/business-support/rekap-pelapor')->assertStatus(403);
        }
    }

    /**
     * 5. Hak Akses: Admin & Manager Maintenance hanya dapat melihat Maintenance.
     */
    public function test_authorization_maintenance_roles(): void
    {
        foreach (['admin_mt', 'mgr_maintenance'] as $uname) {
            $user = User::where('username', $uname)->first();
            $this->assertNotNull($user);

            // Boleh akses Maintenance
            $resAllowed = $this->actingAs($user)->get('/dashboard/fungsi/maintenance/rekap-pelapor');
            $resAllowed->assertStatus(200);

            // Dilarang akses fungsi lain
            $this->actingAs($user)->get('/dashboard/fungsi/operation/rekap-pelapor')->assertStatus(403);
            $this->actingAs($user)->get('/dashboard/fungsi/hsse/rekap-pelapor')->assertStatus(403);
            $this->actingAs($user)->get('/dashboard/fungsi/business-support/rekap-pelapor')->assertStatus(403);
        }
    }

    /**
     * 6. Hak Akses: Admin & Manager HSSE hanya dapat melihat HSSE.
     */
    public function test_authorization_hsse_roles(): void
    {
        foreach (['admin_hsse_f', 'mgr_hsse'] as $uname) {
            $user = User::where('username', $uname)->first();
            $this->assertNotNull($user);

            // Boleh akses HSSE
            $resAllowed = $this->actingAs($user)->get('/dashboard/fungsi/hsse/rekap-pelapor');
            $resAllowed->assertStatus(200);

            // Dilarang akses fungsi lain
            $this->actingAs($user)->get('/dashboard/fungsi/operation/rekap-pelapor')->assertStatus(403);
            $this->actingAs($user)->get('/dashboard/fungsi/maintenance/rekap-pelapor')->assertStatus(403);
            $this->actingAs($user)->get('/dashboard/fungsi/business-support/rekap-pelapor')->assertStatus(403);
        }
    }

    /**
     * 7. Hak Akses: Admin & Manager Business Support tetap dapat melihat Business Support.
     */
    public function test_authorization_business_support_roles(): void
    {
        foreach (['admin_bs', 'mgr_business_support'] as $uname) {
            $user = User::where('username', $uname)->first();
            $this->assertNotNull($user);

            // Boleh akses Business Support
            $resAllowed = $this->actingAs($user)->get('/dashboard/fungsi/business-support/rekap-pelapor');
            $resAllowed->assertStatus(200);

            // Dilarang akses fungsi lain
            $this->actingAs($user)->get('/dashboard/fungsi/operation/rekap-pelapor')->assertStatus(403);
            $this->actingAs($user)->get('/dashboard/fungsi/maintenance/rekap-pelapor')->assertStatus(403);
            $this->actingAs($user)->get('/dashboard/fungsi/hsse/rekap-pelapor')->assertStatus(403);
        }
    }

    /**
     * 8. Superadmin memiliki akses global ke seluruh fungsi.
     */
    public function test_superadmin_global_access(): void
    {
        $superadmin = User::where('username', 'admin')->first();
        $this->assertNotNull($superadmin);

        foreach (['operation', 'maintenance', 'hsse', 'business-support'] as $f) {
            $res = $this->actingAs($superadmin)->get("/dashboard/fungsi/{$f}/rekap-pelapor");
            $res->assertStatus(200);
        }
    }

    /**
     * 9. Sidebar menampilkan menu Rekap Pelapor untuk seluruh fungsi.
     */
    public function test_sidebar_displays_rekap_pelapor(): void
    {
        $users = [
            'admin_op' => 'operation',
            'admin_mt' => 'maintenance',
            'admin_hsse_f' => 'hsse',
            'admin_bs' => 'business-support',
            'admin' => 'hsse',
        ];

        foreach ($users as $uname => $slug) {
            $user = User::where('username', $uname)->first();
            $this->assertNotNull($user);

            $res = $this->actingAs($user)->get("/dashboard/fungsi/{$slug}/rekap-pelapor");
            $res->assertStatus(200);
            $res->assertSee('Rekap Pelapor');
        }
    }

    /**
     * 10. Fitur Export PDF bekerja dan terlindungi otorisasi.
     */
    public function test_export_pdf_and_authorization(): void
    {
        $userBs = User::where('username', 'admin_bs')->first();
        $resPdfBs = $this->actingAs($userBs)->get('/dashboard/fungsi/business-support/rekap-pelapor/pdf');
        $resPdfBs->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resPdfBs->headers->get('content-type', ''));

        $userOp = User::where('username', 'admin_op')->first();
        $resPdfOp = $this->actingAs($userOp)->get('/dashboard/fungsi/operation/rekap-pelapor/pdf');
        $resPdfOp->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $resPdfOp->headers->get('content-type', ''));

        // Admin Operation dilarang export PDF fungsi lain
        $this->actingAs($userOp)->get('/dashboard/fungsi/business-support/rekap-pelapor/pdf')->assertStatus(403);
    }

    /**
     * 11. Dashboard dan fitur lain yang tidak terkait tidak terpengaruh (regresi check).
     */
    public function test_unrelated_features_remain_unaffected(): void
    {
        $superadmin = User::where('username', 'admin')->first();

        $routes = [
            '/dashboard',
            '/dashboard/fungsi/Operation',
            '/dashboard/fungsi/Maintenance',
            '/dashboard/fungsi/HSSE',
            '/dashboard/fungsi/Business%20Support',
            '/findings',
            '/master/manpower',
            '/master/personil',
            '/master/function-mapping',
        ];

        foreach ($routes as $route) {
            $res = $this->actingAs($superadmin)->get($route);
            $res->assertStatus(200);
        }
    }

    /**
     * 12. Query Rekap Pelapor sepenuhnya kompatibel dengan ONLY_FULL_GROUP_BY di MySQL/MariaDB.
     */
    public function test_strict_only_full_group_by_compatibility(): void
    {
        // Pastikan ONLY_FULL_GROUP_BY aktif pada session database
        DB::statement("SET SESSION sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        $service = app(DashboardChartService::class);

        // Uji untuk setiap fungsi utama
        foreach (MasterFunctionMapping::DASHBOARD_FUNGSI_LIST as $fungsi) {
            $data = $service->getFunctionReporterRecap($fungsi);
            $this->assertIsArray($data);
            $this->assertEquals($fungsi, $data['fungsi']);
            $this->assertArrayHasKey('monthly_totals', $data);
            $this->assertArrayHasKey('rekap', $data);
            $this->assertEquals(array_sum($data['monthly_totals']), $data['total_pelaporan']);
        }

        // Uji getBusinessSupportReporterRecap() dengan berbagai variasi filter
        $filterVariations = [
            ['periode' => '', 'bulan' => 0, 'tahun' => 0, 'search' => ''],
            ['periode' => '1_day', 'bulan' => 0, 'tahun' => 0, 'search' => ''],
            ['periode' => '1_month', 'bulan' => 0, 'tahun' => 0, 'search' => ''],
            ['periode' => 'per_bulan', 'bulan' => 2, 'tahun' => 2026, 'search' => ''],
            ['periode' => '', 'bulan' => 0, 'tahun' => 0, 'search' => 'a'],
        ];

        foreach ($filterVariations as $filter) {
            $result = $service->getBusinessSupportReporterRecap(
                $filter['periode'],
                $filter['bulan'],
                $filter['tahun'],
                $filter['search']
            );
            $this->assertIsArray($result);
            $this->assertArrayHasKey('rekap', $result);
            $this->assertArrayHasKey('monthly_totals', $result);
        }
    }
}

