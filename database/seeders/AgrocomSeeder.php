<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Kebun;
use App\Models\KebunBlok;
use App\Models\LaporanHarian;
use App\Models\LaporanMasalah;
use App\Models\PekerjaTugas;
use App\Models\PemeriksaanTanaman;
use App\Models\TugasHarian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgrocomSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Kebun
        $kebun = Kebun::create([
            'nama' => 'Kebun Cabai Agrocom',
            'lokasi_text' => 'Sambas, Kalimantan Barat',
            'latitude' => -0.1234000,
            'longitude' => 109.3456000,
            'radius_meter' => 50,
            'luas_lahan' => '2 Hektar',
            'status' => 'aktif',
        ]);

        // 2. Create Kebun Bloks
        $blokA = KebunBlok::create([
            'kebun_id' => $kebun->id,
            'kode_blok' => 'Blok A',
            'nama_blok' => 'Blok A - Cabai Rawit Merah',
            'status_kondisi' => 'perhatian',
            'jumlah_tanaman' => 150,
            'jumlah_masalah' => 2,
            'jumlah_hama' => 5,
            'latitude' => -0.1233500,
            'longitude' => 109.3455500,
            'keterangan' => 'Ditemukan bercak daun dan 5 tanaman terserang kutu kebul',
        ]);

        $blokB = KebunBlok::create([
            'kebun_id' => $kebun->id,
            'kode_blok' => 'Blok B',
            'nama_blok' => 'Blok B - Cabai Keriting',
            'status_kondisi' => 'normal',
            'jumlah_tanaman' => 200,
            'jumlah_masalah' => 0,
            'jumlah_hama' => 0,
            'latitude' => -0.1234500,
            'longitude' => 109.3456800,
            'keterangan' => 'Kondisi tanaman subur, tidak ada masalah',
        ]);

        // 3. Create Users
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Pemilik Kebun (Admin)',
                'email' => 'admin@agrocom.id',
                'phone' => '081234567890',
                'role' => 'admin',
                'kebun_id' => $kebun->id,
                'password' => Hash::make('123456'),
            ]
        );

        $andi = User::updateOrCreate(
            ['username' => 'andi'],
            [
                'name' => 'Andi',
                'email' => 'andi@agrocom.id',
                'phone' => '081298765432',
                'role' => 'pekerja',
                'kebun_id' => $kebun->id,
                'password' => Hash::make('123456'),
            ]
        );

        // 4. Create Master Tugas Harian
        $tugasList = [
            'Cek seluruh tanaman',
            'Bersihkan gulma',
            'Periksa hama/penyakit',
            'Periksa ajir dan tali',
            'Perempelan bila diperlukan',
            'Pemupukan/penyemprotan',
            'Bersihkan area kebun',
            'Dokumentasi kondisi kebun',
        ];

        foreach ($tugasList as $index => $judul) {
            $tugas = TugasHarian::create([
                'kebun_id' => $kebun->id,
                'judul' => $judul,
                'urutan' => $index + 1,
                'is_active' => true,
            ]);

            // Seeder default status untuk Andi hari ini (sebagian selesai seperti di storyboard)
            PekerjaTugas::create([
                'user_id' => $andi->id,
                'tugas_harian_id' => $tugas->id,
                'tanggal' => Carbon::today(),
                'is_completed' => in_array($index, [0, 1]), // Tugas 1 dan 2 selesai
                'completed_at' => in_array($index, [0, 1]) ? Carbon::now()->subHours(2) : null,
            ]);
        }

        // 5. Absensi Hari Ini untuk Andi
        Absensi::create([
            'user_id' => $andi->id,
            'kebun_id' => $kebun->id,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '07:02:00',
            'lat_masuk' => -0.1234000,
            'long_masuk' => 109.3456000,
            'jarak_masuk_meter' => 12.5,
            'is_valid_geofence_masuk' => true,
            'foto_masuk' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400',
            'status_pekerjaan' => 'sebagian',
        ]);

        // 6. Pemeriksaan Tanaman Hari Ini
        PemeriksaanTanaman::create([
            'user_id' => $andi->id,
            'kebun_id' => $kebun->id,
            'tanggal' => Carbon::today(),
            'kondisi_daun' => 'Bercak',
            'kondisi_batang' => 'Normal',
            'kondisi_bunga' => 'Normal',
            'kondisi_buah' => 'Normal',
            'catatan' => 'Ditemukan sedikit bercak daun di bagian ujung',
            'foto_url' => 'https://images.unsplash.com/photo-1592417817098-8f3d6910985b?w=600',
        ]);

        // 7. Laporan Masalah
        LaporanMasalah::create([
            'user_id' => $andi->id,
            'kebun_id' => $kebun->id,
            'blok_id' => $blokA->id,
            'baris' => 'Blok A - Baris 3',
            'jenis_masalah' => 'Hama',
            'jumlah_tanaman' => 5,
            'kondisi' => 'Daun rusak',
            'foto_urls' => [
                'https://images.unsplash.com/photo-1592417817098-8f3d6910985b?w=600',
                'https://images.unsplash.com/photo-1588252303782-cb80119abd6d?w=600',
            ],
            'catatan' => 'Perlu pemeriksaan lebih lanjut dan penyemprotan insektisida organik',
            'status' => 'menunggu',
        ]);

        // 8. Laporan Harian
        LaporanHarian::create([
            'user_id' => $andi->id,
            'kebun_id' => $kebun->id,
            'tanggal' => Carbon::today(),
            'kondisi_tanaman' => 'Baik',
            'gulma' => 'Sedang',
            'hama' => 'Ada',
            'penyakit' => 'Tidak ada',
            'ajir' => 'Baik',
            'perempelan' => 'Sudah',
            'pemupukan' => 'Dilakukan',
            'penyemprotan' => 'Tidak',
            'kendala' => 'Tidak ada',
            'foto_sebelum_url' => 'https://images.unsplash.com/photo-1592417817098-8f3d6910985b?w=600',
            'foto_sesudah_url' => 'https://images.unsplash.com/photo-1588252303782-cb80119abd6d?w=600',
        ]);
    }
}
