<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicationNotification extends Model
{
    use HasFactory;

    /**
     * Tabel yang digunakan untuk model
     */
    protected $table = 'medication_notifications';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'medication_schedule_id',
        'onesignal_id',
        'reminder_type',
        'scheduled_at',
        'status',
    ];

    /**
     * Atribut yang harus di-cast
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    /**
     * Relasi: MedicationNotification milik satu MedicationSchedule
     */
    public function medicationSchedule(): BelongsTo
    {
        return $this->belongsTo(MedicationSchedule::class, 'medication_schedule_id');
    }
}
