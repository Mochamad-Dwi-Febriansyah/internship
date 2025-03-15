<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_depan', 50)->nullable(); 
            $table->string('nama_belakang', 50)->nullable();
            $table->string('nisn_npm_nim_npp', 20)->nullable()->index();
            $table->string('bagian',30)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['male', 'female'])->nullable();
            $table->string('nomor_hp', 20)->nullable();
            $table->string('foto')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->text('alamat')->nullable();  // Nullable, karena tidak diperlukan oleh mentor dan kepegawaian
            $table->string('kode_pos', 100)->nullable();  // Nullable, karena tidak diperlukan
            $table->string('provinsi', 100)->nullable();  // Nullable, karena tidak diperlukan oleh mentor dan kepegawaian
            $table->string('kabupaten_kota', 100)->nullable(); // Nullable, karena tidak diperlukan oleh mentor dan kepegawaian
            $table->string('kecamatan', 100)->nullable(); // Nullable, karena tidak diperlukan oleh mentor dan kepegawaian
            $table->string('kelurahan_desa', 100)->nullable(); // Nullable, karena tidak diperlukan oleh mentor dan kepegawaian
            $table->enum('role', ['admin', 'user', 'mentor', 'kepegawaian'])->default('user');
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
