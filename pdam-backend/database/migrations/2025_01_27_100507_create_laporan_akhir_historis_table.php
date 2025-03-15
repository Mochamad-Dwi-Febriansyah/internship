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
        Schema::create('laporan_akhir_historis', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relasi laporan akhir utama
            $table->uuid('laporan_akhir_id');
            $table->foreign('laporan_akhir_id')->references('id')->on('laporan_akhirs')->onDelete('cascade');

            // Relasi user pengaju
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Data laporan yang disimpan
            $table->string('title');
            $table->text('report');
            $table->string('assessment_report_file')->nullable();
            $table->string('final_report_file')->nullable();
            $table->string('photo')->nullable();
            $table->string('video')->nullable(); 

            // Status pengajuan riwayat
            $table->enum('status', ['submitted', 'revised', 'approved', 'rejected'])->default('submitted');
            $table->string('rejection_note')->nullable();

            // Nomor versi untuk tracking (misal: 1, 2, 3)
            $table->unsignedInteger('version_number')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_akhir_historis');
    }
};
