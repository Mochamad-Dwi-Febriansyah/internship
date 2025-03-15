<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\KepegawaianController;
use App\Http\Controllers\LaporanAkhirController;
use App\Http\Controllers\MasterSekolahUniversitasController;
use App\Http\Controllers\MentorController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route; 
 
Route::prefix('v1')->group(function () { 
    Route::post('/berkas', [BerkasController::class, 'ajuanBerkas']);  
    Route::get('/berkas-cek/{nomor_registrasi}', [BerkasController::class, 'cekBerkas']);   

    Route::prefix('auth')->group(function(){ 
        Route::post('/login', [AuthController::class, 'login']);    
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']); 
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']); 
    }); 

    Route::middleware('cekToken')->group(function(){
        Route::get('/search', [SearchController::class, 'index']);
        Route::prefix('auth')->group(function(){
            Route::get('/me', [AuthController::class, 'me']); 
            Route::put('/profile/{id}', [AuthController::class, 'profile'])->middleware('cekToken');  
            Route::post('/logout', [AuthController::class, 'logout']); 
        });
        Route::middleware('checkRole:user')->group(function(){ 
            Route::get('/master-sekolah-by-magang', [MasterSekolahUniversitasController::class, 'masterSekolahByMagangId']);
            Route::get('/berkas-by-magang', [BerkasController::class, 'berkasByMagangId']);
                
            Route::post('/presensi', [PresensiController::class,'presensi']);
            Route::get('/presensi', [PresensiController::class,'index']);
            Route::get('/presensi-hari-ini', [PresensiController::class,'presensiHariIni']);

            Route::get('/pengajuan', [PresensiController::class,'getPengajuan']);
            Route::post('/pengajuan', [PresensiController::class,'pengajuan']);

            Route::get('/laporan', [PresensiController::class,'getLaporan']); 
            Route::put('/laporan/{id}', [PresensiController::class,'updateLaporanById']);
            Route::post('/laporan', [PresensiController::class,'laporan']); 
            Route::get('/export-log-book', [PresensiController::class,'exportLogBook']);
 
            Route::get('/laporan-akhir', [LaporanAkhirController::class,'index']);
            Route::post('/laporan-akhir', [LaporanAkhirController::class,'store']);
            Route::get('/laporan-akhir/{id}', [LaporanAkhirController::class,'show']);
            Route::put('/laporan-akhir/{id}', [LaporanAkhirController::class,'update']);
            Route::delete('/laporan-akhir/{id}', [LaporanAkhirController::class,'destroy']);
            Route::get('/clear-cache-laporan-akhir', [LaporanAkhirController::class, 'clearCacheLaporanAkhir']);
          
        });

        Route::middleware('checkRole:admin')->group(function(){ 
            Route::resource('master', MasterSekolahUniversitasController::class);  
            Route::get('/users-kepegawaian', [UserController::class, 'userKepegawaian']);

            Route::get('/berkas', [BerkasController::class, 'index']);
            Route::get('/berkas/{id}', [BerkasController::class, 'show']); 
        });

        Route::middleware('checkRole:mentor')->group(function(){ 
            Route::get('/total-user-magang-by-mentor', [MentorController::class, 'getTotalUserMagang']); 
            Route::get('/total-verifikasi-laporan-harian', [MentorController::class, 'getTotalVerifikasiLaporanHarian']);
            Route::get('/total-verifikasi-laporan-akhir', [MentorController::class, 'getTotalVerifikasiLaporanAkhir']);

            Route::get('/users-magang-by-mentor', [UserController::class, 'userMagangByMentor']);
            Route::get('/clear-cache-users-magang-by-mentor', [UserController::class, 'clearCacheUserMagangByMentor']);

            Route::get('/pengajuan_mentor', [PresensiController::class,'getPengajuanMentor']);
            Route::put('/validasi_pengajuan/{pengajuan_id}', [PresensiController::class,'validasiPengajuan']);

            Route::get('/laporan_harian_mentor', [PresensiController::class,'getLaporanHarianByMentor']);
            Route::get('/clear_cache_laporan_harian_mentor', [PresensiController::class,'clearCacheLaporanHarianByMentor']); 
            Route::put('/validasi_laporan', [PresensiController::class,'validasiLaporan']);
            
            Route::get('/laporan-akhir-mentor',  [LaporanAkhirController::class ,'getLaporanAkhirByMentor']);
            Route::get('/laporan-akhir-mentor/{id}', [LaporanAkhirController::class,'showLaporanAkhirByMentor']);
            Route::get('/clear-cache-laporan-akhir-mentor',  [LaporanAkhirController::class ,'clearCacheLaporanAkhirByMentor']);
            Route::put('/validasi-laporan-akhir-mentor/{laporan_akhir_id}',  [LaporanAkhirController::class ,'validasiLaporanAkhirByMentor']);


            Route::get('/tanda-tangan', [SignatureController::class, 'index']);
            Route::get('/tanda-tangan/{id}', [SignatureController::class, 'show']);
            Route::post('/tanda-tangan', [SignatureController::class, 'store']);
            Route::put('/tanda-tangan/{id}', [SignatureController::class, 'update']);
            Route::delete('/tanda-tangan/{id}', [SignatureController::class, 'destroy']);
            
        }); 

        Route::middleware('checkRole:kepegawaian')->group(function(){ 
            Route::get('/total-pengajuan-berkas', [KepegawaianController::class, 'getTotalPengajuanBerkas']);
            Route::get('/total-user-magang-by-kepegawaian', [KepegawaianController::class, 'getTotalUserMagang']);
            Route::get('/total-user-mentor-by-kepegawaian', [KepegawaianController::class, 'getTotalUserMentor']);
            Route::get('/total-verifikasi-laporan-akhir-by-kepegawaian', [KepegawaianController::class, 'getTotalVerifikasiLaporanAkhir']);

            Route::put('/status-berkas-this-mentor/{user_id}', [BerkasController::class, 'updateThisMentorStatusBerkas']);
            Route::put('/surat-terima/{user_id}', [BerkasController::class, 'suratTerimaPengajuanBerkas']);
            Route::get('/pengajuan-berkas', [BerkasController::class, 'pengajuanBerkas']);
            Route::put('/toggle-status-berkas/{id_berkas}', [BerkasController::class, 'toggleStatusPengajuanBerkas']);
            Route::get('/clear-cache-pengajuan-berkas', [BerkasController::class, 'clearCachePengajuanBerkas']);

            Route::get('/berkas', [BerkasController::class, 'daftarBerkas']);
            Route::get('/berkas/{id}', [BerkasController::class, 'show']);

            Route::get('/laporan-akhir-kepegawaian',  [LaporanAkhirController::class ,'getLaporanAkhirByKepegawaian']);
            Route::get('/laporan-akhir-kepegawaian/{id}', [LaporanAkhirController::class,'showLaporanAkhirByKepegawaian']);
            Route::get('/clear-cache-laporan-akhir-kepegawaian',  [LaporanAkhirController::class ,'clearCacheLaporanAkhirByKepegawaian']);
            Route::put('/validasi-laporan-akhir-kepegawaian/{laporan_akhir_id}',  [LaporanAkhirController::class ,'validasiLaporanAkhirByKepegawaian']);
        });

        Route::middleware(['checkRole:admin,kepegawaian,mentor,user'])->group(function () {
            Route::get('/users-magang/{id}', [UserController::class, 'showUserMagang']);
            Route::get('/laporan/{id}', [PresensiController::class,'getLaporanById']);
        });
        Route::middleware(['checkRole:admin,kepegawaian,user'])->group(function () {
            Route::post('/users', [UserController::class, 'store']);
            Route::delete('/users/{id}', [UserController::class, 'destroy']);
            Route::get('/users-magang', [UserController::class, 'userMagang']);
            Route::get('/clear-cache-users/{role}', [UserController::class, 'clearCacheUser']);
            Route::get('/users-mentor', [UserController::class, 'userMentor']);

            Route::put('/toggle-user-status/{id_user}', [UserController::class, 'toggleUserStatus']);
        }); 
    }); 

});
 