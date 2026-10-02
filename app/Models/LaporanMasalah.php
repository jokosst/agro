<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanMasalah extends Model
{
    use HasFactory;

    protected $table = 'laporan_masalah';

    protected $fillable = [
        'user_id',
        'kebun_id',
        'blok_id',
        'baris',
        'jenis_masalah',
        'jumlah_tanaman',
        'kondisi',
        'foto_urls',
        'catatan',
        'status',
    ];

    protected $casts = [
        'foto_urls' => 'array',
        'jumlah_tanaman' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $laporan): void {
            if ($laporan->blok_id) {
                $laporan->blok?->syncKondisiFromLaporan();
            }

            if ($laporan->wasChanged('blok_id') && $laporan->getOriginal('blok_id')) {
                $oldBlok = KebunBlok::find($laporan->getOriginal('blok_id'));
                $oldBlok?->syncKondisiFromLaporan();
            }
        });

        static::deleted(function (self $laporan): void {
            if ($laporan->blok_id) {
                $laporan->blok?->syncKondisiFromLaporan();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kebun()
    {
        return $this->belongsTo(Kebun::class);
    }

    public function blok()
    {
        return $this->belongsTo(KebunBlok::class, 'blok_id');
    }
}
