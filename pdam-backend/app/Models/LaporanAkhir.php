<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class LaporanAkhir extends Model
{
    use HasUuids, Searchable;
    protected $table = 'laporan_akhirs';
    protected $fillable = [
        'user_id',
        'berkas_id', 
        'master_sekolah_universitas_id', 
        'title', 
        'report', 
        'assessment_report_file',
        'final_report_file',
        'photo',
        'video',
        'certificate',
        'verified_by_mentor_id',
        'status_verifikasi_mentor',
        'rejection_note_mentor',
        'verified_by_kepegawian_id',
        'status_verifikasi_kepegawaian',
        'rejection_note_kepegawaian',
    ];
    public function toSearchableArray(): array
    {
        return [  
            'title' => $this->title,
            'report' => $this->report,
            'rejection_note_mentor' => $this->rejection_note_mentor,
            'rejection_note_kepegawaian' => $this->rejection_note_kepegawaian,
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function masterSekolah()
    {
        return $this->belongsTo(MasterSekolahUniversitas::class, 'master_sekolah_universitas_id');
    }
      // Mutator untuk title
      public function setTitleAttribute($value)
      {
          $this->attributes['title'] = ucfirst(strtolower($value));
      }
  
      // Mutator untuk report
      public function setReportAttribute($value)
      {
          $this->attributes['report'] = ucfirst(strtolower($value));
      }  
      // LaporanAkhir.php
public function historis()
{
    return $this->hasMany(LaporanAkhirHistori::class);
}
// LaporanAkhir.php
public function berkas()
{
    return $this->hasOne(Berkas::class, 'user_id', 'user_id');
}

}
