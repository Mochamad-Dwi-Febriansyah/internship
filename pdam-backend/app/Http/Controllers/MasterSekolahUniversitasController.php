<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use App\Models\MasterSekolahUniversitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

use function App\Providers\logActivity;

class MasterSekolahUniversitasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $master = Cache::remember('masters_list', 600, function () {
                return MasterSekolahUniversitas::get();
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Data master berhasil diambil',
                'data' => $master
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        } 
    } 
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $masterValidator = Validator::make($request->all(), [
            'nama_sekolah_universitas' => 'required|max:100',
            'jurusan_sekolah' => 'nullable|max:100',
            'fakultas_universitas' => 'nullable|max:100',
            'program_studi_universitas' => 'nullable|max:100',
            'alamat_sekolah_universitas' => 'required|max:255',
            'kabupaten_kota_sekolah_universitas' => 'required|max:100',
            'provinsi_sekolah_universitas' => 'required|max:100',
            'kode_pos_sekolah_universitas' => 'required|max:10',
            'nomor_telp_sekolah_universitas' => 'nullable|regex:/^\+?[\d\s\(\)-]+$/',
            'email_sekolah_universitas' => 'nullable|email',
            
        ]); 
        if($masterValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $masterValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            $master = MasterSekolahUniversitas::firstOrCreate(
                ['email_sekolah_universitas' => $request->email_sekolah_universitas,
                'jurusan_sekolah' => $request->jurusan_sekolah,
                'fakultas_universitas' => $request->fakultas_universitas,
                'program_studi_universitas' => $request->program_studi_universitas,
                ], 
                $request->only([
                    'nama_sekolah_universitas',
                    'alamat_sekolah_universitas',
                    'kabupaten_kota_sekolah_universitas',
                    'provinsi_sekolah_universitas',
                    'kode_pos_sekolah_universitas',
                    'nomor_telp_sekolah_universitas',
                    'email_sekolah_universitas',
                ])
            );
            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
 
            logActivity($user->id, $nama, 'update', 'Master', $master->id, null);
            
            DB::commit();
            Cache::forget('masters_list');
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menambahkan data master',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try { 
            $cacheKey = "master_{$id}";
            $master = Cache::remember($cacheKey, 600, function () use ($id) {
                return MasterSekolahUniversitas::find($id);
            });
            if (!$master) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'master tidak ditemukan'
                ], 404); 
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data master berhasil diambil',
                'data' => $master
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }
 

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $master = MasterSekolahUniversitas::find($id);
        if (!$master) {
            return response()->json([
                'status' => 'error',
                'message' => 'Master tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }
        $oldData = $master->toArray();
        $masterValidator = Validator::make($request->all(), [
            'nama_sekolah_universitas' => 'required|max:100',
            'jurusan_sekolah' => 'nullable|max:100',
            'fakultas_universitas' => 'nullable|max:100',
            'program_studi_universitas' => 'nullable|max:100',
            'alamat_sekolah_universitas' => 'required|max:255',
            'kabupaten_kota_sekolah_universitas' => 'required|max:100',
            'provinsi_sekolah_universitas' => 'required|max:100',
            'kode_pos_sekolah_universitas' => 'required|max:10',
            'nomor_telp_sekolah_universitas' => 'nullable|regex:/^\+?[\d\s\(\)-]+$/',
            'email_sekolah_universitas' => 'nullable|email',
            
        ]); 
        if($masterValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $masterValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
           $master->update([
                'email_sekolah_universitas' => $request->email_sekolah_universitas,
                'jurusan_sekolah' => $request->jurusan_sekolah,
                'fakultas_universitas' => $request->fakultas_universitas,
                'program_studi_universitas' => $request->program_studi_universitas,
                'nama_sekolah_universitas' => $request->nama_sekolah_universitas,
                'alamat_sekolah_universitas' => $request->alamat_sekolah_universitas,
                'kabupaten_kota_sekolah_universitas' => $request->kabupaten_kota_sekolah_universitas,
                'provinsi_sekolah_universitas' => $request->provinsi_sekolah_universitas,
                'kode_pos_sekolah_universitas' => $request->kode_pos_sekolah_universitas,
                'nomor_telp_sekolah_universitas' => $request->nomor_telp_sekolah_universitas,
            ]); 
            $newData = $master->toArray(); 

            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
 
             logActivity($user->id, $nama, 'update', 'Master', $master->id, [
                 'old' => $oldData,
                 'new' => $newData,
             ]);

            DB::commit();
    
            Cache::forget('masters_list');
            Cache::forget("master_{$id}"); 

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengupdate data master', 
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengupdate data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $master = MasterSekolahUniversitas::find($id);
            if (!$master) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'master tidak ditemukan'
                ], 404);
            }
            $oldData = $master->toArray(); 
            $master->delete(); 

            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'delete', 'MasterSekolahUniversitas', $user->id, [
                'old' => $oldData,
            ]);
            Cache::forget('masters_list');
            Cache::forget("master_{$id}"); 
        return response()->json([
            'status' => 'success',
            'message' => 'Data master berhasil dihapus', 
        ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }

    public function masterSekolahByMagangId(){
        {
            try { 
                // $cacheKey = "master_{$id}";
                // $master = Cache::remember($cacheKey, 600, function () use ($id) {
                //     return MasterSekolahUniversitas::find($id);
                // });
                $userId = Auth::user()->id; 
                $berkas = Berkas::with('masterSekolah')->where('user_id', $userId)->first(); 
                if (!$berkas || !$berkas->masterSekolah) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'master tidak ditemukan'
                    ], 404); 
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data master berhasil diambil',
                    'data' => $berkas->masterSekolah
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat mengambil data',
                    'error' => $th->getMessage()
                ], 500);
            } 
        }
     
    }
}
