<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemeriksaan_tanaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kebun_id')->nullable()->constrained('kebun')->nullOnDelete();
            $table->date('tanggal');
            $table->string('kondisi_daun')->default('Normal'); // Normal, Layu, Menguning, Bercak, Rusak
            $table->string('kondisi_batang')->default('Normal'); // Normal, Patah, Luka, Terganggu
            $table->string('kondisi_bunga')->default('Normal'); // Normal, Rontok, Terganggu
            $table->string('kondisi_buah')->default('Normal'); // Normal, Rusak, Busuk, Hama
            $table->string('foto_url')->nullable(); // Google Drive URL
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_tanaman');
    }
};
