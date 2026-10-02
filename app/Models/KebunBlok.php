<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KebunBlok extends Model
{
    use HasFactory;

    protected $table = 'kebun_bloks';

    protected $fillable = [
        'kebun_id',
        'kode_blok',
        'nama_blok',
        'status_kondisi',
        'jumlah_tanaman',
        'jumlah_masalah',
        'jumlah_hama',
        'latitude',
        'longitude',
        'keterangan',
    ];

    public function kebun()
    {
        return $this->belongsTo(Kebun::class);
    }

    public function laporanMasalah()
    {
        return $this->hasMany(LaporanMasalah::class, 'blok_id');
    }

    /**
     * Sinkronisasi otomatis status kondisi, jumlah hama, dan jumlah masalah
     * berdasarkan laporan masalah & hama aktif (status belum selesai).
     */
    public function syncKondisiFromLaporan(): void
    {
        $activeReports = $this->laporanMasalah()
            ->where('status', '!=', 'selesai')
            ->get();

        $jumlahHama = (int) $activeReports->filter(function ($item) {
            return strcasecmp($item->jenis_masalah ?? '', 'Hama') === 0;
        })->sum('jumlah_tanaman');

        $jumlahMasalah = (int) $activeReports->filter(function ($item) {
            return strcasecmp($item->jenis_masalah ?? '', 'Hama') !== 0;
        })->sum('jumlah_tanaman');

        if ($activeReports->contains('status', 'menunggu')) {
            $statusKondisi = 'masalah';
        } elseif ($activeReports->contains('status', 'ditangani')) {
            $statusKondisi = 'perhatian';
        } else {
            $statusKondisi = 'normal';
        }

        $this->updateQuietly([
            'jumlah_hama' => $jumlahHama,
            'jumlah_masalah' => $jumlahMasalah,
            'status_kondisi' => $statusKondisi,
        ]);
    }
}
