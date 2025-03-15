<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Presensi extends Model
{
    use HasUuids, Searchable;
    protected $table = 'presensis';
    protected $fillable = [
        'user_id', 
       'tanggal',
        'waktu_check_in',
        'waktu_check_out',
        'foto_check_in',
        'foto_check_out', 
        'latitude_check_in',
        'latitude_check_out',
        'longitude_check_in',
        'longitude_check_out',
        'status'
    ];
    public function toSearchableArray(): array
    {
        return [  
            'tanggal' => $this->tanggal,
            'waktu_check_in' => $this->waktu_check_in,
            'waktu_check_out' => $this->waktu_check_out,
        ];
    }

    public function laporanHarian()
    {
        return $this->hasOne(LaporanHarian::class, 'presensi_id', 'id');
    }
public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    } 
}
