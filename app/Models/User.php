<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'role',
        'avatar',
        'kebun_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function kebun()
    {
        return $this->belongsTo(Kebun::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function laporanHarian()
    {
        return $this->hasMany(LaporanHarian::class);
    }

    public function laporanMasalah()
    {
        return $this->hasMany(LaporanMasalah::class);
    }

    public function pekerjaTugas()
    {
        return $this->hasMany(PekerjaTugas::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPekerja(): bool
    {
        return $this->role === 'pekerja';
    }
}
