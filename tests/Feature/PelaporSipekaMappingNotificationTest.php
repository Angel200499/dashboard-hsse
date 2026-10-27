<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class PelaporSipekaMappingNotificationTest extends TestCase
{
    protected function getAdminHsseUser(): User
    {
        $user = User::where('role', 'Admin HSSE')->first();
        if (!$user) {
            $user = User::factory()->create([
                'role' => 'Admin HSSE',
            ]);
        }
        return $user;
    }

    /**
     * Test pesan sukses hanya muncul satu kali (tidak duplikat) di halaman Mapping Pelapor SIPEKA.
     */
    public function test_success_message_is_rendered_exactly_once(): void
    {
        $admin = $this->getAdminHsseUser();
        $message = 'Mapping "Fano Wewengkang" → RIFIANO V WEWENGKANG berhasil disimpan.';

        $response = $this->actingAs($admin)
            ->withSession(['success' => $message])
            ->get(route('master.pelapor-mapping.index'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertEquals(
            1,
            substr_count($content, e($message)),
            "Pesan sukses harus dirender tepat satu kali pada halaman Mapping Pelapor SIPEKA."
        );
    }

    /**
     * Test pesan error hanya muncul satu kali di halaman Mapping Pelapor SIPEKA.
     */
    public function test_error_message_is_rendered_exactly_once(): void
    {
        $admin = $this->getAdminHsseUser();
        $message = 'Nama pelapor tersebut tidak ditemukan pada data SIPEKA.';

        $response = $this->actingAs($admin)
            ->withSession(['error' => $message])
            ->get(route('master.pelapor-mapping.index'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertEquals(
            1,
            substr_count($content, $message),
            "Pesan error harus dirender tepat satu kali pada halaman Mapping Pelapor SIPEKA."
        );
    }

    /**
     * Test pesan warning hanya muncul satu kali di halaman Mapping Pelapor SIPEKA melalui notifikasi global.
     */
    public function test_warning_message_is_rendered_exactly_once(): void
    {
        $admin = $this->getAdminHsseUser();
        $message = 'Perhatian: Ada data yang membutuhkan konfirmasi manual.';

        $response = $this->actingAs($admin)
            ->withSession(['warning' => $message])
            ->get(route('master.pelapor-mapping.index'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertEquals(
            1,
            substr_count($content, $message),
            "Pesan warning harus dirender tepat satu kali pada halaman Mapping Pelapor SIPEKA."
        );
    }

    /**
     * Test pesan validasi $errors hanya muncul satu kali di halaman Mapping Pelapor SIPEKA.
     */
    public function test_validation_errors_are_rendered_exactly_once(): void
    {
        $admin = $this->getAdminHsseUser();
        $message = 'Nama SIPEKA wajib diisi.';

        $viewErrorBag = new \Illuminate\Support\ViewErrorBag();
        $viewErrorBag->put('default', new \Illuminate\Support\MessageBag(['nama_sipeka' => [$message]]));

        $response = $this->actingAs($admin)
            ->withSession([
                'errors' => $viewErrorBag,
            ])
            ->get(route('master.pelapor-mapping.index'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertEquals(
            1,
            substr_count($content, e($message)),
            "Pesan validasi error harus dirender tepat satu kali pada halaman Mapping Pelapor SIPEKA."
        );
    }
}
