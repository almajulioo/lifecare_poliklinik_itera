<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationLog extends Model
{
    use HasFactory;
    protected $table = 'notification_logs';

    protected $fillable = [
        'user_id',
        'medication_schedule_id',
        'scheduled_time',
        'sent_at',
        'status',
        'snooze_minutes',
        'notification_type',
        'device_info',
        'reminder_number',
        'second_reminder_at',
        'second_reminder_sent_at',
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
        'sent_at' => 'datetime',
        'second_reminder_at' => 'datetime',
        'second_reminder_sent_at' => 'datetime',
    ];

    /**
     * Relasi: Log notification milik satu User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi: Log notification terkait satu MedicationSchedule
     */
    public function medicationSchedule(): BelongsTo
    {
        return $this->belongsTo(MedicationSchedule::class, 'medication_schedule_id');
    }
}
