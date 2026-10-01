<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterHamaPenyakit extends Model
{
    use HasFactory;

    protected $table = 'master_hama_penyakit';

    protected $fillable = [
        'nama',
        'kategori',
        'bagian_tanaman',
        'gejala',
        'solusi_pengendalian',
        'status',
    ];
}
