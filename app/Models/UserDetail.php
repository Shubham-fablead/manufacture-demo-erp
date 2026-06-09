<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'city',
        'gst_number',
        'pan_number',

        'country',
        'department_id',
        'designation_id',
        'joining_date',
        'shift_time',
        'working_location',
        'salary',
        'face_photo',
        'isDeleted',
        'created_at',
        'updated_at',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Kolkata');
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(DesignationModel::class, 'designation_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id', 'id');
    }
}
