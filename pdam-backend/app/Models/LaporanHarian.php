<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class LaporanHarian extends Model
{
    use HasUuids, Searchable;
    protected $table = 'laporan_harians';
    protected $fillable = [
        'user_id',
        'presensi_id', 
        'title', 
        'report', 
        'result', 
        'status',
        'rejection_note', 
    ];
    public function toSearchableArray(): array
    {
        return [  
            'title' => $this->title,
            'report' => $this->report,
            'result' => $this->result,
            'rejection_note' => $this->rejection_note,
        ];
    }
    public function presensi()
    {
        return $this->belongsTo(Presensi::class, 'presensi_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
     // Mutator untuk result
     public function setResultAttribute($value)
     {
         $this->attributes['result'] = ucfirst(strtolower($value));
     }
}
