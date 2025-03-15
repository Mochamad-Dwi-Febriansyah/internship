<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Signature extends Model
{ 
    use HasUuids;
    protected $table = 'signatures';
    protected $fillable = [
        'user_id',
        'signature', 
    ];
}
