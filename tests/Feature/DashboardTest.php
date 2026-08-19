<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\Admin;

class DashboardTest extends TestCase
{
    /**
     * Tamu (guest) dialihkan ke halaman login saat membuka dashboard.
     */
    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    /**
     * Admin dapat mengakses dashboard dan melihat data ringkasan.
     */
    public function test_admin_can_access_dashboard(): void
    {
        $adminRow = DB::selectOne("SELECT * FROM admin WHERE email = ?", ['admin@gmail.com']);
        $this->assertNotNull($adminRow);
        $admin = new Admin((array) $adminRow);

        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200);
        $response->assertViewHas('dashboardData');
    }

    /**
     * Kasir dapat mengakses dashboard dan melihat data ringkasan.
     */
    public function test_kasir_can_access_dashboard(): void
    {
        $kasirRow = DB::selectOne("SELECT * FROM admin WHERE email = ?", ['kasir@gmail.com']);
        $this->assertNotNull($kasirRow);
        $kasir = new Admin((array) $kasirRow);

        $response = $this->actingAs($kasir)->get('/');
        $response->assertStatus(200);
        $response->assertViewHas('dashboardData');
    }
}
