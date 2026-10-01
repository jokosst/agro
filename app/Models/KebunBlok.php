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
}
