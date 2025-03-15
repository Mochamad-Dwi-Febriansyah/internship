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
        Schema::create('laporan_harians', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->uuid('presensi_id');
            $table->foreign('presensi_id')->references('id')->on('presensis')->onDelete('cascade');

            $table->string('title')->nullable(); // Judul kegiatan
            $table->text('report')->nullable(); // Deskripsi kegiatan
            $table->text('result')->nullable(); // Hasil yang dicapai
            
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('pending');
            $table->string('rejection_note')->nullable();
            $table->uuid('verified_by_id')->nullable();
            $table->foreign('verified_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_harians');
    }
};
