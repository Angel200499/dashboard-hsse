<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndNavigationTest extends TestCase
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
     * 1. Login sebagai Admin HSSE dan Manager HSSE harus diarahkan ke Dashboard Global.
     */
    public function test_admin_hsse_and_manager_hsse_login_redirect_to_global_dashboard(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', 'HSSE');
        $mgrHsse   = $this->getOrCreateUser('test_mgr_hsse', 'Manager HSSE', 'HSSE');

        // Test Admin HSSE
        $resAdmin = $this->post(route('login.post'), [
            'username' => 'test_admin_hsse',
            'password' => 'password123',
        ]);
        $resAdmin->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($adminHsse);

        // Logout
        $this->post(route('logout'));
        $this->assertGuest();

        // Test Manager HSSE
        $resMgr = $this->post(route('login.post'), [
            'username' => 'test_mgr_hsse',
            'password' => 'password123',
        ]);
        $resMgr->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($mgrHsse);
    }

    /**
     * 2. Login sebagai Admin Function dan Manager Function dari fungsi berbeda diarahkan ke dashboard fungsi masing-masing.
     */
    public function test_admin_function_and_manager_function_login_redirect_to_their_fungsi_dashboard(): void
    {
        $adminOp  = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');
        $mgrMt    = $this->getOrCreateUser('test_mgr_mt', 'Manager Function', 'Maintenance');
        $adminBs  = $this->getOrCreateUser('test_admin_bs', 'Admin Function', 'Business Support');

        // Admin Operation
        $resOp = $this->post(route('login.post'), [
            'username' => 'test_admin_op',
            'password' => 'password123',
        ]);
        $resOp->assertRedirect(route('dashboard.fungsi', 'Operation'));
        $this->post(route('logout'));

        // Manager Maintenance
        $resMt = $this->post(route('login.post'), [
            'username' => 'test_mgr_mt',
            'password' => 'password123',
        ]);
        $resMt->assertRedirect(route('dashboard.fungsi', 'Maintenance'));
        $this->post(route('logout'));

        // Admin Business Support
        $resBs = $this->post(route('login.post'), [
            'username' => 'test_admin_bs',
            'password' => 'password123',
        ]);
        $resBs->assertRedirect(route('dashboard.fungsi', 'Business Support'));
    }

    /**
     * 3. Uji skenario URL intended lama dari fungsi lain: harus dibersihkan dan diabaikan saat login.
     */
    public function test_stale_intended_url_is_cleared_and_ignored_on_login(): void
    {
        $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        // Simulasikan ada url.intended lama menuju fungsi Maintenance
        $response = $this->withSession(['url.intended' => route('dashboard.fungsi', 'Maintenance')])
            ->post(route('login.post'), [
                'username' => 'test_admin_op',
                'password' => 'password123',
            ]);

        // Harus tetap diarahkan ke Operation sesuai role akun, BUKAN ke Maintenance intended!
        $response->assertRedirect(route('dashboard.fungsi', 'Operation'));
        $response->assertSessionMissing('url.intended');
    }

    /**
     * 4. Pastikan pengguna fungsi tidak bisa mengakses dashboard fungsi lain (403 Forbidden).
     */
    public function test_function_users_cannot_access_other_functions_dashboard(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        // Operation mengakses Operation -> Boleh (200)
        $this->actingAs($adminOp)->get(route('dashboard.fungsi', 'Operation'))->assertStatus(200);

        // Operation mengakses Maintenance -> Ditolak (403)
        $this->actingAs($adminOp)->get(route('dashboard.fungsi', 'Maintenance'))->assertStatus(403);

        // Operation mengakses Business Support -> Ditolak (403)
        $this->actingAs($adminOp)->get(route('dashboard.fungsi', 'Business Support'))->assertStatus(403);
    }

    /**
     * 5. Logout mengakhiri sesi, dan Landing Page setelah logout menampilkan tombol Login (bukan Dashboard).
     */
    public function test_logout_invalidates_session_and_landing_page_shows_login_button(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', 'HSSE');

        $this->actingAs($adminHsse);
        $resLogout = $this->post(route('logout'));
        $resLogout->assertRedirect('/');
        $this->assertGuest();

        // Akses landing page setelah logout
        $resLanding = $this->get('/');
        $resLanding->assertStatus(200);

        // Harus menampilkan tombol Login
        $resLanding->assertSee('Login');
        $resLanding->assertSee(route('login'));

        // Tidak boleh menampilkan tombol Dashboard di nav
        $resLanding->assertDontSee('class="nav-btn">Dashboard', false);
    }

    /**
     * 6. Coba akses /dashboard setelah logout: harus diredirect ke /login.
     */
    public function test_accessing_dashboard_after_logout_redirects_to_login(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', 'HSSE');

        $this->actingAs($adminHsse)->post(route('logout'));
        $this->assertGuest();

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * 7. Route /login dengan middleware guest: pengguna yang sudah login diarahkan ke dashboard sesuai role.
     */
    public function test_authenticated_user_accessing_login_page_is_redirected_to_their_dashboard(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', 'HSSE');
        $adminOp   = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        // Admin HSSE membuka /login -> redirect ke Dashboard Global
        $this->actingAs($adminHsse)->get(route('login'))->assertRedirect(route('dashboard'));

        // Admin Operation membuka /login -> redirect ke Dashboard Operation
        $this->actingAs($adminOp)->get(route('login'))->assertRedirect(route('dashboard.fungsi', 'Operation'));
    }

    /**
     * 8. Header anti-cache terpasang pada landing page, login, dan dashboard.
     */
    public function test_anti_cache_headers_are_present_on_landing_page_login_and_dashboard(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', 'HSSE');
        $expectedCacheControl = 'max-age=0, must-revalidate, no-cache, no-store, private';

        // Landing page
        $resLanding = $this->get('/');
        $resLanding->assertHeader('Cache-Control', $expectedCacheControl);
        $resLanding->assertHeader('Pragma', 'no-cache');

        // Login page (as guest)
        $resLogin = $this->get(route('login'));
        $resLogin->assertHeader('Cache-Control', $expectedCacheControl);
        $resLogin->assertHeader('Pragma', 'no-cache');

        // Dashboard (authenticated)
        $resDashboard = $this->actingAs($adminHsse)->get(route('dashboard'));
        $resDashboard->assertHeader('Cache-Control', $expectedCacheControl);
        $resDashboard->assertHeader('Pragma', 'no-cache');
    }

    /**
     * 9. Landing page menyertakan skrip deteksi BFCache (pageshow persisted reload).
     */
    public function test_landing_page_contains_bfcache_detection_script(): void
    {
        $res = $this->get('/');
        $res->assertStatus(200);
        $res->assertSee('window.addEventListener(\'pageshow\'', false);
        $res->assertSee('event.persisted', false);
        $res->assertSee('window.location.reload()', false);
    }
}
