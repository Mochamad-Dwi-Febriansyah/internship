<?php

namespace App\Http\Controllers;

use App\Models\LaporanAkhir;
use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

use function App\Providers\logActivity;

class SignatureController extends Controller
{
    public function index(){
        try { 
            $user = JWTAuth::parseToken()->authenticate();  
            $tandaTangan =  Signature::where('user_id', $user->id)->first(); 
            return response()->json([
                'status' => 'success',
                'message' => 'Data tanda tangan berhasil diambil',
                'data' => $tandaTangan
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
    
        $tandaTanganValidator = Validator::make($request->all(), [
            'signature' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);
    
        if ($tandaTanganValidator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $tandaTanganValidator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
        try {
            // Cek apakah user sudah memiliki tanda tangan sebelumnya
            $tandaTangan = Signature::where('user_id', $user->id)->first();
    
            // Jika sudah ada, hapus file lama
            if ($tandaTangan && $tandaTangan->signature && Storage::disk('public')->exists($tandaTangan->signature)) {
                Storage::disk('public')->delete($tandaTangan->signature);
            }
    
            // Simpan file baru dengan format yang sama
            $tandaTanganPath = $request->file('signature')->storeAs(
                'signatures',
                'SC_' . time() . '_' . Str::random(10) . '.' . $request->file('signature')->getClientOriginalExtension(),
                'public'
            );
    
            if ($tandaTangan) {
                // Jika sudah ada, update data lama
                $tandaTangan->update([
                    'signature' => $tandaTanganPath, 
                ]);
    
                logActivity($user->id, $user->nama_depan . ' ' . $user->nama_belakang, 'update', 'Signature', $tandaTangan->id, null);
    
                $message = 'Berhasil memperbarui tanda tangan';
            } else {
                // Jika belum ada, buat data baru
                $tandaTangan = Signature::create([
                    'user_id' => $user->id,
                    'signature' => $tandaTanganPath,
                ]);
    
                logActivity($user->id, $user->nama_depan . ' ' . $user->nama_belakang, 'create', 'Signature', $tandaTangan->id, null);
    
                $message = 'Berhasil menambahkan tanda tangan';
            }
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => $tandaTangan
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
    

    public function show($id){
        {
            try {  
                $tandaTangan =   Signature::find($id);
                
                if (!$tandaTangan) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Tanda tangan tidak ditemukan'
                    ], 404); 
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data tanda tangan berhasil diambil',
                    'data' => $tandaTangan
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

    public function update(Request $request, $id)
    {
        $user = JWTAuth::parseToken()->authenticate();
    
        $validator = Validator::make($request->all(), [
            'signature' => 'nullable|image|mimes:jpeg,jpg,png|max:2048', // Wajib upload tanda tangan baru
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
            $tandaTangan = Signature::where('id', $id)->where('user_id', $user->id)->first();
    
            if (!$tandaTangan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tanda tangan tidak ditemukan atau bukan milik Anda'
                ], 404);
            }
    
            // Jika ada file baru, hapus file lama
            if ($request->hasFile('signature')) { 
                if ($tandaTangan->signature && Storage::disk('public')->exists($tandaTangan->signature)) {
                    Storage::disk('public')->delete($tandaTangan->signature);
                }
    
                // Simpan file baru
                $tandaTanganPath = $request->file('signature')->storeAs(
                    'signatures',
                    'SC_' . time() . '_' . Str::random(10) . '.' . $request->file('signature')->getClientOriginalExtension(),
                    'public'
                );
    
                // Update tanda tangan dengan file baru
                $tandaTangan->update([
                    'signature' => $tandaTanganPath, 
                ]);
            } 
    
            logActivity($user->id, $user->nama_depan . ' ' . $user->nama_belakang, 'update', 'Signature', $tandaTangan->id, null);
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Tanda tangan berhasil diperbarui',
                'data' => $tandaTangan
            ], 200);
    
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    
    

    public function destroy($id){
        try {
            $tandaTangan = Signature::find($id);
            if (!$tandaTangan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tanda tangan tidak ditemukan'
                ], 404);
            }
            $oldData = $tandaTangan->toArray(); 
            $tandaTangan->delete(); 

            $user = JWTAuth::parseToken()->authenticate();
            $nama = $user->nama_depan. ' ' .$user->nama_belakang;
            logActivity($user->id, $nama, 'delete', 'Signature', $user->id, [
                'old' => $oldData,
            ]); 
        return response()->json([
            'status' => 'success',
            'message' => 'Data tanda tangan berhasil dihapus', 
        ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data',
                'error' => $th->getMessage()
            ], 500);
        } 
  
    }
}
