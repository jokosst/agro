<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PekerjaTugas extends Model
{
    use HasFactory;

    protected $table = 'pekerja_tugas';

    protected $fillable = [
        'user_id',
        'tugas_harian_id',
        'tanggal',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tugasHarian()
    {
        return $this->belongsTo(TugasHarian::class, 'tugas_harian_id');
    }
}
