<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Admin;

class AuthTest extends TestCase
{
    /**
     * Uji tamu (guest) dialihkan ke halaman login saat membuka halaman utama.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    /**
     * Uji validasi input form login.
     */
    public function test_login_validation_errors(): void
    {
        $responseJson = $this->postJson('/login', [
            'email' => 'invalid-email',
            'password' => '123',
        ]);
        $responseJson->assertStatus(422);
        $responseJson->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Uji login berhasil dengan kredensial yang valid.
     */
    public function test_login_success(): void
    {
        $response = $this->postJson('/login', [
            'email' => 'admin@gmail.com',
            'password' => 'admin123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertAuthenticated();
    }

    /**
     * Uji otorisasi role: Admin BISA mengakses Master Data.
     */
    public function test_role_authorization_admin_can_access_master_data(): void
    {
        $adminRow = DB::selectOne("SELECT * FROM admin WHERE email = ?", ['admin@gmail.com']);
        $this->assertNotNull($adminRow);
        
        $admin = new Admin((array) $adminRow);

        $response = $this->actingAs($admin)->get('/master/aroma');
        $response->assertStatus(200);
    }

    /**
     * Uji otorisasi role: Kasir TIDAK BISA mengakses Master Data (403 Forbidden).
     */
    public function test_role_authorization_kasir_cannot_access_master_data(): void
    {
        $kasirRow = DB::selectOne("SELECT * FROM admin WHERE email = ?", ['kasir@gmail.com']);
        $this->assertNotNull($kasirRow);
        
        $kasir = new Admin((array) $kasirRow);

        $response = $this->actingAs($kasir)->get('/master/aroma');
        $response->assertStatus(403);
    }
}
