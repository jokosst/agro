<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_masalah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kebun_id')->nullable()->constrained('kebun')->nullOnDelete();
            $table->foreignId('blok_id')->nullable()->constrained('kebun_bloks')->nullOnDelete();
            $table->string('baris')->nullable(); // misal: Baris 3
            $table->string('jenis_masalah')->default('Hama'); // Hama, Penyakit, Gulma, dll
            $table->integer('jumlah_tanaman')->default(1);
            $table->string('kondisi')->nullable(); // misal: Daun rusak
            $table->json('foto_urls')->nullable(); // Array of Google Drive URLs
            $table->text('catatan')->nullable();
            $table->enum('status', ['menunggu', 'ditangani', 'selesai'])->default('menunggu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_masalah');
    }
};
