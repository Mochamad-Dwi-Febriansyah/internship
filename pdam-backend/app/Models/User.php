<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasUuids, HasApiTokens, HasFactory, Notifiable, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_depan',
        'nama_belakang',
        'nisn_npm_nim_npp',
        'bagian',
        'tanggal_lahir',
        'jenis_kelamin',
        'nomor_hp',
        'email',
        'password',
        'alamat',
        'kabupaten_kota',
        'kecamatan',
        'kelurahan_desa',
        'provinsi',
        'kode_pos',
        'foto',
        'role',
        'status'
    ];

    public function toSearchableArray(): array
    {
        return [ 
            'nama_depan' => $this->nama_depan,
            'nama_belakang' => $this->nama_belakang,
            'nisn_npm_nim_npp' => $this->nisn_npm_nim_npp,
            'bagian' => $this->bagian,
            'tanggal_lahir' => $this->tanggal_lahir, 
            'nomor_hp' => $this->nomor_hp,
            'email' => $this->email,  
        ];
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    } 

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function berkas()
    {
        return $this->hasOne(Berkas::class, 'user_id');
    } 
      // Mutator untuk nama depan
      public function setNamaDepanAttribute($value)
      {
          $this->attributes['nama_depan'] = ucfirst(strtolower($value));
      }
  
      // Mutator untuk nama belakang
      public function setNamaBelakangAttribute($value)
      {
          $this->attributes['nama_belakang'] = ucfirst(strtolower($value));
      } 
    


}
