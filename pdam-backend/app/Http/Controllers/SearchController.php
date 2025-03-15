<?php

namespace App\Http\Controllers;

use App\Models\LaporanAkhir;
use App\Models\LaporanHarian;
use App\Models\MasterSekolahUniversitas;
use App\Models\Presensi;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');

        $users = User::search($query)->get();
        if ($users->isEmpty()) {
            $users = User::where('nama_depan', 'LIKE', "%{$query}%")
                         ->orWhere('nama_belakang', 'LIKE', "%{$query}%")
                         ->orWhere('email', 'LIKE', "%{$query}%")
                         ->get();
        }
        $presensi = Presensi::search($query)->get();
        if ($presensi->isEmpty()) {
            $presensi = Presensi::where('tanggal', 'LIKE', "%{$query}%")
                         ->orWhere('waktu_check_in', 'LIKE', "%{$query}%")
                         ->orWhere('waktu_check_out', 'LIKE', "%{$query}%")
                         ->get();
        } 
        $masterSekolahUniversitas = MasterSekolahUniversitas::search($query)->get();
        if ($masterSekolahUniversitas->isEmpty()) {
            $masterSekolahUniversitas = MasterSekolahUniversitas::where('nama_sekolah_universitas', 'LIKE', "%{$query}%")
                         ->orWhere('jurusan_sekolah', 'LIKE', "%{$query}%")
                         ->orWhere('fakultas_universitas', 'LIKE', "%{$query}%")
                         ->orWhere('program_studi_universitas', 'LIKE', "%{$query}%")
                         ->orWhere('alamat_sekolah_universitas', 'LIKE', "%{$query}%")
                         ->get();
        }  
        $laporanHarian = LaporanHarian::search($query)->get();
        if ($laporanHarian->isEmpty()) {
            $laporanHarian = LaporanHarian::where('tanggal', 'LIKE', "%{$query}%")
                         ->orWhere('nama_sekolah_universitas', 'LIKE', "%{$query}%")
                         ->orWhere('jurusan_sekolah', 'LIKE', "%{$query}%")
                         ->orWhere('fakultas_universitas', 'LIKE', "%{$query}%")
                         ->orWhere('program_studi_universitas', 'LIKE', "%{$query}%")
                         ->orWhere('alamat_sekolah_universitas', 'LIKE', "%{$query}%")
                         ->get();
        }  
        $laporanAkhir = LaporanAkhir::search($query)->get();

        return response()->json([
            'users' => $users,
            'presensi' => $presensi,
            'masterSekolahUniversitas' => $masterSekolahUniversitas,
            'laporanHarian' => $laporanHarian,
            'laporanAkhir' => $laporanAkhir,
        ]);
    }
}
