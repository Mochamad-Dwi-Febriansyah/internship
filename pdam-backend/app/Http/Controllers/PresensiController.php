<?php

namespace App\Http\Controllers;

use App\Models\LaporanAkhir;
use App\Models\LaporanHarian;
use App\Models\Pengajuan;
use App\Models\Presensi;
use App\Models\Signature;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

use function App\Providers\logActivity;

class PresensiController extends Controller
{
    //user presensi
    public function index(){
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
            $page = request('page', 1);
            // $cacheKey = "presensi_user_{$userId}_page_{$page}";
            $presensi =   Presensi::where('user_id', $userId)->with(['user:id,nama_depan,nama_belakang', 'laporanHarian:id,status,presensi_id'])->orderBy('tanggal', 'desc')->paginate(20);

            // $presensi = Cache::remember($cacheKey, 600, function () use ($userId) {
            //     return Presensi::where('user_id', $userId)->with(['user:id,nama_depan,nama_belakang'])->with('laporanHarians')->paginate(20);
            // });

            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $presensi
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function presensi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'tanggal' => 'required|date|date_format:Y-m-d',
            'waktu' => 'required|date_format:H:i:s', // Waktu dipakai untuk check-in/check-out
            'foto' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {

            $tanggal = $request->tanggal;

            // Cek apakah user sudah melakukan presensi pada hari ini
            $presensi = Presensi::where('user_id', $request->user_id)
                ->whereDate('tanggal', $tanggal)
                ->first();

             $fotoPath = null;

            // Simpan foto
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $fileName = time() . '_' . ($presensi ? 'checkout' : 'checkin') . '_' . $foto->getClientOriginalName();
                $fotoPath = $foto->storeAs('presensi', $fileName, 'public');
                $fotoPath = str_replace('public/', '', $fotoPath);
            }

            $user = JWTAuth::parseToken()->authenticate();

            if (!$presensi) {
                // Belum ada presensi hari ini → Simpan sebagai Check-In
                $presensi = Presensi::create([
                    'user_id' => $request->user_id,
                    'tanggal' => $tanggal,
                    'waktu_check_in' => $request->waktu,
                    'foto_check_in' => $fotoPath,
                    'latitude_check_in' => $request->latitude,
                    'longitude_check_in' => $request->longitude,
                ]);

                $message = 'Presensi Check-In berhasil';
            } else {
                // Sudah ada presensi → Simpan sebagai Check-Out
                $presensi->update([
                    'waktu_check_out' => $request->waktu,
                    'foto_check_out' => $fotoPath,
                    'latitude_check_out' => $request->latitude,
                    'longitude_check_out' => $request->longitude,
                ]);

                $message = 'Presensi Check-Out berhasil';
            }

            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'create', 'Presensi', $presensi->id, null);

              Cache::forget("presensi_user_{$user->id}");

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Presensi berhasil',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan presensi',
                'error' => $th->getMessage()
            ], 500);
        }

    }
    public function presensiHariIni(){
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
            $today = now()->setTimezone('Asia/Jakarta')->toDateString();
            $presensi =  Presensi::where('user_id', $userId)->whereDate('tanggal', $today)->with(['user:id,nama_depan,nama_belakang'])->with('laporanHarian')->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Data user berhasil diambil',
                'data' => $presensi
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // user pengajuan
    public function getPengajuan(){
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
            $pengajuan  = Pengajuan::where('user_id', $userId)->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan berhasil diambil',
                'data' => $pengajuan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    } 

    public function pengajuan(Request $request){

        $validator = Validator::make($request->all(), [
            'keterangan' => 'nullable|string|max:255',
            'tanggal' => 'date',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();
            Pengajuan::create([
                'user_id' => $user->id,
                'keterangan' => $request->keterangan,
                'tanggal' => $request->tanggal,
            ]);

            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'create', 'Pengajuan', null, null);
            Cache::forget("presensi_user_{$user->id}");
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan berhasil disimpan',
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan pengajuan',
                'error' => $th->getMessage()
            ], 500);
        }
    } 

    //user laporan
    public function laporan(Request $request){
        $validator = Validator::make($request->all(), [
            'presensi_id' => 'required|uuid|exists:presensis,id',
            'title' => 'required|string|min:1',
            'report' => 'required', 
            'result' => 'required', 
        ]); 
        if($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try { 
            $user = JWTAuth::parseToken()->authenticate();

            $laporan = LaporanHarian::create([
                'user_id' => $user->id,
                'presensi_id' => $request->presensi_id,
                'title' => $request->title,
                'report' => $request->report,
                'result' => $request->result, 
                'status' => 'pending' 
            ]);

            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'create', 'LaporanHarian', $laporan->id, null);
            Cache::forget("presensi_user_{$user->id}");
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan berhasil disimpan',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan laporan',
                'error' => $th->getMessage()
            ], 500);
        }
    } 

    public function getLaporan(){
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
            $laporan  = LaporanHarian::where('user_id', $userId)->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan berhasil diambil',
                'data' => $laporan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function getLaporanById($id){
        try {
            $userId = JWTAuth::parseToken()->authenticate()->id;
            // $laporan  = LaporanHarian::where('id', $id)->where('user_id', $userId)->first();
            $laporan  = LaporanHarian::where('id', $id)->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan berhasil diambil',
                'data' => $laporan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function updateLaporanById(Request $request, $id)
    {   
        // $request->headers->set('Content-Type', 'multipart/form-data'); 
        // return response()->json(['sa'=> $request->input('judul')]);
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'report' => 'required', 
            'result' => 'required', 
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();
    
            $laporan = LaporanHarian::where('id', $id)->where('user_id', $user->id)->first();
    
            if (!$laporan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Laporan tidak ditemukan atau tidak memiliki izin untuk mengedit'
                ], 404);
            }
    
            // Update laporan
            $laporan->update([ 
                'title' => $request->title,
                'report' => $request->report,
                'result' => $request->result, 
                'status' => 'pending' 
            ]);
    
            $nama = $user->nama_depan . ' ' . $user->nama_belakang;
            logActivity($user->id, $nama, 'update', 'LaporanHarian', $laporan->id, null);
            Cache::forget("presensi_user_{$user->id}");
    
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan berhasil diperbarui',
                'laporan' => $laporan
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
    
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui laporan',
                'error' => $th->getMessage()
            ], 500);
        }
    } 

    public function exportLogBook(){
        try {
            $user = JWTAuth::parseToken()->authenticate()->load(['berkas.masterSekolah', 'berkas.mentor']);
            $logBookGenerate = Presensi::where('user_id', $user->id)->with(['laporanHarian:id,status,presensi_id,title,report,result,status,rejection_note,verified_by_id'])->orderBy('tanggal', 'desc')->get();
            $signature = Signature::select('signature')->where('user_id', $user->berkas->mentor->id)->first();

            $statusApprovedTtdMentor = $signature;  

            foreach ($logBookGenerate as $logBook) {
                if (optional($logBook->laporanHarian)->status != 'approved') {
                    $statusApprovedTtdMentor = null;
                    break;
                }
            }


            $logBook = [
                'user' => [ 
                    'nama' => $user->nama_depan.' '.$user->nama_belakang,  
                    'nisn_npm_nim_npp' => $user->nisn_npm_nim_npp,   
                    'nama_sekolah_universitas' => $user->berkas->masterSekolah->nama_sekolah_universitas,
                    'jurusan_sekolah' => $user->berkas->masterSekolah->jurusan_sekolah,
                    'fakultas_universitas' => $user->berkas->masterSekolah->fakultas_universitas,
                    'program_studi_universitas' => $user->berkas->masterSekolah->program_studi_universitas,  
                    'tanggal_mulai' => Carbon::parse($user->berkas->tanggal_mulai)->translatedFormat('d F Y'),
                    'tanggal_selesai' => Carbon::parse($user->berkas->tanggal_selesai)->translatedFormat('d F Y'),
                    'mentor_nama' => $user->berkas->mentor->nama_depan.' '.$user->berkas->mentor->nama_belakang,
                    'mentor_nisn_npm_nim_npp' => $user->berkas->mentor->nisn_npm_nim_npp,                    
                    'mentor_tanda_tangan' => $statusApprovedTtdMentor,                    
                ],
                'logBook' => $logBookGenerate,
            ];
          
            $pdf = Pdf::loadView('pdf.logbook', ['result' => $logBook]); 

            return $pdf->download(); 
            // return response()->json([
            //     'status' => 'error',
            //     'message' => 'Terjadi kesalahan saat mengambil data',
            //     'data' => $logBook
            // ], 200); 
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    
    //mentor

    public function getPengajuanMentor(){
        try {
            $mentorId = JWTAuth::parseToken()->authenticate()->id;
            // $pengajuan  = Pengajuan::where('user_id', $userId)->get();

            $pengajuan = Pengajuan::whereIn('user_id', function ($query) use ($mentorId) {
                $query->select('user_id')
                      ->from('berkas')
                      ->where('mentor_id', $mentorId);
            })->get();

            // dd($pengajuan);
            return response()->json([
                'status' => 'success',
                'message' => 'Data pengajuan berhasil diambil',
                'data' => $pengajuan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getLaporanHarianByMentor(){
        try {
            $mentorId = JWTAuth::parseToken()->authenticate()->id;
            $page = request('page', 1);
            $perPage = 20;
            // $cacheKey = "laporan_harian_mentor_{$mentorId}_page_{$page}";

            $laporan =  LaporanHarian::whereIn('user_id', function ($query) use ($mentorId) {
                $query->select('user_id')
                      ->from('berkas')
                      ->where('mentor_id', $mentorId)->where('status_berkas', 'terima');
            })->with(['user:id,nama_depan,nama_belakang,email','presensi:id,tanggal'])->orderBy('created_at','desc')->where('status', '!=', 'approved')->get();
      
        //     $laporan = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($mentorId) {
        //      return  LaporanHarian::whereIn('user_id', function ($query) use ($mentorId) {
        //         $query->select('user_id')
        //               ->from('berkas')
        //               ->where('mentor_id', $mentorId)->where('status_berkas', 'terima');
        //     })->with(['user:id,nama_depan,nama_belakang,email','presensi:id,tanggal'])->orderBy('created_at','desc')->where('status', '!=', 'approved')->get();
        // });

        $groupedLaporan = $laporan->groupBy('user_id')->map(function ($items) {
            $user = $items->first()->user;
            return [
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama_depan . ' ' . $user->nama_belakang,
                    'email' => $user->email,
                ],
                'laporan' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'report' => $item->report,
                        'result' => $item->result,
                        'status' => $item->status,
                        'rejection_note' => $item->rejection_note,
                        'tanggal_presensi' => $item->presensi->tanggal ?? null,
                        'created_at' => $item->created_at,
                        ];
                })
            ];
        })->values();
        $total = $groupedLaporan->count(); // Hitung total user yang memiliki laporan
        $paginatedData = new LengthAwarePaginator(
            $groupedLaporan->forPage($page, $perPage), // Ambil subset data untuk halaman tertentu
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan berhasil diambil',
                'data' => $paginatedData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
 
    public function clearCacheLaporanHarianByMentor(){
        try {   
            
            $mentorId = JWTAuth::parseToken()->authenticate()->id;

            // Hitung total halaman dengan lebih efisien
            $totalData = Cache::remember("laporan_harian_mentor_{$mentorId}", now()->addMinutes(10), function () use ($mentorId) {
                return LaporanHarian::whereIn('user_id', function ($query) use ($mentorId) {
                    $query->select('user_id')
                          ->from('berkas')
                          ->where('mentor_id', $mentorId)->where('status_berkas', 'terima');
                })->count();
            });
    
            $totalPages = max(1, ceil($totalData / 20));
            //  Log::info("Total pages untuk {$role}: {$totalPages}");
            for ($page = 1; $page <= $totalPages; $page++) {
                $cacheKey = "laporan_harian_mentor_{$mentorId}_page_{$page}";
                if (Cache::has($cacheKey)) {
                    Cache::forget($cacheKey);
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil membersihkan cache laporan harian by mentor', 
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membersihkan cache laporan harian by mentor',
                'error' => $th->getMessage()
            ], 500);
        }
        
    }
 
    
    public function validasiPengajuan(Request $request, $id){
        $pengajuan = Pengajuan::find($id);
        if (!$pengajuan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }

        $oldData = $pengajuan->toArray();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,pending,rejected'
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {


            $pengajuan->update([
                'status' => $request->status,
            ]);
            $newData = $pengajuan->toArray();

            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'update', 'Pengajuan', $pengajuan->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            Cache::forget("presensi_user_{$user->id}");
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan berhasil divalidasi',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat validasi pengajuan',
                'error' => $th->getMessage()
            ], 500);
        }
    }
 
    public function validasiLaporan(Request $request){
        // Validasi input
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,pending,rejected',
            'ids' => 'required|array',
            'ids.*' => 'exists:laporan_harians,id', // Pastikan setiap ID ada di database
            'rejection_note' => 'nullable|string'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
        try {
            // Ambil data laporan yang akan diperbarui
            $laporanList = LaporanHarian::whereIn('id', $request->ids)->get();
    
            if ($laporanList->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada laporan yang ditemukan'
                ], 404);
            }
    
            $oldData = $laporanList->toArray(); // Simpan data lama sebelum update
    
            // Update semua laporan
            LaporanHarian::whereIn('id', $request->ids)->update([
                'status' => $request->status,
                'rejection_note' => $request->rejection_note
            ]);
    
            $newData = LaporanHarian::whereIn('id', $request->ids)->get()->toArray();
    
            // Catat aktivitas
            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan . ' ' . $user->nama_belakang;
            foreach ($request->ids as $id) {
                $laporan = LaporanHarian::find($id);
                if (!$laporan) {
                    continue; // Skip jika laporan tidak ditemukan
                }
    
                $oldData = $laporan->toArray();
    
                // Update status laporan
                $laporan->update([
                    'status' => $request->status,
                    'rejection_note' => $request->rejection_note ?? null,
                ]);
    
                $newData = $laporan->toArray();
    
                // Logging aktivitas per laporan
                logActivity($user->id, $nama, 'update', 'LaporanHarian', $id, [
                    'old' => $oldData,
                    'new' => $newData,
                ]);
            }
    
            // Hapus cache presensi user
            Cache::forget("presensi_user_{$user->id}");
    
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Laporan berhasil divalidasi',
            ], 201);
    
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Terjadi kesalahan saat validasi laporan',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    
    
}
