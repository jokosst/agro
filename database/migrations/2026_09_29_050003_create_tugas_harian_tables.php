<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kebun_id')->nullable()->constrained('kebun')->nullOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pekerja_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('tugas_harian_id')->constrained('tugas_harian')->onDelete('cascade');
            $table->date('tanggal');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tugas_harian_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pekerja_tugas');
        Schema::dropIfExists('tugas_harian');
    }
};
