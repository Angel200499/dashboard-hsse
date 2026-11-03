<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IntegrasiDashboardGlobalDanFungsiTest extends TestCase
{
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
     * 1. Global HSSE dapat mengakses Dashboard Global.
     */
    public function test_01_global_hsse_can_access_dashboard_global(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', 'HSSE');
        $mgrHsse   = $this->getOrCreateUser('test_mgr_hsse', 'Manager HSSE', 'HSSE');

        $this->actingAs($adminHsse)->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Dashboard Global HSSE');

        $this->actingAs($mgrHsse)->get(route('dashboard'))
            ->assertStatus(200)
            ->assertSee('Dashboard Global HSSE');
    }

    /**
     * 2. Admin Operation diarahkan ke Dashboard Operation.
     */
    public function test_02_admin_operation_redirected_to_operation_dashboard(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        // Login redirect
        $this->post(route('login.post'), [
            'username' => 'test_admin_op',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.fungsi', 'Operation'));
        $this->post(route('logout'));

        // Direct URL /dashboard redirect
        $this->actingAs($adminOp)->get(route('dashboard'))
            ->assertRedirect(route('dashboard.fungsi', 'Operation'));

        // Akses dashboard Operation menampilkan data Operation
        $this->actingAs($adminOp)->get(route('dashboard.fungsi', 'Operation'))
            ->assertStatus(200)
            ->assertSee('Dashboard Fungsi: Operation');
    }

    /**
     * 3. Manager Operation diarahkan ke Dashboard Operation sesuai kebijakan akses.
     */
    public function test_03_manager_operation_redirected_to_operation_dashboard(): void
    {
        $mgrOp = $this->getOrCreateUser('test_mgr_operation', 'Manager Function', 'Operation');

        // Login redirect
        $this->post(route('login.post'), [
            'username' => 'test_mgr_operation',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.fungsi', 'Operation'));
        $this->post(route('logout'));

        // Direct URL /dashboard redirect
        $this->actingAs($mgrOp)->get(route('dashboard'))
            ->assertRedirect(route('dashboard.fungsi', 'Operation'));

        // Akses dashboard Operation menampilkan data Operation
        $this->actingAs($mgrOp)->get(route('dashboard.fungsi', 'Operation'))
            ->assertStatus(200)
            ->assertSee('Dashboard Fungsi: Operation');
    }

    /**
     * 4. Admin Maintenance diarahkan ke Dashboard Maintenance.
     */
    public function test_04_admin_maintenance_redirected_to_maintenance_dashboard(): void
    {
        $adminMt = $this->getOrCreateUser('test_admin_mt', 'Admin Function', 'Maintenance');

        // Login redirect
        $this->post(route('login.post'), [
            'username' => 'test_admin_mt',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.fungsi', 'Maintenance'));
        $this->post(route('logout'));

        // Direct URL /dashboard redirect
        $this->actingAs($adminMt)->get(route('dashboard'))
            ->assertRedirect(route('dashboard.fungsi', 'Maintenance'));

        // Akses dashboard Maintenance menampilkan data Maintenance
        $this->actingAs($adminMt)->get(route('dashboard.fungsi', 'Maintenance'))
            ->assertStatus(200)
            ->assertSee('Dashboard Fungsi: Maintenance');
    }

    /**
     * 5. Manager Maintenance diarahkan ke Dashboard Maintenance sesuai kebijakan akses.
     */
    public function test_05_manager_maintenance_redirected_to_maintenance_dashboard(): void
    {
        $mgrMt = $this->getOrCreateUser('test_mgr_mt', 'Manager Function', 'Maintenance');

        // Login redirect
        $this->post(route('login.post'), [
            'username' => 'test_mgr_mt',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.fungsi', 'Maintenance'));
        $this->post(route('logout'));

        // Direct URL /dashboard redirect
        $this->actingAs($mgrMt)->get(route('dashboard'))
            ->assertRedirect(route('dashboard.fungsi', 'Maintenance'));

        // Akses dashboard Maintenance menampilkan data Maintenance
        $this->actingAs($mgrMt)->get(route('dashboard.fungsi', 'Maintenance'))
            ->assertStatus(200)
            ->assertSee('Dashboard Fungsi: Maintenance');
    }

    /**
     * 6. Admin Business Support diarahkan ke Dashboard Business Support.
     */
    public function test_06_admin_business_support_redirected_to_business_support_dashboard(): void
    {
        $adminBs = $this->getOrCreateUser('test_admin_bs', 'Admin Function', 'Business Support');

        // Login redirect
        $this->post(route('login.post'), [
            'username' => 'test_admin_bs',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.fungsi', 'Business Support'));
        $this->post(route('logout'));

        // Direct URL /dashboard redirect
        $this->actingAs($adminBs)->get(route('dashboard'))
            ->assertRedirect(route('dashboard.fungsi', 'Business Support'));

        // Akses dashboard Business Support baik via format spasi maupun format slug kebab-case
        $this->actingAs($adminBs)->get(route('dashboard.fungsi', 'Business Support'))
            ->assertStatus(200)
            ->assertSee('Dashboard Fungsi: Business Support');

        $this->actingAs($adminBs)->get('/dashboard/fungsi/business-support')
            ->assertStatus(200)
            ->assertSee('Dashboard Fungsi: Business Support');
    }

    /**
     * 7. Manager Business Support diarahkan ke Dashboard Business Support sesuai kebijakan akses.
     */
    public function test_07_manager_business_support_redirected_to_business_support_dashboard(): void
    {
        $mgrBs = $this->getOrCreateUser('test_mgr_business_support', 'Manager Function', 'Business Support');

        // Login redirect
        $this->post(route('login.post'), [
            'username' => 'test_mgr_business_support',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.fungsi', 'Business Support'));
        $this->post(route('logout'));

        // Direct URL /dashboard redirect
        $this->actingAs($mgrBs)->get(route('dashboard'))
            ->assertRedirect(route('dashboard.fungsi', 'Business Support'));

        // Akses dashboard Business Support
        $this->actingAs($mgrBs)->get(route('dashboard.fungsi', 'Business Support'))
            ->assertStatus(200)
            ->assertSee('Dashboard Fungsi: Business Support');

        $this->actingAs($mgrBs)->get('/dashboard/fungsi/business-support')
            ->assertStatus(200)
            ->assertSee('Dashboard Fungsi: Business Support');
    }

    /**
     * 8. Role HSSE tetap mengikuti aturan akses yang berlaku.
     */
    public function test_08_hsse_roles_follow_access_policy(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', 'HSSE');
        $mgrHsse   = $this->getOrCreateUser('test_mgr_hsse', 'Manager HSSE', 'HSSE');

        // Admin HSSE dan Manager HSSE bisa membuka seluruh dashboard fungsi
        foreach (['Operation', 'Maintenance', 'HSSE', 'Business Support'] as $fn) {
            $this->actingAs($adminHsse)->get(route('dashboard.fungsi', $fn))
                ->assertStatus(200)
                ->assertSee("Dashboard Fungsi: {$fn}");

            $this->actingAs($mgrHsse)->get(route('dashboard.fungsi', $fn))
                ->assertStatus(200)
                ->assertSee("Dashboard Fungsi: {$fn}");
        }

        // Temuan GM khusus untuk Admin HSSE
        $this->actingAs($adminHsse)->get(route('dashboard.temuan-gm'))->assertStatus(200);
        $this->actingAs($mgrHsse)->get(route('dashboard.temuan-gm'))->assertStatus(403);
    }

    /**
     * 9. Trending Temuan global tidak muncul di Dashboard Fungsi Operation, Maintenance, dan Business Support.
     */
    public function test_09_trending_temuan_not_present_in_function_dashboards(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', 'HSSE');
        $adminOp   = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');
        $adminMt   = $this->getOrCreateUser('test_admin_mt', 'Admin Function', 'Maintenance');
        $adminBs   = $this->getOrCreateUser('test_admin_bs', 'Admin Function', 'Business Support');

        // Dashboard Operation
        $resOp = $this->actingAs($adminOp)->get(route('dashboard.fungsi', 'Operation'));
        $resOp->assertStatus(200);
        $resOp->assertDontSee('id="chartTrending"', false);
        $resOp->assertDontSee('trending-summary', false);

        // Dashboard Maintenance
        $resMt = $this->actingAs($adminMt)->get(route('dashboard.fungsi', 'Maintenance'));
        $resMt->assertStatus(200);
        $resMt->assertDontSee('id="chartTrending"', false);
        $resMt->assertDontSee('trending-summary', false);

        // Dashboard Business Support
        $resBs = $this->actingAs($adminBs)->get(route('dashboard.fungsi', 'Business Support'));
        $resBs->assertStatus(200);
        $resBs->assertDontSee('id="chartTrending"', false);
        $resBs->assertDontSee('trending-summary', false);

        // Dashboard Fungsi HSSE
        $resHsseFungsi = $this->actingAs($adminHsse)->get(route('dashboard.fungsi', 'HSSE'));
        $resHsseFungsi->assertStatus(200);
        $resHsseFungsi->assertDontSee('id="chartTrending"', false);
        $resHsseFungsi->assertDontSee('trending-summary', false);
    }

    /**
     * 10. Dashboard Global tetap menampilkan Trending Temuan sesuai desain yang dimaksudkan.
     */
    public function test_10_dashboard_global_functions_and_renders_trending_temuan(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', 'HSSE');

        $res = $this->actingAs($adminHsse)->get(route('dashboard'));
        $res->assertStatus(200);
        $res->assertSee('id="chartTrending"', false);
        $res->assertSee('trending-summary', false);
        $res->assertSee('Total Temuan');
        $res->assertSee('Status Open');
        $res->assertSee('Status Closed');
    }

    /**
     * 11. Pengguna tanpa hak akses tidak dapat membuka Dashboard Global melalui URL langsung.
     */
    public function test_11_unauthorized_user_cannot_open_dashboard_global_directly(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');
        $mgrOp   = $this->getOrCreateUser('test_mgr_operation', 'Manager Function', 'Operation');
        $adminMt = $this->getOrCreateUser('test_admin_mt', 'Admin Function', 'Maintenance');
        $mgrMt   = $this->getOrCreateUser('test_mgr_mt', 'Manager Function', 'Maintenance');
        $adminBs = $this->getOrCreateUser('test_admin_bs', 'Admin Function', 'Business Support');
        $mgrBs   = $this->getOrCreateUser('test_mgr_business_support', 'Manager Function', 'Business Support');

        // Function users dialihkan ke dashboard fungsi masing-masing dan TIDAK BISA melihat Dashboard Global
        $this->actingAs($adminOp)->get(route('dashboard'))->assertRedirect(route('dashboard.fungsi', 'Operation'));
        $this->actingAs($mgrOp)->get(route('dashboard'))->assertRedirect(route('dashboard.fungsi', 'Operation'));
        $this->actingAs($adminMt)->get(route('dashboard'))->assertRedirect(route('dashboard.fungsi', 'Maintenance'));
        $this->actingAs($mgrMt)->get(route('dashboard'))->assertRedirect(route('dashboard.fungsi', 'Maintenance'));
        $this->actingAs($adminBs)->get(route('dashboard'))->assertRedirect(route('dashboard.fungsi', 'Business Support'));
        $this->actingAs($mgrBs)->get(route('dashboard'))->assertRedirect(route('dashboard.fungsi', 'Business Support'));

        // Guest tanpa login diarahkan ke login
        $this->post(route('logout'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    /**
     * 12. Pengguna tidak dapat mengakses data fungsi lain dengan memanipulasi parameter URL.
     */
    public function test_12_user_cannot_access_other_function_data_via_url_manipulation(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        // Admin Operation mencoba buka Maintenance -> 403
        $this->actingAs($adminOp)->get('/dashboard/fungsi/Maintenance')->assertStatus(403);
        $this->actingAs($adminOp)->get('/dashboard/fungsi/maintenance')->assertStatus(403);

        // Admin Operation mencoba buka Business Support -> 403
        $this->actingAs($adminOp)->get('/dashboard/fungsi/Business Support')->assertStatus(403);
        $this->actingAs($adminOp)->get('/dashboard/fungsi/business-support')->assertStatus(403);

        // Admin Operation mencoba buka HSSE -> 403
        $this->actingAs($adminOp)->get('/dashboard/fungsi/HSSE')->assertStatus(403);
        $this->actingAs($adminOp)->get('/dashboard/fungsi/hsse')->assertStatus(403);

        // Admin Maintenance mencoba buka Operation -> 403
        $adminMt = $this->getOrCreateUser('test_admin_mt', 'Admin Function', 'Maintenance');
        $this->actingAs($adminMt)->get('/dashboard/fungsi/Operation')->assertStatus(403);

        // Admin Business Support mencoba buka Operation -> 403
        $adminBs = $this->getOrCreateUser('test_admin_bs', 'Admin Function', 'Business Support');
        $this->actingAs($adminBs)->get('/dashboard/fungsi/Operation')->assertStatus(403);
    }

    /**
     * 13. Login, logout, dan navigasi kembali tetap berfungsi.
     */
    public function test_13_login_logout_and_back_navigation(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        // Login
        $this->post(route('login.post'), [
            'username' => 'test_admin_op',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard.fungsi', 'Operation'));
        $this->assertAuthenticatedAs($adminOp);

        // Logout
        $this->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();

        // Navigasi kembali ke dashboard setelah logout -> redirect ke login
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('dashboard.fungsi', 'Operation'))->assertRedirect(route('login'));
    }

    /**
     * 14. Pagination modal Belum Melapor tetap berfungsi setelah perubahan.
     */
    public function test_14_unreported_modal_pagination_and_data_scope(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', 'HSSE');

        // Admin Operation bisa mengambil data modal untuk fungsinya sendiri
        $resOpSelf = $this->actingAs($adminOp)->get(route('unreported-personil.data', [
            'fungsi'   => 'Operation',
            'page'     => 1,
            'per_page' => 10,
        ]));
        $resOpSelf->assertStatus(200);
        $resOpSelf->assertJsonStructure([
            'success',
            'data',
            'pagination' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        // Admin Operation TIDAK BISA mengambil data modal fungsi Maintenance (403)
        $resOpOther = $this->actingAs($adminOp)->get(route('unreported-personil.data', [
            'fungsi'   => 'Maintenance',
            'page'     => 1,
            'per_page' => 10,
        ]));
        $resOpOther->assertStatus(403);

        // Admin HSSE bisa mengambil data modal fungsi apapun
        $resHsse = $this->actingAs($adminHsse)->get(route('unreported-personil.data', [
            'fungsi'   => 'Operation',
            'page'     => 1,
            'per_page' => 10,
        ]));
        $resHsse->assertStatus(200);
    }
}
