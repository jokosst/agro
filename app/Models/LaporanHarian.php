<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanHarian extends Model
{
    use HasFactory;

    protected $table = 'laporan_harian';

    protected $fillable = [
        'user_id',
        'kebun_id',
        'tanggal',
        'kondisi_tanaman',
        'gulma',
        'hama',
        'penyakit',
        'ajir',
        'perempelan',
        'pemupukan',
        'penyemprotan',
        'kendala',
        'foto_sebelum_url',
        'foto_sesudah_url',
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
