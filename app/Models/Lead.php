<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'assigned_to',
        'created_by',
        'updated_by',
        'name',
        'email',
        'phone',
        'whatsapp',
        'address',
        'image',
        'company_name',
        'sic_code',
        'lead_source',
        'lead_status',
        'comment',
        'converted_customer_id',
        'isDeleted',
    ];

    protected $casts = [
        'isDeleted' => 'boolean',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to')->where('isDeleted', 0);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->where('isDeleted', 0);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by')->where('isDeleted', 0);
    }

    public function branch()
    {
        return $this->belongsTo(User::class, 'branch_id')->where('role', 'admin')->where('isDeleted', 0);
    }

    public function statusHistories()
    {
        return $this->hasMany(LeadStatusHistory::class, 'lead_id')->orderByDesc('id');
    }

    public function convertedCustomer()
    {
        return $this->belongsTo(User::class, 'converted_customer_id')->where('role', 'customer')->where('isDeleted', 0);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('isDeleted')
              ->orWhere('isDeleted', 0)
              ->orWhere('isDeleted', '');
        });
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->branch_id) && auth()->check()) {
                $user = auth()->user();
                $model->branch_id = $user->branch_id ?? $user->id;
            }

            $now = Carbon::now('Asia/Kolkata');
            $model->created_at = $now;
            $model->updated_at = $now;
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });
    }
}
