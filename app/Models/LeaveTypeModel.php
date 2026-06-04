<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveTypeModel extends Model
{
    use HasFactory;

    protected $table = 'leave_type';
    protected $primaryKey = 'id';

    protected $fillable = [
        'leave_type',
        'number_of_leaves',
        'allow_half_day',
    ];

    public $timestamps = true;

    protected $casts = [
        'allow_half_day' => 'boolean',
        'number_of_leaves' => 'integer',
    ];
}
