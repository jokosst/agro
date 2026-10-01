<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['admin', 'mandor', 'pekerja'])->default('pekerja')->after('phone');
            $table->string('avatar')->nullable()->after('role');
            $table->foreignId('kebun_id')->nullable()->after('avatar')->constrained('kebun')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['kebun_id']);
            $table->dropColumn(['username', 'phone', 'role', 'avatar', 'kebun_id']);
        });
    }
};
