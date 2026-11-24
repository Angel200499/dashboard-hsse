<?php

namespace Tests\Feature;

use App\Models\SipekaFinding;
use App\Models\User;
use Tests\TestCase;

class DropdownTahunSipekaTest extends TestCase
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

    public function test_get_tahun_list_returns_valid_years_with_plus_one_descending(): void
    {
        $list = SipekaFinding::getTahunList();

        $this->assertIsArray($list);
        $this->assertNotEmpty($list);

        $maxYear = SipekaFinding::maxTahun();
        if ($maxYear !== null) {
            // First item must be max data year + 1
            $this->assertSame($maxYear + 1, $list[0]);
            // Max data year must be in the list
            $this->assertContains($maxYear, $list);
        }

        // Must be sorted descending
        $sorted = $list;
        rsort($sorted);
        $this->assertSame($sorted, $list);

        // Must not contain hardcoded old years that have no data
        $this->assertNotContains(2020, $list);
        $this->assertNotContains(2021, $list);
        $this->assertNotContains(2022, $list);
        $this->assertNotContains(2023, $list);
        $this->assertNotContains(2024, $list);
    }

    public function test_dashboard_global_html_and_json_use_dynamic_tahun_list(): void
    {
        $hsseUser = $this->getOrCreateUser('test_hsse_user', 'Admin HSSE', 'HSSE');
        $tahunList = SipekaFinding::getTahunList();

        // 1. Test HTML view
        $response = $this->actingAs($hsseUser)->get(route('dashboard'));
        $response->assertStatus(200);

        foreach ($tahunList as $yr) {
            $response->assertSee("<option value=\"{$yr}\"", false);
        }

        // Years from 2020-2024 that have no data should not appear in options
        $response->assertDontSee('<option value="2020"', false);
        $response->assertDontSee('<option value="2021"', false);
        $response->assertDontSee('<option value="2022"', false);
        $response->assertDontSee('<option value="2023"', false);
        $response->assertDontSee('<option value="2024"', false);

        // 2. Test AJAX / JSON response
        $ajaxResponse = $this->actingAs($hsseUser)->getJson(route('dashboard'));
        $ajaxResponse->assertStatus(200);
        $json = $ajaxResponse->json();

        $this->assertArrayHasKey('tahunList', $json);
        $this->assertSame($tahunList, $json['tahunList']);
    }

    public function test_dashboard_fungsi_html_and_json_use_dynamic_tahun_list(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');
        $tahunList = SipekaFinding::getTahunList();

        // 1. Test HTML view Tab Fungsi
        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi', [
            'nama_fungsi' => 'Operation',
        ]));
        $response->assertStatus(200);

        foreach ($tahunList as $yr) {
            $response->assertSee("<option value=\"{$yr}\"", false);
        }

        $response->assertDontSee('<option value="2020"', false);
        $response->assertDontSee('<option value="2021"', false);
        $response->assertDontSee('<option value="2022"', false);
        $response->assertDontSee('<option value="2023"', false);
        $response->assertDontSee('<option value="2024"', false);

        // 2. Test HTML view Tab Global
        $responseGlobal = $this->actingAs($adminOp)->get(route('dashboard.fungsi', [
            'nama_fungsi' => 'Operation',
            'tab'         => 'global',
        ]));
        $responseGlobal->assertStatus(200);

        foreach ($tahunList as $yr) {
            $responseGlobal->assertSee("<option value=\"{$yr}\"", false);
        }

        $responseGlobal->assertDontSee('<option value="2020"', false);

        // 3. Test AJAX / JSON response
        $ajaxResponse = $this->actingAs($adminOp)->getJson(route('dashboard.fungsi.data', [
            'nama_fungsi' => 'Operation',
        ]));
        $ajaxResponse->assertStatus(200);
        $json = $ajaxResponse->json();

        $this->assertArrayHasKey('tahunList', $json);
        $this->assertSame($tahunList, $json['tahunList']);
    }

    public function test_penyeragaman_filter_utama_tahun_dan_ketiadaan_filter_bulan_utama(): void
    {
        $hsseUser = $this->getOrCreateUser('test_hsse_user', 'Admin HSSE', 'HSSE');
        $adminOp  = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        // 1. Dashboard Global mandiri
        $responseGlobal = $this->actingAs($hsseUser)->get(route('dashboard'));
        $responseGlobal->assertStatus(200);
        // Memiliki filter utama Tahun
        $responseGlobal->assertSee('id="filter-year"', false);
        $responseGlobal->assertSee('name="year"', false);
        // TIDAK memiliki filter utama Bulan
        $responseGlobal->assertDontSee('id="filter-month"', false);
        // Mempertahankan filter mandiri
        $responseGlobal->assertSee('id="rr-year-filter"', false);
        $responseGlobal->assertSee('id="rr-month-filter"', false);
        $responseGlobal->assertSee('id="peka-year-filter"', false);
        $responseGlobal->assertSee('id="ko-year-filter"', false);
        $responseGlobal->assertSee('id="ko-month-filter"', false);

        // 2. Dashboard Fungsi Tab Fungsi
        $responseFungsi = $this->actingAs($adminOp)->get(route('dashboard.fungsi', ['nama_fungsi' => 'Operation']));
        $responseFungsi->assertStatus(200);
        // Memiliki filter utama Tahun
        $responseFungsi->assertSee('id="filter-year"', false);
        $responseFungsi->assertSee('name="year"', false);
        // TIDAK memiliki filter utama Bulan
        $responseFungsi->assertDontSee('id="filter-month"', false);
        // Mempertahankan filter mandiri
        $responseFungsi->assertSee('id="rr-year-filter"', false);
        $responseFungsi->assertSee('id="rr-month-filter"', false);
        $responseFungsi->assertSee('id="ko-year-filter"', false);
        $responseFungsi->assertSee('id="ko-month-filter"', false);

        // 3. Dashboard Fungsi Tab Global
        $responseTabGlobal = $this->actingAs($adminOp)->get(route('dashboard.fungsi', [
            'nama_fungsi' => 'Operation',
            'tab'         => 'global',
        ]));
        $responseTabGlobal->assertStatus(200);
        // Memiliki filter utama Tahun
        $responseTabGlobal->assertSee('id="g-filter-year"', false);
        $responseTabGlobal->assertSee('name="g_year"', false);
        // TIDAK memiliki filter utama Bulan
        $responseTabGlobal->assertDontSee('id="g-filter-month"', false);
        $responseTabGlobal->assertDontSee('name="g_month"', false);
        // Mempertahankan filter mandiri
        $responseTabGlobal->assertSee('id="g-rr-year-filter"', false);
        $responseTabGlobal->assertSee('id="g-rr-month-filter"', false);
        $responseTabGlobal->assertSee('id="g-peka-year-filter"', false);
        $responseTabGlobal->assertSee('id="g-ko-year-filter"', false);
        $responseTabGlobal->assertSee('id="g-ko-month-filter"', false);

        // 4. Perubahan filter utama Tahun di Dashboard Global via AJAX memperbarui KPI
        $latestYear = SipekaFinding::maxTahun() ?? 2026;
        $ajaxYear = $this->actingAs($hsseUser)->getJson(route('dashboard', ['year' => $latestYear]));
        $ajaxYear->assertStatus(200);
        $this->assertSame($latestYear, $ajaxYear->json('selectedYear'));
        $this->assertGreaterThan(0, $ajaxYear->json('kpi.total'));

        // Tahun tanpa data SIPEKA (misal tahun data + 1) menghasilkan KPI 0
        $nextYear = $latestYear + 1;
        $ajaxEmptyYear = $this->actingAs($hsseUser)->getJson(route('dashboard', ['year' => $nextYear]));
        $ajaxEmptyYear->assertStatus(200);
        $this->assertSame($nextYear, $ajaxEmptyYear->json('selectedYear'));
        $this->assertSame(0, $ajaxEmptyYear->json('kpi.total'));
    }
}
