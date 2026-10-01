<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasHarian extends Model
{
    use HasFactory;

    protected $table = 'tugas_harian';

    protected $fillable = [
        'kebun_id',
        'judul',
        'deskripsi',
        'urutan',
        'is_active',
    ];

    public function kebun()
    {
        return $this->belongsTo(Kebun::class);
    }

    public function pekerjaTugas()
    {
        return $this->hasMany(PekerjaTugas::class);
    }
}
