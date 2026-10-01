<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'user_id',
        'kebun_id',
        'tanggal',
        'jam_masuk',
        'foto_masuk',
        'lat_masuk',
        'long_masuk',
        'jarak_masuk_meter',
        'is_valid_geofence_masuk',
        'jam_pulang',
        'foto_pulang',
        'lat_pulang',
        'long_pulang',
        'jarak_pulang_meter',
        'is_valid_geofence_pulang',
        'status_pekerjaan',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_valid_geofence_masuk' => 'boolean',
        'is_valid_geofence_pulang' => 'boolean',
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
