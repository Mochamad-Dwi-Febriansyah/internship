<?php

namespace App\Http\Controllers;

use App\Models\LaporanAkhir;
use App\Models\LaporanHarian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class MentorController extends Controller
{

    private function getUserIdsForMentor($mentorId)
    {
        return DB::table('berkas')
            ->where('mentor_id', $mentorId)
            ->where('status_berkas', 'terima')
            ->pluck('user_id');
    }
    private function getMentorFromToken()
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

    public function getTotalUserMagang()
    {
        $mentor = $this->getMentorFromToken();
        if ($mentor instanceof \Illuminate\Http\JsonResponse) return $mentor;

        $userIds = $this->getUserIdsForMentor($mentor->id);
        $totalUserMagang = User::whereIn('id', $userIds)->where('role', 'user')->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Total user magang berhasil diambil',
            'data' => $totalUserMagang
        ], 200);
    }
    public function getTotalVerifikasiLaporanHarian()
    {
        $mentor = $this->getMentorFromToken();
        if ($mentor instanceof \Illuminate\Http\JsonResponse) return $mentor;

        $userIds = $this->getUserIdsForMentor($mentor->id);
        $totalLaporanHarian = LaporanHarian::whereIn('user_id', $userIds)
            ->where('status', '!=', 'approved')
            ->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Total laporan harian belum disetujui berhasil diambil',
            'data' => $totalLaporanHarian
        ], 200);
    }
    public function getTotalVerifikasiLaporanAkhir()
    {
        $mentor = $this->getMentorFromToken();
        if ($mentor instanceof \Illuminate\Http\JsonResponse) return $mentor;


        $userIds = $this->getUserIdsForMentor($mentor->id);
        $totalLaporanAkhir = LaporanAkhir::whereIn('user_id', $userIds)
            ->where('status_verifikasi_mentor', '!=', 'approved')
            ->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Total laporan akhir belum disetujui berhasil diambil',
            'data' => $totalLaporanAkhir
        ], 200);
    }
}
