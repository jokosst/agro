<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_pekerja_cannot_access_master_data(): void
    {
        $pekerja = User::factory()->create(['role' => 'pekerja']);

        $response = $this->actingAs($pekerja)->get(route('admin.master.pekerja'));
        $response->assertStatus(403);

        $response2 = $this->actingAs($pekerja)->get(route('admin.master.lahan'));
        $response2->assertStatus(403);
    }

    public function test_admin_can_access_master_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.master.pekerja'));
        $response->assertStatus(200);
    }

    public function test_pekerja_cannot_delete_or_update_absensi(): void
    {
        $pekerja = User::factory()->create(['role' => 'pekerja']);

        $response = $this->actingAs($pekerja)->delete(route('admin.absensi.destroy', 1));
        $response->assertStatus(403);

        $response2 = $this->actingAs($pekerja)->put(route('admin.absensi.update', 1));
        $response2->assertStatus(403);
    }

    public function test_pekerja_cannot_delete_or_update_laporan_masalah(): void
    {
        $pekerja = User::factory()->create(['role' => 'pekerja']);

        $response = $this->actingAs($pekerja)->delete(route('admin.laporan_masalah.destroy', 1));
        $response->assertStatus(403);

        $response2 = $this->actingAs($pekerja)->put(route('admin.laporan_masalah.update', 1));
        $response2->assertStatus(403);
    }

    public function test_pekerja_cannot_view_other_worker_detail(): void
    {
        $pekerja = User::factory()->create(['role' => 'pekerja']);
        $otherPekerja = User::factory()->create(['role' => 'pekerja']);

        $response = $this->actingAs($pekerja)->get(route('admin.detail_pekerja', $otherPekerja->id));
        $response->assertStatus(403);
    }

    public function test_pekerja_can_view_own_detail(): void
    {
        $pekerja = User::factory()->create(['role' => 'pekerja']);

        $response = $this->actingAs($pekerja)->get(route('admin.detail_pekerja', $pekerja->id));
        $response->assertStatus(200);
    }

    public function test_admin_can_store_pekerja_without_kebun_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.master.pekerja.store'), [
            'name' => 'Pekerja Baru',
            'username' => 'pekerjabaru',
            'email' => 'pekerjabaru@example.com',
            'phone' => '08123456789',
            'role' => 'pekerja',
            'password' => 'secret123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'username' => 'pekerjabaru',
            'email' => 'pekerjabaru@example.com',
        ]);
    }
}
