<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Models\Berkas;
use App\Models\RefreshToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

use function App\Providers\logActivity;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            // Log::info('Login request received from:', ['user-agent' => $request->header('User-Agent')]);
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            } 

            // Cek kredensial user
            if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email atau password salah'
                ], 401);
            }

             
            $authUser = Auth::user();

            $user = $authUser->role === 'user'
                ? User::where('id', $authUser->id)
                    ->select('id', 'nama_depan', 'nama_belakang', 'email', 'nisn_npm_nim_npp', 'role', 'foto')
                    ->with(['berkas' => function ($query) {
                        $query->select('id', 'user_id', 'mentor_id'); 
                    }, 'berkas.mentor' => function ($query) {
                        $query->select('id', 'nama_depan', 'nama_belakang', 'email');
                    }])
                    ->first()
                : $authUser;
        

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 401);
            }
            // return response()->json(['message' => $user]);
            $isBlocked = false;
            // $mentor= [];
            $durasi_magang = [];
            // Cek apakah user memiliki berkas dengan status 'approved'
            if ($user->role === 'user') {
                $berkasApproved = Berkas::firstWhere([
                    'user_id' => $user->id,
                    'status_berkas' => 'terima'
                ]); 
                $durasi_magang['tanggal_mulai'] = $berkasApproved->tanggal_mulai;
                $durasi_magang['tanggal_selesai'] = $berkasApproved->tanggal_selesai;

                // if($berkasApproved){
                //     $mentor = $berkasApproved->mentor_id;
                //     return $mentor;
                // }

                if (!$berkasApproved) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Berkas belum disetujui'
                    ], 403);
                }
                $isBlocked = !$berkasApproved || !$berkasApproved->mentor_id;
            }

            // Cek apakah user masih "inactive"
            if ($user->status === 'inactive') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akun belum aktif. Silakan hubungi admin.'
                ], 403);
            }

            $refreshToken = bin2hex(random_bytes(40));
            RefreshToken::updateOrCreate(
                ['user_id' => $user->id],
                ['token' => $refreshToken, 'expires_at' => Carbon::now()->addDays(7)]
            );



            // $cookie = cookie('refresh_token', $refreshToken, 7 * 24 * 60, '/',null, true, true);
            $cookie = cookie('refresh_token', $refreshToken, 7 * 24 * 60, '/', null, true, true, false, 'None');

            $customClaims = [
                'nama_depan' => $user->nama_depan,
                'nama_belakang' => $user->nama_belakang,
                'email' => $user->email,
                'role' => $user->role,
            ];
            $token = JWTAuth::claims($customClaims)->attempt($request->only('email', 'password'));

            

            return response()->json([
                'status' => 'success',
                'message' => 'Login berhasil',
                'user' => [
                    'id' => $user->id,
                    'nama_depan' => $user->nama_depan,
                    'nama_belakang' => $user->nama_belakang,
                    'email' => $user->email,
                    'nisn_npm_nim_npp' => $user->nisn_npm_nim_npp,
                    'role' => $user->role,
                    'foto' => $user->foto,
                    'is_blocked' => $isBlocked,
                    'durasi_magang' => $durasi_magang,
                    'mentor' => $user->berkas->mentor ?? null,  
                ],
                'token' => $token,
            ], 200)->cookie($cookie);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat token',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat login',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function profile(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);  // Kode status 404, karena data tidak ditemukan
            }
            // dd($user);
            $oldData = $user->toArray();
            $userValidator = Validator::make($request->all(), [
                'nisn_npm_nim_npp' => 'sometimes|max:20',
                'tanggal_lahir' => 'sometimes|date',
                'nama_depan' => 'sometimes|required',
                'nama_belakang' => 'sometimes|nullable',
                'jenis_kelamin' => 'sometimes|required|in:male,female',
                'nomor_hp' => ['sometimes', 'required', 'regex:/^\+?[\d\s\(\)-]+$/', Rule::unique('users')->ignore($user->id)],
                'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
                'password' => 'sometimes|nullable',
                'alamat' => 'sometimes|required',
                'kabupaten_kota' => 'sometimes|required',
                'provinsi' => 'sometimes|required',
                'kode_pos' => 'sometimes|required',
                'foto' => 'nullable|image|mimes:jpg,png,jpeg|max:2048', 
                'role' => 'sometimes|required|in:admin,user',
            ]);
            
            // $userValidator = Validator::make($request->all(), [
            //     'nisn_npm_nim_npp' => 'max:20',
            //     'tanggal_lahir' => 'required|date',
            //     'nama_depan' => 'required',
            //     'nama_belakang' => 'nullable',
            //     'jenis_kelamin' => 'required|in:male,female',
            //     'nomor_hp' => 'required',
            //     Rule::unique('users')->ignore($user->id),
            //     'regex:/^\+?[\d\s\(\)-]+$/',
            //     'email' => 'required|email',
            //     Rule::unique('users')->ignore($user->id),
            //     'password' => 'nullable',
            //     'alamat' => 'required',
            //     'kabupaten_kota' => 'required',
            //     'provinsi' => 'required',
            //     'kode_pos' => 'required',
            //     'role' => 'required|in:admin,user',

            // ]);
            if ($userValidator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $userValidator->errors()
                ], 422);
            }
            $data = $request->only([
                'nisn_npm_nim_npp',
                'tanggal_lahir',
                'nama_depan',
                'nama_belakang',
                'jenis_kelamin',
                'nomor_hp',
                'email',
                'alamat',
                'kabupaten_kota',
                'provinsi',
                'kecamatan',
                'kelurahan_desa',
                'foto',
                'kode_pos',
            ]);
            // Cek apakah ada file foto yang diunggah
            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($user->foto) {
                    Storage::disk('public')->delete($user->foto);
                }

                // Simpan foto baru dengan nama unik
                $fotoPath = $request->file('foto')
                    ->storeAs('user_photos', 'PL_' . time() . '_' . Str::random(10) . '.' . $request->file('foto')->getClientOriginalExtension(), 'public');

                $data['foto'] = $fotoPath;
            } else {
                $data['foto'] = $user->foto;
            }
            // Jika password diisi, hash terlebih dahulu
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }
            $user->update($data);
            $newData = $user->toArray();
            $nama = $user->nama_depan . ' ' . $user->nama_belakang;

            logActivity($user->id, $nama, 'update', 'User', $user->id, [
                'old' => $oldData,
                'new' => $newData,
            ]);
            DB::commit();
            if ($user) {
                return response()->json(['status' => 'success', 'message' => 'Data user berhasil diperbarui']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'User tidak ditemukan'], 404);
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        try {
            // Ambil user dari token
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User berhasil ditemukan',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data user',
                'error' => $th->getMessage()
            ], 500);
        }
    } 

    public function logout(Request $request)
    {
        try {
            // Ambil token dari Authorization header
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token tidak ditemukan'
                ], 400);
            }

            // Invalidate JWT Token
            JWTAuth::invalidate($token);

            // Hapus refresh token dari cookie & database
            $refreshToken = $request->cookie('refresh_token');
            if ($refreshToken) {
                DB::table('refresh_tokens')->where('token', $refreshToken)->delete();
            }

            // Hapus refresh token dari cookie
            $cookie = cookie('refresh_token', '', -1);

            return response()->json([
                'status' => 'success',
                'message' => 'Logout berhasil'
            ], 200)->cookie($cookie);
        } catch (JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal logout: Token tidak valid atau sudah expired',
                'error' => $e->getMessage()
            ], 401);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat logout',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function refreshToken(Request $request)
    {
        // return response()->json(['message' => $request]);
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['error' => 'Refresh token missing'], 403);
        }

        $tokenRecord = RefreshToken::where('token', $refreshToken)->first();

        if (!$tokenRecord || $tokenRecord->expires_at < now()) {
            return response()->json(['error' => 'Invalid or expired refresh token'], 401);
        }

        $user = User::find($tokenRecord->user_id);
        $newAccessToken = JWTAuth::fromUser($user);

        $newRefreshToken = bin2hex(random_bytes(40));

        RefreshToken::where('user_id', $user->id)->update([
            'token' => $newRefreshToken,
            'expires_at' => now()->addDays(7)
        ]);

        $cookie = cookie('refresh_token', $newRefreshToken, 7 * 24 * 60, null, null, false, true);

        return response()->json([
            'status' => 'success',
            'message' => 'Token refreshed',
            'token' => $newAccessToken
        ], 200)->cookie($cookie);
    }

    public function forgotPassword(Request $request)
    {
        // return response()->json(['re'=> $request->all()]);
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'Email tidak terdaftar di sistem.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        // Kirim email ke user
        Mail::to($user->email)->send(new ResetPasswordMail($token));

        return response()->json([
            'status' => 'success',
            'message' => 'Email reset password berhasil dikirim'
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $reset = DB::table('password_reset_tokens')->where('token', $request->token)->first();

        if (!$reset) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid atau kadaluarsa.'
            ], 400);
        }

        $user = User::where('email', $reset->email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan.'
            ], 404);
        }

        // Update password
        $user->update(['password' => Hash::make($request->password)]);

        // Hapus token
        DB::table('password_reset_tokens')->where('email', $reset->email)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil diperbarui. Silakan login kembali.'
        ], 200);
    }

}
