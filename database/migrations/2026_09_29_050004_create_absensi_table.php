<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kebun_id')->nullable()->constrained('kebun')->nullOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->string('foto_masuk')->nullable(); // Google Drive / Storage URL
            $table->decimal('lat_masuk', 10, 7)->nullable();
            $table->decimal('long_masuk', 10, 7)->nullable();
            $table->float('jarak_masuk_meter')->nullable();
            $table->boolean('is_valid_geofence_masuk')->default(true);

            $table->time('jam_pulang')->nullable();
            $table->string('foto_pulang')->nullable(); // Google Drive / Storage URL
            $table->decimal('lat_pulang', 10, 7)->nullable();
            $table->decimal('long_pulang', 10, 7)->nullable();
            $table->float('jarak_pulang_meter')->nullable();
            $table->boolean('is_valid_geofence_pulang')->default(true);

            $table->enum('status_pekerjaan', ['selesai', 'sebagian', 'belum'])->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
