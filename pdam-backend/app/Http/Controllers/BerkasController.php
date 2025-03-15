<?php

namespace App\Http\Controllers;

use App\Mail\MailSendCredentialsLogin;
use App\Mail\MailSendNomorRegistrasi;
use App\Models\Berkas;
use App\Models\MasterSekolahUniversitas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

use function App\Providers\logActivity;

class BerkasController extends Controller
{
    public function ajuanBerkas(Request $request)
    {
        $userValidator = Validator::make($request->all(), [
            'nisn_npm_nim_npp' => 'max:20',
            'tanggal_lahir' => 'required|date',
            'nama_depan' => 'required',
            'nama_belakang' => 'nullable',
            'jenis_kelamin' => 'required|in:male,female',
            'nomor_hp' => 'required|regex:/^\+?[\d\s\(\)-]+$/',
            'email' => 'required|email|unique:users,email',
            'alamat' => 'required',
            'kode_pos' => 'required', 
            'provinsi' => 'required',
            'kabupaten_kota' => 'required',
            'kecamatan' => 'required',
            'kelurahan_desa' => 'required',
            
        ]); 

        if($userValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $userValidator->errors()
            ], 422);
        } 

        $sekolahValidator = Validator::make($request->all(), [
            'nama_sekolah_universitas' => 'required|max:100',
            'jurusan_sekolah' => 'nullable|max:100',
            'fakultas_universitas' => 'nullable|max:100',
            'program_studi_universitas' => 'nullable|max:100',
            'alamat_sekolah_universitas' => 'required|max:255',
            'kode_pos_sekolah_universitas' => 'required|max:10',
            'provinsi_sekolah_universitas' => 'required|max:100',
            'kabupaten_kota_sekolah_universitas' => 'required|max:100',
            'kecamatan_sekolah_universitas' => 'required|max:100',
            'kelurahan_desa_sekolah_universitas' => 'required|max:100',
            'nomor_telp_sekolah_universitas' => 'nullable|regex:/^\+?[\d\s\(\)-]+$/',
            'email_sekolah_universitas' => 'nullable|email',
        ]);

        if ($sekolahValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal pada data Master Sekolah/Universitas',
                'errors' => $sekolahValidator->errors()
            ], 422);
        };

        $berkasValidator = Validator::make($request->all(), [
            'foto_identitas' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'surat_permohonan' => 'required|mimes:pdf,doc,docx|max:2048', 
            'surat_diterima' => 'nullable|mimes:pdf,doc,docx|max:2048', 
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
        ]);

        if ($berkasValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal pada berkas',
                'errors' => $berkasValidator->errors()
            ], 422);
        }
        // dd($request->all());
        DB::beginTransaction();

        try {
            
            $masterSekolah = MasterSekolahUniversitas::firstOrCreate(
                ['email_sekolah_universitas' => $request->email_sekolah_universitas,
                'jurusan_sekolah' => $request->jurusan_sekolah,
                'fakultas_universitas' => $request->fakultas_universitas,
                'program_studi_universitas' => $request->program_studi_universitas,
                ],
                $request->only([
                    'nama_sekolah_universitas',
                    'alamat_sekolah_universitas',
                    'kode_pos_sekolah_universitas',
                    'provinsi_sekolah_universitas',
                    'kabupaten_kota_sekolah_universitas',
                    'kecamatan_sekolah_universitas',
                    'kelurahan_desa_sekolah_universitas',
                    'nomor_telp_sekolah_universitas',
                    'email_sekolah_universitas',
                ])
            );
            logActivity(null, null, 'create', 'MasterSekolahUniversitas', $masterSekolah->id, null);
    
            $user = User::create([
                'nisn_npm_nim_npp' => $request->nisn_npm_nim_npp,
                'tanggal_lahir' => $request->tanggal_lahir,
                'nama_depan' => $request->nama_depan,
                'nama_belakang' => $request->nama_belakang,
                'jenis_kelamin' => $request->jenis_kelamin,
                'nomor_hp' => $request->nomor_hp,
                'email' => $request->email,
                // 'password' => Hash::make($request->password),
                'alamat' => $request->alamat,
                'kode_pos' => $request->kode_pos,
                'provinsi' => $request->provinsi,
                'kecamatan' => $request->kecamatan,
                'kelurahan_desa' => $request->kelurahan_desa,
                'kabupaten_kota' => $request->kabupaten_kota,
            ]); 
            // $token = JWTAuth::fromUser($user); 
            
            logActivity(null, null, 'create', 'User', $user->id, null);
         

            if (!Storage::exists('public/berkas')) {
                Storage::makeDirectory('public/berkas');
            }

            // SIMPAN BERKAS 
            $fotoIdentitasPath = $request->file('foto_identitas')->store('berkas', 'public');
            $suratPermohonanPath = $request->file('surat_permohonan')->store('berkas', 'public');
            $suratDiterimaPath = $request->hasFile('surat_diterima') ? $request->file('surat_diterima')->store('berkas', 'public') : null;
    
            // HAPUS PREFIX 'public/' UNTUK URL AKSES
            $fotoIdentitasPath = str_replace('public/', '', $fotoIdentitasPath);
            $suratPermohonanPath = str_replace('public/', '', $suratPermohonanPath);
            $suratDiterimaPath = $suratDiterimaPath ? str_replace('public/', '', $suratDiterimaPath) : null;
 
             // Menyimpan data Berkas
             $berkas = Berkas::create([
                 'user_id' => $user->id,
                 'master_sekolah_universitas_id' => $masterSekolah->id,
                 'foto_identitas' => $fotoIdentitasPath,
                 'surat_permohonan' => $suratPermohonanPath, 
                 'surat_diterima' => $suratDiterimaPath, 
                 'tanggal_mulai' => $request->tanggal_mulai,
                 'tanggal_selesai' => $request->tanggal_selesai,
             ]);
 
            Mail::to($request->email)->send(new MailSendNomorRegistrasi($berkas->nomor_registrasi));
             
            logActivity(null, $user->id, 'create', 'Berkas', $berkas->id, null);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil menambahkan data',
                'data' => [
                    // 'user' => $user,
                    // 'master_sekolah' => $masterSekolah,
                    'berkas_id' => $berkas->id,
                    'nomor_berkas' => $berkas->nomor_registrasi
                ],
                // 'token' => $token
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

    public function cekBerkas($nomor_registrasi){
        try {
            if (!preg_match('/^BR-\d{6}-[A-Z0-9]+$/', $nomor_registrasi)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Format nomor registrasi tidak valid'
                ], 400);
            }

            $berkas = Berkas::where('nomor_registrasi', $nomor_registrasi)
                            ->join('users', 'berkas.user_id', '=', 'users.id')
                            ->select(  
                                'berkas.nomor_registrasi',
                                'berkas.status_berkas',
                            )->first();

            if (!$berkas) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan'
                ], 404);
            } 
            return response()->json([
                'status' => 'success',
                'message' => 'Data berkas berhasil ditemukan',
                'data' => $berkas
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mendapatkan data',
                'error' => $th->getMessage()
            ], 500);
        }
       
    }  

    public function updateThisMentorStatusBerkas(Request $request, $user_id){
        $berkas = Berkas::where('user_id', $user_id)->first();
        if (!$berkas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Berkas tidak ditemukan'
            ], 404); 
        }
        $oldData = $berkas->toArray(); 
        $berkasValidator = Validator::make($request->all(), [ 
            // 'mentor_id' => 'required',  
            'status_berkas' => 'required',  
        ]); 
        if($berkasValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $berkasValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            $berkas->update([  
                'mentor_id' => $request->mentor_id ?? null,
                'status_berkas' => $request->status_berkas
            ]);
            $newData = $berkas->toArray();

            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang; 

            logActivity($user->id, $nama, 'update', 'Berkas', $berkas->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            $userBerkas = $berkas->user;
            if ($request->status_berkas === 'terima' && $userBerkas) { 
                $defaultPassword = Str::random(8); // Buat password acak
                $hashedPassword = Hash::make($defaultPassword); // Hash sebelum menyimpan

                // Update password di database
                $userBerkas->update([
                    'password' => $hashedPassword,
                    'status' => 'active',
                ]);

                Mail::to($userBerkas->email)->send(new MailSendCredentialsLogin($userBerkas->email, $defaultPassword));
            }
            DB::commit(); 
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengupdate data',  
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

    public function suratTerimaPengajuanBerkas(Request $request, $user_id){
        $berkas = Berkas::where('user_id', $user_id)->first();
        if (!$berkas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Berkas tidak ditemukan'
            ], 404); 
        }
        if ($berkas->status_berkas != 'terima') {
            return response()->json([
                'status' => 'error',
                'message' => 'Berkas belum disetujui'
            ], 404); 
        }
        $suratTerimaValidator = Validator::make($request->all(), [  
            // 'status_berkas' => 'required',  
            // 'status_magang' => 'required|in:mahasiswa,siswa',  
            'nama' => 'required',
            'jurusan' => 'nullable',
            'program_studi_universitas' => 'nullable',
            'nisn_npm_nim_npp' => 'required',
            'tanggalSurat' => 'required',
            'nomor_surat' => 'required',
            'sifat' => 'required',
            'lampiran' => 'required',
            'kepada' => 'required',
            'alamat_kepada' => 'required',
            'tanggal_kepada' => 'required',
            'tanggalMulai' => 'required',
            'tanggalSelesai' => 'required',
        ]); 
        if($suratTerimaValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $suratTerimaValidator->errors()
            ], 422);
        } 
        DB::beginTransaction();
        try {
        $data = $request->all();

        // Ubah/format tanggal surat
        $tanggalFields = ['tanggalSurat', 'tanggalMulai', 'tanggalSelesai', 'tanggal_kepada'];

        // 3. Loop tiap field tanggal
        foreach ($tanggalFields as $field) {
            if (!empty($request->$field)) {
                // Cek apakah input mengandung hari (misal: "Rabu, 12 Maret 2025")
                if (strpos($request->$field, ',') !== false) {
                    // Jika ada hari, ambil bagian setelah koma
                    $tanggal = trim(explode(',', $request->$field)[1]);
                } else {
                    // Jika tidak ada hari, gunakan langsung
                    $tanggal = trim($request->$field); // contoh: "2025-12-02"
                }
        
                // Jika formatnya yyyy-mm-dd (dari input type="date"), kita parse langsung
                if (preg_match('/\d{4}-\d{2}-\d{2}/', $tanggal)) {
                    $carbonDate = Carbon::parse($tanggal);
                } else {
                    // Jika formatnya "12 Maret 2025", pastikan bulan diubah ke English untuk parsing
                    $bulanIndonesia = [
                        'Januari' => 'January',
                        'Februari' => 'February',
                        'Maret' => 'March',
                        'April' => 'April',
                        'Mei' => 'May',
                        'Juni' => 'June',
                        'Juli' => 'July',
                        'Agustus' => 'August',
                        'September' => 'September',
                        'Oktober' => 'October',
                        'November' => 'November',
                        'Desember' => 'December',
                    ];
                    $tanggalInggris = strtr($tanggal, $bulanIndonesia); // Ubah bulan ke English
                    $carbonDate = Carbon::createFromFormat('d F Y', $tanggalInggris);
                }
        
                // Set locale dan format kembali ke "d F Y"
                Carbon::setLocale('id');
                $data[$field] = $carbonDate->translatedFormat('d F Y'); // contoh: "02 Desember 2025"
            } else {
                $data[$field] = '......'; // Default jika kosong
            }
        }
        
        // Tentukan status berkas
        $data['status_berkas'] = $berkas->status_berkas ?? '......'; // Default jika kosong
        
        // Tentukan status magang (siswa atau mahasiswa)
        // return response()->json(['k'=>empty($berkas->jurusan_sekolah)]);
        if (empty($berkas->jurusan_sekolah)) {
            $data['status_magang'] = 'mahasiswa';
        } elseif (!empty($berkas->fakultas_universitas) && !empty($berkas->program_studi_universitas)) {
            $data['status_magang'] = 'siswa';
        } else {
            $data['status_magang'] = 'tidak diketahui'; // Fallback jika tidak masuk kondisi
        }

        // Generate PDF pakai data yang sudah diupdate
        $pdf = Pdf::loadView('pdf.surat_terima', ['result' => $data]);
        
        // return $pdf->download(); 
        $pdfFileName = 'surat_terima_' . time() . '.pdf';
        // Storage::put('public/berkas/surat_terima' . $pdfFileName, $pdf->output());
        // $suratDiterimaPath = $pdf ? $pdf->storeAs('berkas',$pdfFileName, 'public') : null; 
         // Path folder public
         $path = storage_path('app/public/berkas/' . $pdfFileName); // sesuai storage

         // Pastikan folder 'berkas' ada
         if (!file_exists(storage_path('app/public/berkas'))) {
             mkdir(storage_path('app/public/berkas'), 0777, true);
         }
     
         // Simpan file ke storage/app/public/berkas
         file_put_contents($path, $pdf->output());
     
         // Path untuk disimpan di DB (optional)
         $pathToSaveInDB = 'berkas/' . $pdfFileName; // karena nanti akses via public/berkas/xxx
         $berkas->update([   
            'surat_diterima' => $pathToSaveInDB
        ]); 
           DB::commit(); 
        return response()->json([
            'status' => 'success',
            'message' => 'Surat Terima berhasil dibuat',
            'uploaded_file' => $pathToSaveInDB,
        ]);  
     
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengupdate data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    
    public function  berkasByMagangId(){
        try {  
            $userId = Auth::user()->id; 
            $berkas = Berkas::where('user_id', $userId)->first(); 
            if (!$berkas) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'berkas tidak ditemukan'
                ], 404); 
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data berkas berhasil diambil',
                'data' => $berkas
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }  
 
    }
    
    // kepegawaian
    public function pengajuanBerkas(){
        try {
            // $cacheKey = 'pengajuan_berkas_magang_page_' .request('page', 1);
            $berkasGenerate =   Berkas::with([
                        'user:id,nama_depan,nama_belakang,email,nomor_hp,nisn_npm_nim_npp',
                        'masterSekolah:id,nama_sekolah_universitas,jurusan_sekolah,fakultas_universitas,program_studi_universitas,alamat_sekolah_universitas,kabupaten_kota_sekolah_universitas,provinsi_sekolah_universitas,kode_pos_sekolah_universitas,nomor_telp_sekolah_universitas,email_sekolah_universitas'
                        ])
                    ->select(['id','mentor_id', 'nomor_registrasi','foto_identitas', 'surat_permohonan','tanggal_mulai','tanggal_selesai' ,'status_berkas', 'created_at','user_id', 'master_sekolah_universitas_id'])
                    ->whereNull('surat_diterima') 
                    ->get(); 
                    // return response()->json([
                    //     'status' => 'success',
                    //     'message' => 'Data berkas berhasil diambil',
                    //     'data' => $berkasGenerate
                    // ], 200);
            // $berkasGenerate = Cache::remember($cacheKey, now()->addMinutes(10), function() { 
            //         return Berkas::with([
            //             'user:id,nama_depan,nama_belakang,email,nomor_hp',
            //             'masterSekolah:id,nama_sekolah_universitas,jurusan_sekolah,fakultas_universitas,program_studi_universitas,alamat_sekolah_universitas,kabupaten_kota_sekolah_universitas,provinsi_sekolah_universitas,kode_pos_sekolah_universitas,nomor_telp_sekolah_universitas,email_sekolah_universitas'
            //             ])
            //         ->select(['id','mentor_id', 'nomor_registrasi','foto_identitas', 'surat_permohonan','tanggal_mulai','tanggal_selesai' ,'status_berkas', 'created_at','user_id', 'master_sekolah_universitas_id'])
            //         ->get();
            // }); 

            $berkas = [];
            if($berkasGenerate){
                for($i = 0; $i < count($berkasGenerate); $i++){
                    // if(!$berkasGenerate[$i]->mentor_id){ 
                    //     $berkas[] = $berkasGenerate[$i];
                    // }
                    // if($berkasGenerate[$i]->mentor_id && $berkasGenerate[$i]->status_berkas === 'pending'){ 
                    //     $berkas[] = $berkasGenerate[$i];
                    // }
                    // if($berkasGenerate[$i]->surat_diterima === null){
                    //     $berkas[] = $berkasGenerate[$i];
                    // }
                    if (
                        !$berkasGenerate[$i]->mentor_id ||
                        ($berkasGenerate[$i]->mentor_id && $berkasGenerate[$i]->status_berkas === 'pending') ||
                        $berkasGenerate[$i]->surat_diterima === null
                    ) {
                        $berkas[] = $berkasGenerate[$i];
                    }
                } 
            }

            $perPage = 20; // Misalnya menampilkan 10 item per halaman
            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            // Slice data berkas sesuai dengan halaman aktif dan item per halaman
            $currentItems = array_slice($berkas, ($currentPage - 1) * $perPage, $perPage);

            // Buat instance LengthAwarePaginator
            $paginatedBerkas = new LengthAwarePaginator(
                $currentItems, // Data yang ditampilkan pada halaman ini
                count($berkas), // Total item yang ada
                $perPage, // Item per halaman
                $currentPage, // Halaman aktif
                ['path' => Paginator::resolveCurrentPath()] // Link untuk pagination
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Data berkas berhasil diambil',
                'data' => $paginatedBerkas
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }

    public function toggleStatusPengajuanBerkas(Request $request, $id_berkas){
        $berkas = Berkas::with('user')->find($id_berkas);
        if (!$berkas) {
            return response()->json([
                'status' => 'error',
                'message' => 'Berkas tidak ditemukan'
            ], 404);  // Kode status 404, karena data tidak ditemukan
        }
        $oldData = $berkas->toArray();
        // dd($user);
        $berkasValidator = Validator::make($request->all(), [ 
            'status_berkas' => 'required|in:terima,pending,tolak', 
            
        ]); 
        if($berkasValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $berkasValidator->errors()
            ], 422);
        } 

        DB::beginTransaction();
        try {
            $berkas->update([  
                'status_berkas' => $request->status_berkas
            ]);
            $newData = $berkas->toArray();

            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang; 
            return response()->json(['ds'=> $user]);
            logActivity($user->id, $nama, 'update', 'Berkas', $berkas->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            DB::commit();
 
             
            $totalPages = max(1, ceil(Berkas::count() / 20)); // Paksa minimal 1 halaman
            //  Log::info("Total pages untuk {$role}: {$totalPages}");
            for ($page = 1; $page <= $totalPages; $page++) {
                // Log::info("Query dieksekusi user_{$role}_page_{$page}");
                Cache::forget("pengajuan_berkas_magang_page_{$page}");
            }

            // buat kirim email saat update status berkas
            $userBerkas = $berkas->user;
            if ($request->status_berkas === 'terima' && $userBerkas) { 
                $defaultPassword = Str::random(8); // Buat password acak
                $hashedPassword = Hash::make($defaultPassword); // Hash sebelum menyimpan

                // Update password di database
                $userBerkas->update([
                    'password' => $hashedPassword,
                    'status' => 'active',
                ]);

                Mail::to($userBerkas->email)->send(new MailSendCredentialsLogin($userBerkas->email, $defaultPassword));
            }
    
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil mengupdate status berkas', 
                'status_berkas' => $request->status_berkas
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
    
    public function clearCachePengajuanBerkas(){
        try {  
            $query = Berkas::where('status_berkas', 'pending');
            $totalPages = max(1, ceil($query->count() / 20)); 
            for ($page = 1; $page <= $totalPages; $page++) { 
                Cache::forget("pengajuan_berkas_magang_page_{$page}");
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil membersihkan cache pengajuan berkas', 
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membersihkan cache pengajuan berkas',
                'error' => $th->getMessage()
            ], 500);
        }
        
    }

    // admin && kepegawaian
    public function daftarBerkas()
    {
        try {
            $berkas = Berkas::with(['user', 'masterSekolah'])->paginate(20);
            return response()->json([
                'status' => 'success',
                'message' => 'Data berkas berhasil diambil',
                'data' => $berkas
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }

    public function show(string $id)
    {
        try {
            $berkas = Berkas::find($id);
            if (!$berkas) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Berkas tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data berkas berhasil diambil',
                'data' => $berkas
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        } 
    }

    //mentor
    // public function update_status(Request $request, string $id)
    // {
    //     $berkas = Berkas::with('user')->find($id);
    //     if (!$berkas) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Berkas tidak ditemukan'
    //         ], 404);  
    //     }
    //     $oldData = $berkas->toArray(); 
    //     $berkasValidator = Validator::make($request->all(), [ 
    //         'status_berkas' => 'required|in:terima,pending,tolak',  
            
    //     ]); 
    //     if($berkasValidator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Validasi gagal',
    //             'errors' => $berkasValidator->errors()
    //         ], 422);
    //     } 

    //     DB::beginTransaction();
    //     try {
    //         $berkas->update([ 
    //             'status_berkas' => $request->status_berkas
    //         ]);
    //         $newData = $berkas->toArray(); 
    //         $user = JWTAuth::parseToken()->authenticate();
    //         $nama = $user->nama_depan. ' ' .$user->nama_belakang;

    //         logActivity($berkas->id, $nama, 'update', 'User', $berkas->id, [
    //             'old' => $oldData,
    //             'new' => $newData,
    //         ]);

    //         $userBerkas = $berkas->user;
    //         if ($request->status_berkas === 'terima' && $userBerkas) { 
    //             $defaultPassword = Str::random(8); // Buat password acak
    //             $hashedPassword = Hash::make($defaultPassword); // Hash sebelum menyimpan

    //             // Update password di database
    //             $userBerkas->update([
    //                 'password' => $hashedPassword,
    //                 'status' => 'active',
    //             ]);

    //             Mail::to($userBerkas->email)->send(new MailSendCredentialsLogin($userBerkas->email, $defaultPassword));
    //         }

    //         DB::commit();
    
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Berhasil mengupdate data status',  
    //         ], 201);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan saat mengupdate data',
    //             'error' => $th->getMessage()
    //         ], 500);
    //     }
    // }

}
