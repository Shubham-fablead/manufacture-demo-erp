<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'Scheduled';
    public const STATUS_COMPLETED = 'Completed';
    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'branch_id',
        'customer_id',
        'assigned_to',
        'created_by',
        'meeting_title',
        'meeting_type',
        'agenda',
        'address',
        'scheduled_on',
        'status',
        'isDeleted',
    ];

    protected $casts = [
        'scheduled_on' => 'datetime',
        'isDeleted' => 'boolean',
    ];

    protected $appends = [
        'formatted_scheduled_on',
    ];

    /**
     * Get the customer that owns the meeting.
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id')
            ->where('role', 'customer')
            ->where('isDeleted', 0);
    }

    /**
     * Get the assigned staff user for the meeting.
     */
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to')
            ->where('isDeleted', 0);
    }

    /**
     * Get the user who created the meeting.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')
            ->where('isDeleted', 0);
    }

    /**
     * Get the branch for the meeting.
     */
    public function branch()
    {
        return $this->belongsTo(User::class, 'branch_id')
            ->where('role', 'admin')
            ->where('isDeleted', 0);
    }

    /**
     * Scope to get only active records (not soft deleted).
     * Handles NULL, empty string, and 0 as "not deleted".
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('isDeleted')
              ->orWhere('isDeleted', 0)
              ->orWhere('isDeleted', '');
        });
    }

    /**
     * Scope to get scheduled meetings only.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    /**
     * Format the scheduled datetime for display.
     */
    public function getFormattedScheduledOnAttribute()
    {
        return $this->scheduled_on ? $this->scheduled_on->format('d-m-Y h:i A') : null;
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_SCHEDULED => '#009ef7',
            self::STATUS_COMPLETED => '#10b981',
            self::STATUS_CANCELLED => '#ef4444',
            default => '#6b7280'
        };
    }

    public function reminderLogs()
    {
        return $this->hasMany(MeetingReminderLog::class, 'meeting_id');
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
