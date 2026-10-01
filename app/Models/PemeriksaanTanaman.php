<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemeriksaanTanaman extends Model
{
    use HasFactory;

    protected $table = 'pemeriksaan_tanaman';

    protected $fillable = [
        'user_id',
        'kebun_id',
        'tanggal',
        'kondisi_daun',
        'kondisi_batang',
        'kondisi_bunga',
        'kondisi_buah',
        'foto_url',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kebun()
    {
        return $this->belongsTo(Kebun::class);
    }
}
