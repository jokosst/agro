<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kebun extends Model
{
    use HasFactory;

    protected $table = 'kebun';

    protected $fillable = [
        'nama',
        'lokasi_text',
        'latitude',
        'longitude',
        'radius_meter',
        'luas_lahan',
        'status',
    ];

    public function bloks()
    {
        return $this->hasMany(KebunBlok::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }
}
