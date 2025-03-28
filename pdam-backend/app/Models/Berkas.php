<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Berkas extends Model
{
    use HasUuids;
    protected $table = 'berkas';
    protected $fillable = [
        'user_id',
        'mentor_id',
        'master_sekolah_universitas_id',
        'foto_identitas',
        'surat_permohonan',
        'cv_riwayat_hidup',
        'surat_diterima',
        'status_berkas',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected static function booted()
    {
        static::creating(function ($berkas) {
            $berkas->nomor_registrasi = 'BR-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -4));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    } 
    // Relasi ke MasterSekolah
    public function masterSekolah()
    {
        return $this->belongsTo(MasterSekolahUniversitas::class, 'master_sekolah_universitas_id');
    }
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id', 'id');
    }
}
