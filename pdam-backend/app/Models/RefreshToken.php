<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    use HasUuids;
    protected $table = 'refresh_tokens';
    protected $fillable = [
        'user_id',
        'token',
        'expires_at'
    ];
}
