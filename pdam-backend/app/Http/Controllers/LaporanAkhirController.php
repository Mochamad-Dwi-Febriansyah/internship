<?php

namespace App\Http\Controllers;

use App\Models\Berkas;
use App\Models\LaporanAkhir;
use App\Models\LaporanAkhirHistori;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cache;

use function App\Providers\logActivity;

class LaporanAkhirController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     // users 
    public function index()
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();
            $page = request('page', 1);
            // $cacheKey = "laporan_akhir_{$user->id}_page_{$page}";

            $laporanAkhir =   LaporanAkhir::where('user_id', $user->id)->paginate(20);
            // $berkas = Berkas::select('tanggal_selesai')->where('user_id', $user->id)->first();
          
            // $laporanAkhir = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user) {
            //  return  LaporanAkhir::where('user_id', $user->id)->paginate(20);
            // });
            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan akhir berhasil diambil',
                // 'tanggal_selesai' => $berkas->tanggal_selesai,
                'data' => $laporanAkhir
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
 
    public function clearCacheLaporanAkhir(){
        try {   
            
            $userId = JWTAuth::parseToken()->authenticate()->id;

            // Hitung total halaman dengan lebih efisien
            $totalData = Cache::remember("laporan_akhir_{$userId}", now()->addMinutes(10), function () use ($userId) {
                return LaporanAkhir::where('user_id', $userId)->count();
            });
    
            $totalPages = max(1, ceil($totalData / 20));
            //  Log::info("Total pages untuk {$role}: {$totalPages}");
            for ($page = 1; $page <= $totalPages; $page++) {
                $cacheKey = "laporan_akhir_{$userId}_page_{$page}";
                if (Cache::has($cacheKey)) {
                    Cache::forget($cacheKey);
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil membersihkan cache laporan akhir', 
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membersihkan cache laporan akhir',
                'error' => $th->getMessage()
            ], 500);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request)
{
    $user = JWTAuth::parseToken()->authenticate();
    $berkas = Berkas::where('user_id', $user->id)->first();

    if (!$berkas) {
        return response()->json([
            'status' => 'error',
            'message' => 'Berkas tidak ditemukan'
        ], 404);
    }

    $laporanAkhirValidator = Validator::make($request->all(), [
        'title' => 'required|string',
        'report' => 'required|string',
        'assessment_report_file' => 'nullable|mimes:pdf,doc,docx|max:2048',
        'final_report_file' => 'nullable|mimes:pdf,doc,docx|max:2048',
        'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        'video' => 'nullable|string',
        'certificate' => 'nullable|mimes:pdf,jpg,png|max:2048',
        'work_certificate' => 'nullable|mimes:pdf,jpg,png|max:2048',
    ]);

    if ($laporanAkhirValidator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validasi gagal',
            'errors' => $laporanAkhirValidator->errors()
        ], 422);
    }

    $tanggalSelesai = Carbon::parse($berkas->tanggal_selesai);
    $hariIni = Carbon::now();
    $sisaHari = $hariIni->diffInDays($tanggalSelesai, false); // false = bisa negatif
    // dd($sisaHari);
    return response()->json(['sa' => $sisaHari]);
    if ($sisaHari < 14) {
        return response()->json([
            'status' => 'error',
            'message' => 'Pengisian laporan akhir hanya diperbolehkan maksimal H-14 sebelum tanggal selesai magang.'
        ], 403); // 403 = Forbidden
    }

    DB::beginTransaction();
    try {
        // Simpan file jika ada
        $assessmentReportPath = $request->hasFile('assessment_report_file') 
        ? $request->file('assessment_report_file')->storeAs('assessment_reports', 'PL_' . time() . '_' . Str::random(10) . '.' . $request->file('assessment_report_file')->getClientOriginalExtension(), 'public')
        : null;
    
        $finalReportPath = $request->hasFile('final_report_file') 
            ? $request->file('final_report_file')->storeAs('final_reports', 'LA_' . time() . '_' . Str::random(10) . '.' . $request->file('final_report_file')->getClientOriginalExtension(), 'public')
            : null;
        
        $photoPath = $request->hasFile('photo') 
            ? $request->file('photo')->storeAs('photos', 'FT_' . time() . '_' . Str::random(10) . '.' . $request->file('photo')->getClientOriginalExtension(), 'public')
            : null;
        
        $certificatePath = $request->hasFile('certificate') 
            ? $request->file('certificate')->storeAs('certificates', 'SC_' . time() . '_' . Str::random(10) . '.' . $request->file('certificate')->getClientOriginalExtension(), 'public')
            : null;
        $workCertificatePath = $request->hasFile('work_certificate') 
            ? $request->file('work_certificate')->storeAs('work_certificate', 'SC_' . time() . '_' . Str::random(10) . '.' . $request->file('work_certificate')->getClientOriginalExtension(), 'public')
            : null;
    

        $laporanAkhir = LaporanAkhir::create([ 
            'user_id' => $user->id,
            'berkas_id' => $berkas->id,
            'master_sekolah_universitas_id' => $berkas->master_sekolah_universitas_id,
            'title' => $request->title,
            'report' => $request->report,
            'assessment_report_file' => $assessmentReportPath,
            'final_report_file' => $finalReportPath,
            'photo' => $photoPath,
            'video' => $request->video,
            'certificate' => $certificatePath,
            'work_certificate' => $workCertificatePath,
            'status_verifikasi_mentor' => 'pending',
            'status_verifikasi_kepegawaian' => 'pending',
        ]);

        // Log aktivitas
        logActivity($user->id, $user->nama_depan . ' ' . $user->nama_belakang, 'create', 'LaporanAkhir', $laporanAkhir->id, null);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menambahkan laporan akhir',
            'data' => $laporanAkhir
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
            $laporanAkhir = LaporanAkhir::with([
                'user:id,nama_depan,nama_belakang,email,nomor_hp',
                'masterSekolah:id,nama_sekolah_universitas,jurusan_sekolah,fakultas_universitas,program_studi_universitas,alamat_sekolah_universitas,kabupaten_kota_sekolah_universitas,provinsi_sekolah_universitas,kode_pos_sekolah_universitas,nomor_telp_sekolah_universitas,email_sekolah_universitas',
               'historis' => function ($query) {
        $query->select('id', 'laporan_akhir_id', 'user_id', 'version_number', 'created_at')
              ->orderByDesc('created_at') ; // ambil user yang revisi
    }
            ])->find($id);
            if (!$laporanAkhir) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'laporan akhir tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan akhir berhasil diambil',
                'data' => $laporanAkhir
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
        $laporanAkhir = LaporanAkhir::find($id);
        if (!$laporanAkhir) {
            return response()->json([
                'status' => 'error',
                'message' => 'Laporan akhir tidak ditemukan'
            ], 404);
        }

        if($laporanAkhir->status_verifikasi_mentor === 'approved' && $laporanAkhir->status_verifikasi_kepegawaian === 'approved'){
            return response()->json([
                'status' => 'error',
                'message' => 'Laporan akhir sudah disetujui dan tidak dapat diubah'
            ], 403);
        }
    
        $oldData = $laporanAkhir->toArray();
    
        $laporanAkhirValidator = Validator::make($request->all(), [
            'title' => 'required',
            'report' => 'required',
            'assessment_report_file' => 'nullable|mimes:pdf,doc,docx|max:2048',
            'final_report_file' => 'nullable|mimes:pdf,doc,docx|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'certificate' => 'nullable|mimes:pdf|max:2048',
            'work_certificate' => 'nullable|mimes:pdf,jpg,png|max:2048',
        ]);
    
        if ($laporanAkhirValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $laporanAkhirValidator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $lastHistory = LaporanAkhirHistori::where('laporan_akhir_id', $laporanAkhir->id)
                ->orderByDesc('version_number')->first();
            $nextVersion = $lastHistory ? $lastHistory->version_number + 1 : 2; // kalau belum ada, mulai dari 2
               // 📝 Simpan riwayat sebelum update
               $laporanAkhirHistori = LaporanAkhirHistori::create([
                'laporan_akhir_id' => $laporanAkhir->id,
                'user_id' => $user->id,
                'title' => $laporanAkhir->title,
                'report' => $laporanAkhir->report,
                'assessment_report_file' => $laporanAkhir->assessment_report_file,
                'final_report_file' => $laporanAkhir->final_report_file,
                'photo' => $laporanAkhir->photo,
                'video' => $laporanAkhir->video,
                'certificate' => $laporanAkhir->certificate,
                'work_certificate' => $laporanAkhir->work_certificate,
                'status' => 'revised',
                'version_number' => $nextVersion,
            ]); 
    
            // Handle file uploads dan hapus file lama jika ada file baru diunggah
            if ($request->hasFile('assessment_report_file')) {
                Storage::disk('public')->delete($laporanAkhir->assessment_report_file);
                $assessmentReportPath = $request->file('assessment_report_file')
                    ->storeAs('assessment_reports', 'PL_' . time() . '_' . Str::random(10) . '.' . $request->file('assessment_report_file')->getClientOriginalExtension(), 'public');
            } else {
                $assessmentReportPath = $laporanAkhir->assessment_report_file;
            }
    
            if ($request->hasFile('final_report_file')) {
                Storage::disk('public')->delete($laporanAkhir->final_report_file);
                $finalReportPath = $request->file('final_report_file')
                    ->storeAs('final_reports', 'LA_' . time() . '_' . Str::random(10) . '.' . $request->file('final_report_file')->getClientOriginalExtension(), 'public');
            } else {
                $finalReportPath = $laporanAkhir->final_report_file;
            }
    
            if ($request->hasFile('photo')) {
                Storage::disk('public')->delete($laporanAkhir->photo);
                $photoPath = $request->file('photo')
                    ->storeAs('photos', 'FT_' . time() . '_' . Str::random(10) . '.' . $request->file('photo')->getClientOriginalExtension(), 'public');
            } else {
                $photoPath = $laporanAkhir->photo;
            }
    
            if ($request->hasFile('certificate')) {
                Storage::disk('public')->delete($laporanAkhir->certificate);
                $certificatePath = $request->file('certificate')
                    ->storeAs('certificates', 'SC_' . time() . '_' . Str::random(10) . '.' . $request->file('certificate')->getClientOriginalExtension(), 'public');
            } else {
                $certificatePath = $laporanAkhir->certificate;
            }
            if ($request->hasFile('work_certificate')) {
                Storage::disk('public')->delete($laporanAkhir->work_certificate);
                $workCertificatePath = $request->file('work_certificate')
                    ->storeAs('work_certificate', 'SC_' . time() . '_' . Str::random(10) . '.' . $request->file('work_certificate')->getClientOriginalExtension(), 'public');
            } else {
                $workCertificatePath = $laporanAkhir->certificate;
            }
            
            // Update data laporan akhir
            $laporanAkhir->update([
                'title' => $request->title,
                'report' => $request->report,
                'assessment_report_file' => $assessmentReportPath,
                'final_report_file' => $finalReportPath,
                'photo' => $photoPath,
                'certificate' => $certificatePath,
                'workCertificatePath' => $workCertificatePath,
                'video' => $request->video,
                'status_verifikasi_mentor' => 'pending',
                'status_verifikasi_kepegawaian' => 'pending',
            ]);
    
            $newData = $laporanAkhir->toArray();
            $nama = $user->nama_depan . ' ' . $user->nama_belakang;
    
            logActivity($user->id, $nama, 'update', 'LaporanAkhir', $laporanAkhir->id, [
                'old' => $oldData,
                'new' => $newData,
            ]); 
            logActivity($user->id, $nama, 'create', 'LaporanAkhirHistori', $laporanAkhirHistori->id, [
                'data' => $laporanAkhirHistori->toArray(),
            ]);
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengupdate data laporan akhir',
                // 'd' => $laporanAkhir
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
            $laporanAkhir = LaporanAkhir::find($id);
            if (!$laporanAkhir) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'laporan akhir tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            $oldData = $laporanAkhir->toArray();
            $laporanAkhir->delete();
            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'delete', 'LaporanAkhir', $laporanAkhir->id, [
                'old' => $oldData,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan akhir berhasil dihapus',  // Mengganti 'diambil' dengan 'dihapus'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    
    public function getLaporanAkhirByMentor(){
        try {
            $mentorId = JWTAuth::parseToken()->authenticate()->id;
            $page = request('page', 1);
            // $cacheKey = "laporan_akhir_mentor_{$mentorId}_page_{$page}";

            $laporan =   LaporanAkhir::whereIn('user_id', function ($query) use ($mentorId) {
                $query->select('user_id')
                      ->from('berkas')
                      ->where('mentor_id', $mentorId)->where('status_berkas', 'terima'); 
            })->with(['user:id,nama_depan,nama_belakang,email'])->orderBy('created_at','desc')->where('status_verifikasi_mentor', '!=', 'approved')->paginate(20);
    
        //     $laporan = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($mentorId) {
        //      return  LaporanAkhir::whereIn('user_id', function ($query) use ($mentorId) {
        //         $query->select('user_id')
        //               ->from('berkas')
        //               ->where('mentor_id', $mentorId)->where('status_berkas', 'terima'); 
        //     })->with(['user:id,nama_depan,nama_belakang,email'])->orderBy('created_at','desc')->where('status_verifikasi_mentor', '!=', 'approved')->paginate(20);
        // });

            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan akhir berhasil diambil',
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

    public function clearCacheLaporanAkhirByMentor() {
        try {   
            $mentorId = JWTAuth::parseToken()->authenticate()->id;
    
            // Hitung total halaman tanpa menyimpan cache (lebih efisien untuk clear cache)
            $totalData = LaporanAkhir::whereIn('user_id', function ($query) use ($mentorId) {
                $query->select('user_id')
                      ->from('berkas')
                      ->where('mentor_id', $mentorId)
                      ->where('status_berkas', 'terima');
            })->count();
    
            $totalPages = max(1, ceil($totalData / 20)); // Pastikan minimal 1 halaman
    
            // Loop untuk menghapus semua cache terkait laporan akhir mentor
            for ($page = 1; $page <= $totalPages; $page++) {
                Cache::forget("laporan_akhir_mentor_{$mentorId}_page_{$page}");
            }
    
            // Hapus cache total laporan akhir mentor
            Cache::forget("laporan_akhir_mentor_{$mentorId}");
    
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil membersihkan cache laporan akhir by mentor',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membersihkan cache laporan akhir by mentor',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    
    public function validasiLaporanAkhirByMentor(Request $request, $id){
        // return response()->json($request->all());
            // dd($request->all());
            $laporan = LaporanAkhir::find($id);
            if (!$laporan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Laporan tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }

            $oldData = $laporan->toArray();

            $validator = Validator::make($request->all(), [
                'status_verifikasi_mentor' => 'required|in:approved,pending,rejected'
            ]);
            // return response()->json(['e' => $request->all()]);

            if($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();
            try {

                $laporan->update([
                    'status_verifikasi_mentor' => $request->status_verifikasi_mentor,
                    'rejection_note_mentor' => $request->rejection_note_mentor,
                ]);
                $newData = $laporan->toArray();

                $user = JWTAuth::parseToken()->authenticate();
                $nama = $user->nama_depan. ' ' .$user->nama_belakang;
                logActivity($user->id, $nama, 'update', 'LaporanAkhir', $laporan->id, [
                    'old' => $oldData,
                    'new' => $newData,
                ]);
                $totalData = LaporanAkhir::where('user_id', $user->id)->count();
                $totalPages = max(1, ceil($totalData / 20));
        
                for ($page = 1; $page <= $totalPages; $page++) {
                    Cache::forget("laporan_akhir_{$user->id}_page_{$page}");
                }
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
    public function showLaporanAkhirByMentor(string $id)
    {
        try {
            $laporanAkhir = LaporanAkhir::with([
                'user:id,nama_depan,nama_belakang,email,nomor_hp',
                'masterSekolah:id,nama_sekolah_universitas,jurusan_sekolah,fakultas_universitas,program_studi_universitas,alamat_sekolah_universitas,kabupaten_kota_sekolah_universitas,provinsi_sekolah_universitas,kode_pos_sekolah_universitas,nomor_telp_sekolah_universitas,email_sekolah_universitas'
                ])->find($id);
            if (!$laporanAkhir) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'laporan akhir tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan akhir berhasil diambil',
                'data' => $laporanAkhir
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
 

    //kepegawawian
    public function getLaporanAkhirByKepegawaian(){
        try {
            $kepegawaianId = JWTAuth::parseToken()->authenticate()->id;
            $page = request('page', 1);
            // $cacheKey = "laporan_akhir_kepegawaian_{$kepegawaianId}_page_{$page}";

            $laporan =   LaporanAkhir::whereIn('user_id', function ($query)   {
                $query->select('user_id')
                      ->from('berkas')
                       ->where('status_berkas', 'terima');
            })->with(['user:id,nama_depan,nama_belakang,email'])->where('status_verifikasi_mentor', 'approved')->where('status_verifikasi_kepegawaian', '!=' ,'approved')->orderBy('created_at','desc')->paginate(20);
   
        //     $laporan = Cache::remember($cacheKey, now()->addMinutes(10), function()  {
        //      return  LaporanAkhir::whereIn('user_id', function ($query)   {
        //         $query->select('user_id')
        //               ->from('berkas')
        //                ->where('status_berkas', 'terima');
        //     })->with(['user:id,nama_depan,nama_belakang,email'])->where('status_verifikasi_mentor', 'approved')->where('status_verifikasi_kepegawaian', '!=' ,'approved')->orderBy('created_at','desc')->paginate(20);
        // });

            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan akhir berhasil diambil',
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
    public function clearCacheLaporanAkhirByKepegawaian() {
        try {   
            $kepegawaianId = JWTAuth::parseToken()->authenticate()->id;
    
            // Hitung total halaman tanpa menyimpan cache (lebih efisien untuk clear cache)
            $totalData = LaporanAkhir::whereIn('user_id', function ($query) {
                $query->select('user_id')
                      ->from('berkas') 
                      ->where('status_berkas', 'terima');
            })->count();
    
            $totalPages = max(1, ceil($totalData / 20)); // Pastikan minimal 1 halaman
    
            // Loop untuk menghapus semua cache terkait laporan akhir kepegawaian
            for ($page = 1; $page <= $totalPages; $page++) {
                Cache::forget("laporan_akhir_kepegawaian_{$kepegawaianId}_page_{$page}");
            }
    
            // Hapus cache total laporan akhir kepegawaian
            Cache::forget("laporan_akhir_kepegawaian_{$kepegawaianId}");
    
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil membersihkan cache laporan akhir by kepegawaian',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membersihkan cache laporan akhir by kepegawaian',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function validasiLaporanAkhirByKepegawaian(Request $request, $id){
        // dd($request->all());
        $laporan = LaporanAkhir::find($id);
        if (!$laporan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Laporan tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }

        $oldData = $laporan->toArray();

        $validator = Validator::make($request->all(), [
            'status_verifikasi_kepegawaian' => 'required|in:approved,pending,rejected'
        ]);
        // return response()->json(['e' => $request->all()]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {

            $laporan->update([
                'status_verifikasi_kepegawaian' => $request->status_verifikasi_kepegawaian,
                'rejection_note_kepegawaian' => $request->rejection_note_kepegawaian,
            ]);
            $newData = $laporan->toArray();

            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'update', 'LaporanAkhir', $laporan->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            $totalData = LaporanAkhir::where('user_id', $user->id)->count();
            $totalPages = max(1, ceil($totalData / 20));
    
            for ($page = 1; $page <= $totalPages; $page++) {
                Cache::forget("laporan_akhir_{$user->id}_page_{$page}");
            }
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

    public function showLaporanAkhirByKepegawaian(string $id)
    {
        try {
            $laporanAkhir = LaporanAkhir::with([
                'user:id,nama_depan,nama_belakang,email,nomor_hp',
                'masterSekolah:id,nama_sekolah_universitas,jurusan_sekolah,fakultas_universitas,program_studi_universitas,alamat_sekolah_universitas,kabupaten_kota_sekolah_universitas,provinsi_sekolah_universitas,kode_pos_sekolah_universitas,nomor_telp_sekolah_universitas,email_sekolah_universitas'
                ])->find($id);
            if (!$laporanAkhir) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'laporan akhir tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data laporan akhir berhasil diambil',
                'data' => $laporanAkhir
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
