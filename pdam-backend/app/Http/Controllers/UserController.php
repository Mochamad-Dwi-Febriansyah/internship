<?php

namespace App\Http\Controllers;

use App\Models\LaporanAkhir;
use App\Models\Presensi;
use App\Models\User;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 
use Tymon\JWTAuth\Facades\JWTAuth;

use function App\Providers\logActivity;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = Cache::remember('users_list', 600, function () {
                return User::where('role', 'user')->get();
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $users
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
        $userValidator = Validator::make($request->all(), [
            'nisn_npm_nim_npp' => 'max:20',
            // 'tanggal_lahir' => 'required|date',
            'nama_depan' => 'required',
            'nama_belakang' => 'nullable',
            'jenis_kelamin' => 'required|in:male,female',
            'nomor_hp' => 'required|unique:users,nomor_hp|regex:/^\+?[\d\s\(\)-]+$/',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable',
            // 'bagian' => $request->role === 'mentor' ? 'required' : 'nullable',
            // 'alamat' => 'required',
            // 'kabupaten_kota' => 'required',
            // 'provinsi' => 'required',
            // 'kode_pos' => 'required',
            'role' => 'required|in:admin,mentor,kepegawaian',  
            
        ]); 
        if($userValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $userValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            $userCreate = User::create([
                'nisn_npm_nim_npp' => $request->nisn_npm_nim_npp,
                'tanggal_lahir' => $request->tanggal_lahir,
                'nama_depan' => $request->nama_depan,
                'nama_belakang' => $request->nama_belakang,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nomor_hp' => $request->nomor_hp,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'alamat' => $request->alamat,
                'kode_pos' => $request->kode_pos,
                'provinsi' => $request->provinsi,
                'kabupaten_kota' => $request->kabupaten_kota,
                'kecamatan' => $request->kecamatan,
                'kelurahan_desa' => $request->kelurahan_desa,
                'bagian' => $request->bagian,
                'role' => $request->role
            ]);
            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'create', 'User', $userCreate->id, null);

            DB::commit();

            $roles = ['magang', 'mentor', 'kepegawaian'];
            foreach ($roles as $role) {
                $totalPages = max(1, ceil(User::where('role', $role)->count() / 20));
                for ($page = 1; $page <= $totalPages; $page++) {
                    Log::info("Query dieksekusi");
                    Cache::forget("user_{$role}_page_{$page}");
                }
            }
  
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menambahkan data user',
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
            // $cacheKey = "user_{$id}";
            $user = User::find($id);
            // $user = Cache::remember($cacheKey, 600, function () use ($id) {
            //     return User::find($id);
            // });
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $user
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
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }
        $oldData = $user->toArray();
        // dd($user);
        $userValidator = Validator::make($request->all(), [
            'nisn_npm_nim_npp_npp' => 'max:20',
            'tanggal_lahir' => 'required|date',
            'nama_depan' => 'required',
            'nama_belakang' => 'nullable',
            'jenis_kelamin' => 'required|in:male,female',
            'nomor_hp' => 'required',Rule::unique('users')->ignore($user->id),'regex:/^\+?[\d\s\(\)-]+$/',
            'email' => 'required|email',Rule::unique('users')->ignore($user->id),
            'password' => 'nullable',
            'alamat' => 'required',
            'provinsi' => 'required',
            'kabupaten_kota' => 'required',
            'kecamatan' => 'required',
            'kelurahan_desa' => 'required',
            'kode_pos' => 'required',
            'role' => 'required|in:admin,user',  
            
        ]); 
        if($userValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $userValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            $user->update([
                'nisn_npm_nim_npp' => $request->nisn_npm_nim_npp,
                'tanggal_lahir' => $request->tanggal_lahir,
                'nama_depan' => $request->nama_depan,
                'nama_belakang' => $request->nama_belakang,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nomor_hp' => $request->nomor_hp,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) :  $user->password,
                'alamat' => $request->alamat,
                'kode_pos' => $request->kode_pos,
                'provinsi' => $request->provinsi,
                'kabupaten_kota' => $request->kabupaten_kota,
                'kecamatan' => $request->kecamatan,
                'kelurahan_desa' => $request->kelurahan_desa,
                'bagian' => $request->bagian,
                'role' => $request->role
            ]);
            $newData = $user->toArray();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;

            logActivity($user->id, $nama, 'update', 'User', $user->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            DB::commit();

            Cache::forget('users_list');
            Cache::forget("user_{$id}");
    
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengupdate data user', 
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
 

    public function toggleUserStatus(Request $request, $id_user)
    {
        $user = User::find($id_user);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }
        $oldData = $user->toArray();
        // dd($user);
        $userValidator = Validator::make($request->all(), [ 
            'status' => 'required|in:active,inactive', 
            
        ]); 
        if($userValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $userValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            $user->update([  
                'status' => $request->status
            ]);
            $newData = $user->toArray();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;

            logActivity($user->id, $nama, 'update', 'User', $user->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            DB::commit();
 
            $roles = ['magang', 'mentor', 'kepegawaian'];
            foreach ($roles as $role) {
                $totalPages = max(1, ceil(User::where('role', $role)->count() / 20)); // Paksa minimal 1 halaman
                //  Log::info("Total pages untuk {$role}: {$totalPages}");
                for ($page = 1; $page <= $totalPages; $page++) {
                    // Log::info("Query dieksekusi user_{$role}_page_{$page}");
                    Cache::forget("user_{$role}_page_{$page}");
                }
            }
    
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengupdate status user', 
            ], 200);
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
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            $user->delete(); 

            $oldData = $user->toArray();
            $user->delete();
            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'delete', 'User', $user->id, [
                'old' => $oldData,
            ]);

            $roles = ['magang', 'mentor', 'kepegawaian'];
            foreach ($roles as $role) {
                $totalPages = max(1, ceil(User::where('role', $role)->count() / 20)); // Paksa minimal 1 halaman
                //  Log::info("Total pages untuk {$role}: {$totalPages}");
                for ($page = 1; $page <= $totalPages; $page++) {
                    // Log::info("Query dieksekusi user_{$role}_page_{$page}");
                    Cache::forget("user_{$role}_page_{$page}");
                }
            }
            
        return response()->json([
            'status' => 'success',
            'message' => 'Data user berhasil dihapus',  // Mengganti 'diambil' dengan 'dihapus'
        ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }

    // mentor
    public function userMagangByMentor(){
        try {
            $mentor = JWTAuth::parseToken()->authenticate();
            
            $page = request('page', 1);
            $cacheKey = "user_mentor_by_mentor_{$mentor->id}_page_{$page}";

            $users = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($mentor) {
             return User::whereIn('id', function($query) use ($mentor){
                $query->select('user_id')->from('berkas')->where('mentor_id', $mentor->id);
             })->where('role', 'user')
             ->with(['berkas' => function ($query) {
                $query->select('id', 'user_id', 'master_sekolah_universitas_id')
                      ->with(['masterSekolah' => function ($subQuery) {
                          $subQuery->select('id', 'nama_sekolah_universitas', 'jurusan_sekolah','fakultas_universitas','program_studi_universitas');
                      }]);
            }])->paginate(20);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function clearCacheUserMagangByMentor(){
        try {   
            
            $mentorId = JWTAuth::parseToken()->authenticate()->id;

            // Hitung total halaman dengan lebih efisien
            $totalData = Cache::remember("user_magang_count_mentor_{$mentorId}", now()->addMinutes(10), function () use ($mentorId) {
                return User::whereIn('id', function ($query) use ($mentorId) {
                    $query->select('user_id')->from('berkas')->where('mentor_id', $mentorId);
                })->where('role', 'user')->count();
            });
    
            $totalPages = max(1, ceil($totalData / 20));
            //  Log::info("Total pages untuk {$role}: {$totalPages}");
            for ($page = 1; $page <= $totalPages; $page++) {
                $cacheKey = "user_mentor_by_mentor_{$mentorId}_page_{$page}";
                if (Cache::has($cacheKey)) {
                    Cache::forget($cacheKey);
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil membersihkan cache user magang by mentor', 
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membersihkan cache user magang by mentor',
                'error' => $th->getMessage()
            ], 500);
        }
        
    }
 

    //kepegawaian && admin
     public function userMagang(){
        try { 
            $cacheKey = 'user_magang_page_' .request('page', 1);
            $users = Cache::remember($cacheKey, now()->addMinutes(10), function() { 
                return User::whereIn('id', function($query){
                   $query->select('user_id')->from('berkas')->where('status_berkas', 'terima');
                })->where('role', 'user')->paginate(20);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function clearCacheUser($role){
        try {
               // Validasi role
            if (!in_array($role, ['magang', 'mentor', 'kepegawaian'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Role tidak valid',
                ], 400);
            }

            // hapus jika role users user sudah diganti magang
            if ($role === 'magang') {
                $role = 'user';
            }

            $query = User::where('role', $role);
            $totalPages = max(1, ceil($query->count() / 20)); // Paksa minimal 1 halaman
            //  Log::info("Total pages untuk {$role}: {$totalPages}");
            for ($page = 1; $page <= $totalPages; $page++) {
                // Log::info("Query dieksekusi user_{$role}_page_{$page}");
                Cache::forget("user_magang_page_{$page}");
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil membersihkan cache user magang', 
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membersihkan cache user magang',
                'error' => $th->getMessage()
            ], 500);
        }
        
    } 
    
     public function userMentor(){
        try { 
            $cacheKey = 'user_mentor_page_' .request('page', 1);
            $users = Cache::remember($cacheKey, now()->addMinutes(10), function() {
                    return User::where('role', 'mentor')->paginate(20);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
     public function userKepegawaian(){
        try { 
            $cacheKey = 'user_kepegawaian_page_' .request('page', 1);
            $users = Cache::remember($cacheKey, now()->addMinutes(10), function() {
                return User::where('role', 'kepegawaian')->paginate(20);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // admin , kepegawaian, mentor
    public function showUserMagang(string $id)
    {
        try {
            // $cacheKey = "user_{$id}";
            $user = User::with(['berkas' => function ($query) {
                $query->select('id', 'user_id', 'master_sekolah_universitas_id')
                      ->with(['masterSekolah' => function ($subQuery) {
                          $subQuery->select('id', 'nama_sekolah_universitas', 'jurusan_sekolah','fakultas_universitas','program_studi_universitas');
                      }]); }])->find($id);
            // $user = Cache::remember($cacheKey, 600, function () use ($id) {
            //     return User::find($id);
            // });
            $presensi = Presensi::where('user_id', $user->id)->whereHas('laporanHarian', function ($query) {
                $query->where('status', 'approved');
            })->with(['laporanHarian:id,status,presensi_id'])->orderBy('tanggal', 'desc')->get();
            $laporanAkhir = LaporanAkhir::where('user_id', $user->id)->first();
            $data = [
                'user' => $user,
                'presensi' => $presensi,
                'laporanAkhir' => $laporanAkhir,
            ];
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $data
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
