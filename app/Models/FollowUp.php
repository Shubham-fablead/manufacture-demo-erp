<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Lead;

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'customer_id',
        'lead_id',
        'assigned_to',
        'created_by',
        'purpose',
        'comment',
        'priority',
        'status',
        'follow_up_datetime',
        'isDeleted',
    ];

    protected $casts = [
        'follow_up_datetime' => 'datetime',
        'isDeleted' => 'boolean',
    ];

    protected $appends = [
        'formatted_follow_up_datetime',
        'subject_name',
    ];

    /**
     * Get the customer that owns the follow up.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id')
            ->where('role', 'customer')
            ->where('isDeleted', 0);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id')
            ->where('isDeleted', 0);
    }

    /**
     * Get the assigned staff user for the follow up.
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to')
            ->where('isDeleted', 0);
    }

    /**
     * Get the user who created the follow up.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')
            ->where('isDeleted', 0);
    }

    /**
     * Scope to get only active records (not soft deleted).
     */
    public function scopeActive($query)
    {
        return $query->where('isDeleted', 0);
    }

    /**
     * Format the follow up datetime for display.
     */
    public function getFormattedFollowUpDatetimeAttribute()
    {
        return $this->follow_up_datetime ? $this->follow_up_datetime->format('d-m-Y h:i A') : null;
    }

    public function getSubjectNameAttribute()
    {
        return $this->lead?->name
            ?? $this->customer?->name
            ?? 'N/A';
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Pending' => '#ff9f43',
            'Rescheduled' => '#009ef7',
            'Completed' => '#10b981',
            'Cancelled' => '#ef4444',
            default => '#6b7280'
        };
    }

    /**
     * Get priority color for UI.
     */
    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'High' => '#ef4444',
            'Medium' => '#ff9f43',
            'Low' => '#10b981',
            default => '#6b7280'
        };
    }

    /**
     * Boot method for model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set a default branch_id only when it is not already provided by controller logic.
            if (empty($model->branch_id) && auth()->check()) {
                $user = auth()->user();
                $model->branch_id = $user->branch_id ?? $user->id;
            }
            $model->created_at = Carbon::now('Asia/Kolkata');
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });
    }
}
