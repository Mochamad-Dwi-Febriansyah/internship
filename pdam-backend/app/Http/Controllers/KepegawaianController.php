<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use App\Models\LaporanAkhir;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class KepegawaianController extends Controller
{ 
    private function getKepegawaianFromToken()
    {
        try {
            return JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token expired'], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['status' => 'error', 'message' => 'Token invalid'], 401);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Token not found'], 401);
        }
    }

    public function getTotalPengajuanBerkas()
    {
        $kepegawaian = $this->getKepegawaianFromToken();
        if ($kepegawaian instanceof \Illuminate\Http\JsonResponse) return $kepegawaian;
 
        $totalPengajuanBerkas = Berkas::whereNull('surat_diterima')
        ->where(function($query) {
            $query->whereNull('mentor_id')
                  ->orWhere(function($q) {
                      $q->whereNotNull('mentor_id')
                        ->where('status_berkas', 'pending');
                  });
        })
        ->count(); // ->count(); // Jika hanya total jumlah, pakai count()

        $totalBelumDisetujui = Berkas::where(function($query) {
            $query->whereNull('mentor_id') // belum memilih mentor
                  ->orWhere(function($q) {
                      $q->whereNotNull('mentor_id')
                        ->where('status_berkas', 'pending'); // status masih pending
                  });
        })->count();
    
        // Berkas yang belum dikirim surat_diterima
        $totalBelumKirimSurat = Berkas::where('status_berkas', 'terima')
        ->whereNotNull('mentor_id')
        ->whereNull('surat_diterima')
        ->count();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Total pengajuan berkas berhasil diambil',
            'data' => [
                'total_belum_disetujui' => $totalBelumDisetujui,
                'total_belum_kirim_surat' => $totalBelumKirimSurat
            ]
        ], 200);
    } 
    public function getTotalUserMagang()
    {
        $kepegawaian = $this->getKepegawaianFromToken();
        if ($kepegawaian instanceof \Illuminate\Http\JsonResponse) return $kepegawaian;
 
        $totalUserMagang = User::where('role', 'user')->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Total user magang berhasil diambil',
            'data' => $totalUserMagang
        ], 200);
    } 
    public function getTotalUserMentor()
    {
        $kepegawaian = $this->getKepegawaianFromToken();
        if ($kepegawaian instanceof \Illuminate\Http\JsonResponse) return $kepegawaian;
 
        $totalUserMentor = User::where('role', 'mentor')->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Total user mentor berhasil diambil',
            'data' => $totalUserMentor
        ], 200);
    } 
    public function getTotalVerifikasiLaporanAkhir()
    {
        $kepegawaian = $this->getKepegawaianFromToken();
        if ($kepegawaian instanceof \Illuminate\Http\JsonResponse) return $kepegawaian;
 
        $totalLaporanAkhir = LaporanAkhir::where('status_verifikasi_kepegawaian', '!=', 'approved')->where('status_verifikasi_mentor', '=', 'approved')
            ->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Total laporan akhir belum disetujui berhasil diambil',
            'data' => $totalLaporanAkhir
        ], 200);
    }
} 
       