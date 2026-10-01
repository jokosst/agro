<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kebun', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->default('Kebun Cabai Agrocom');
            $table->string('lokasi_text')->default('Sambas, Kalimantan Barat');
            $table->decimal('latitude', 10, 7)->default(-0.1234000);
            $table->decimal('longitude', 10, 7)->default(109.3456000);
            $table->integer('radius_meter')->default(50); // Radius valid <= 50 meter
            $table->string('luas_lahan')->nullable()->default('2 Hektar');
            $table->string('status')->default('aktif');
            $table->timestamps();
        });

        Schema::create('kebun_bloks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kebun_id')->constrained('kebun')->onDelete('cascade');
            $table->string('kode_blok'); // Blok A, Blok B, dll
            $table->string('nama_blok')->nullable();
            $table->enum('status_kondisi', ['normal', 'perhatian', 'masalah'])->default('normal');
            $table->integer('jumlah_tanaman')->default(100);
            $table->integer('jumlah_masalah')->default(0);
            $table->integer('jumlah_hama')->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kebun_bloks');
        Schema::dropIfExists('kebun');
    }
};
