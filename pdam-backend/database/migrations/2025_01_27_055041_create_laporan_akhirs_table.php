<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('laporan_akhirs', function (Blueprint $table) {
            $table->uuid('id')->primary(); 

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->uuid('berkas_id');
            $table->foreign('berkas_id')->references('id')->on('berkas')->onDelete('cascade');

            $table->uuid('master_sekolah_universitas_id');
            $table->foreign('master_sekolah_universitas_id')->references('id')->on('master_sekolah_universitas')->onDelete('cascade');

            $table->string('title');
            $table->text('report');
            $table->string('assessment_report_file')->nullable();
            $table->string('final_report_file')->nullable();
            $table->string('photo')->nullable();
            $table->string('video')->nullable(); 
            $table->string('certificate')->nullable();
            $table->string('work_certificate')->nullable();
            $table->uuid('verified_by_mentor_id')->nullable();
            $table->foreign('verified_by_mentor_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('status_verifikasi_mentor', ['approved', 'pending', 'rejected'])->default('pending');
            $table->string('rejection_note_mentor')->nullable();
            $table->uuid('verified_by_kepegawian_id')->nullable();
            $table->foreign('verified_by_kepegawian_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('status_verifikasi_kepegawaian', ['approved', 'pending', 'rejected'])->default('pending');
            $table->string('rejection_note_kepegawaian')->nullable(); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_akhirs');
    }
};
