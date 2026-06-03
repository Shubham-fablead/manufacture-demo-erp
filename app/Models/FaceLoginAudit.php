<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceLoginAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'branch_id',
        'status',
        'method',
        'distance',
        'confidence',
        'ip_address',
        'user_agent',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
