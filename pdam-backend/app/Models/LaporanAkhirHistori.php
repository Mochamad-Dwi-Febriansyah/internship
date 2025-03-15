<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LaporanAkhirHistori extends Model
{
    use HasUuids;
    protected $table = 'laporan_akhir_historis';
    protected $fillable = [ 
        'user_id',
        'laporan_akhir_id',
        'title',
        'report',
        'assessment_report_file',
        'final_report_file',
        'photo',
        'video',
        'status',
        'rejection_note',
        'version_number',
    ];
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = ucfirst(strtolower($value));
    }

    // Mutator untuk report
    public function setReportAttribute($value)
    {
        $this->attributes['report'] = ucfirst(strtolower($value));
    }  
    // LaporanAkhirHistori.php
public function user()
{
    return $this->belongsTo(User::class);
}

public function laporanAkhir()
{
    return $this->belongsTo(LaporanAkhir::class);
}

}
