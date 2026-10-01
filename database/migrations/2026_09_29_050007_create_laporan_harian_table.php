<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kebun_id')->nullable()->constrained('kebun')->nullOnDelete();
            $table->date('tanggal');
            $table->string('kondisi_tanaman')->default('Baik'); // Baik, Cukup, Buruk
            $table->string('gulma')->default('Sedang'); // Bersih, Sedang, Banyak
            $table->string('hama')->default('Tidak ada'); // Ada, Tidak ada
            $table->string('penyakit')->default('Tidak ada'); // Ada, Tidak ada
            $table->string('ajir')->default('Baik'); // Baik, Perlu perbaikan
            $table->string('perempelan')->default('Sudah'); // Sudah, Belum
            $table->string('pemupukan')->default('Dilakukan'); // Dilakukan, Tidak
            $table->string('penyemprotan')->default('Tidak'); // Dilakukan, Tidak
            $table->text('kendala')->nullable(); // misal: "Tidak ada"
            $table->string('foto_sebelum_url')->nullable(); // Google Drive URL
            $table->string('foto_sesudah_url')->nullable(); // Google Drive URL
            $table->timestamps();

            $table->unique(['user_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_harian');
    }
};
