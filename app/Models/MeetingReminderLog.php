<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingReminderLog extends Model
{
    use HasFactory;

    public const QUEUE_PENDING = 'pending';
    public const QUEUE_QUEUED = 'queued';
    public const QUEUE_PROCESSING = 'processing';
    public const QUEUE_COMPLETED = 'completed';
    public const QUEUE_FAILED = 'failed';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'meeting_id',
        'branch_id',
        'reminder_at',
        'queued_at',
        'processed_at',
        'queue_status',
        'email_status',
        'whatsapp_status',
        'email_sent_at',
        'whatsapp_sent_at',
        'email_recipient',
        'whatsapp_recipient',
        'twilio_message_sid',
        'email_error',
        'whatsapp_error',
        'attempts',
    ];

    protected $casts = [
        'reminder_at' => 'datetime',
        'queued_at' => 'datetime',
        'processed_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'whatsapp_sent_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }
}

