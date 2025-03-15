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
        Schema::create('presensis', function (Blueprint $table) {
            $table->uuid('id')->primary(); 

            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); 

            $table->date('tanggal')->nullable();
            $table->time('waktu_check_in')->nullable();
            $table->string('foto_check_in', 255)->nullable(); 
            $table->time('waktu_check_out')->nullable();
            $table->string('foto_check_out', 255)->nullable(); 
            $table->decimal('latitude_check_in', 10, 6)->nullable(); // akurasi sekitar 1 meter   
            $table->decimal('latitude_check_out', 10, 6)->nullable(); // akurasi sekitar 1 meter   
            $table->decimal('longitude_check_in', 10, 6)->nullable(); 
            $table->decimal('longitude_check_out', 10, 6)->nullable(); 
            $table->enum('status', ['hadir', 'izin', 'sakit','alpa'])->nullable(); 
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
