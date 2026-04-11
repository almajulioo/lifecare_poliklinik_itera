<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicationLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'medication_schedule_id',
        'taken_at',
        'status',
        'note',
        'offline_synced',
        'offline_synced_at',
        'sync_metadata',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(MedicationSchedule::class, 'medication_schedule_id');
    }

    public function medicationSchedule()
    {
        return $this->belongsTo(MedicationSchedule::class, 'medication_schedule_id');
    }
}