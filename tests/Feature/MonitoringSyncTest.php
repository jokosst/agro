<?php

namespace Tests\Feature;

use App\Models\Kebun;
use App\Models\KebunBlok;
use App\Models\LaporanMasalah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringSyncTest extends TestCase
{
    use RefreshDatabase;

    private Kebun $kebun;

    private KebunBlok $blok;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kebun = Kebun::create([
            'nama' => 'Kebun Test',
            'lokasi_text' => 'Sambas',
            'latitude' => 1.3,
            'longitude' => 109.3,
            'radius_meter' => 50,
            'luas_lahan' => '1 Ha',
            'status' => 'aktif',
        ]);

        $this->blok = KebunBlok::create([
            'kebun_id' => $this->kebun->id,
            'kode_blok' => 'Blok T1',
            'nama_blok' => 'Blok Varietas Unggul',
            'status_kondisi' => 'normal',
            'jumlah_tanaman' => 200,
            'jumlah_masalah' => 0,
            'jumlah_hama' => 0,
        ]);

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_laporan_masalah_creation_automatically_syncs_kebun_blok(): void
    {
        $this->assertEquals('normal', $this->blok->status_kondisi);
        $this->assertEquals(0, $this->blok->jumlah_hama);
        $this->assertEquals(0, $this->blok->jumlah_masalah);

        // Buat laporan hama
        $laporanHama = LaporanMasalah::create([
            'user_id' => $this->admin->id,
            'kebun_id' => $this->kebun->id,
            'blok_id' => $this->blok->id,
            'jenis_masalah' => 'Hama',
            'jumlah_tanaman' => 12,
            'status' => 'menunggu',
        ]);

        $this->blok->refresh();
        $this->assertEquals(12, $this->blok->jumlah_hama);
        $this->assertEquals(0, $this->blok->jumlah_masalah);
        $this->assertEquals('masalah', $this->blok->status_kondisi);

        // Tambah laporan penyakit (non-hama)
        $laporanPenyakit = LaporanMasalah::create([
            'user_id' => $this->admin->id,
            'kebun_id' => $this->kebun->id,
            'blok_id' => $this->blok->id,
            'jenis_masalah' => 'Penyakit',
            'jumlah_tanaman' => 5,
            'status' => 'menunggu',
        ]);

        $this->blok->refresh();
        $this->assertEquals(12, $this->blok->jumlah_hama);
        $this->assertEquals(5, $this->blok->jumlah_masalah);
        $this->assertEquals('masalah', $this->blok->status_kondisi);
    }

    public function test_updating_status_laporan_adjusts_blok_status_kondisi(): void
    {
        $laporan = LaporanMasalah::create([
            'user_id' => $this->admin->id,
            'kebun_id' => $this->kebun->id,
            'blok_id' => $this->blok->id,
            'jenis_masalah' => 'Hama',
            'jumlah_tanaman' => 8,
            'status' => 'menunggu',
        ]);

        $this->blok->refresh();
        $this->assertEquals('masalah', $this->blok->status_kondisi);

        // Ubah status ke ditangani
        $laporan->update(['status' => 'ditangani']);
        $this->blok->refresh();
        $this->assertEquals('perhatian', $this->blok->status_kondisi);
        $this->assertEquals(8, $this->blok->jumlah_hama);

        // Ubah status ke selesai
        $laporan->update(['status' => 'selesai']);
        $this->blok->refresh();
        $this->assertEquals('normal', $this->blok->status_kondisi);
        $this->assertEquals(0, $this->blok->jumlah_hama);
        $this->assertEquals(0, $this->blok->jumlah_masalah);
    }

    public function test_deleting_laporan_masalah_recalculates_blok_status(): void
    {
        $laporan = LaporanMasalah::create([
            'user_id' => $this->admin->id,
            'kebun_id' => $this->kebun->id,
            'blok_id' => $this->blok->id,
            'jenis_masalah' => 'Hama',
            'jumlah_tanaman' => 10,
            'status' => 'menunggu',
        ]);

        $this->blok->refresh();
        $this->assertEquals(10, $this->blok->jumlah_hama);
        $this->assertEquals('masalah', $this->blok->status_kondisi);

        $laporan->delete();

        $this->blok->refresh();
        $this->assertEquals(0, $this->blok->jumlah_hama);
        $this->assertEquals('normal', $this->blok->status_kondisi);
    }

    public function test_master_lahan_store_and_update_without_manual_pest_inputs(): void
    {
        // 1. Simpan blok baru tanpa input status_kondisi, jumlah_hama, jumlah_masalah
        $response = $this->actingAs($this->admin)->post(route('admin.master.blok.store'), [
            'kebun_id' => $this->kebun->id,
            'kode_blok' => 'Blok T2',
            'nama_blok' => 'Cabai Rawit Merah',
            'jumlah_tanaman' => 150,
            'latitude' => 1.3005,
            'longitude' => 109.3005,
            'keterangan' => 'Lahan baru',
        ]);

        $response->assertSessionHas('success');

        $newBlok = KebunBlok::where('kode_blok', 'Blok T2')->first();
        $this->assertNotNull($newBlok);
        $this->assertEquals('normal', $newBlok->status_kondisi);
        $this->assertEquals(0, $newBlok->jumlah_hama);
        $this->assertEquals(0, $newBlok->jumlah_masalah);

        // 2. Update blok
        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.master.blok.update', $newBlok->id), [
            'kode_blok' => 'Blok T2 Updated',
            'nama_blok' => 'Cabai Rawit Unggul Baru',
            'jumlah_tanaman' => 180,
            'latitude' => 1.3006,
            'longitude' => 109.3006,
            'keterangan' => 'Update keterangan',
        ]);

        $responseUpdate->assertSessionHas('success');
        $newBlok->refresh();
        $this->assertEquals('Blok T2 Updated', $newBlok->kode_blok);
        $this->assertEquals(180, $newBlok->jumlah_tanaman);
        $this->assertEquals('normal', $newBlok->status_kondisi);
    }
}
