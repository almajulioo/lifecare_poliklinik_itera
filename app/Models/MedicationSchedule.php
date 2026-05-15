<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicationSchedule extends Model
{
    use HasFactory;
    /**
     * Tabel yang digunakan untuk model
     */
    protected $table = 'medication_schedules';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'user_id',
        'medicine_id',
        'start_date',
        'end_date',
        'time',
        'frequency',
        'duration_days',
        'source',
        'source_type',
        'is_active',
    ];

    /**
     * Atribut yang harus di-cast
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi: MedicationSchedule milik satu User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi: MedicationSchedule menggunakan satu Medicine
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    /**
     * Relasi: MedicationSchedule memiliki banyak MedicationLog
     */
    public function logs(): HasMany
    {
        return $this->hasMany(MedicationLog::class, 'medication_schedule_id');
    }

    /**
     * Relasi: MedicationSchedule memiliki banyak MedicationNotification
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(MedicationNotification::class, 'medication_schedule_id');
    }

    /**
     * Scope: Hanya jadwal aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Hanya jadwal nonaktif
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: Jadwal berdasarkan sumber
     */
    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope: Jadwal ADMIN (resep - prescriptions)
     */
    public function scopeAdminSchedules($query)
    {
        return $query->where(function ($q) {
            $q->where('source_type', 'ADMIN')
              ->orWhere('source', 'resep')
              ->orWhereNull('source_type');
        });
    }

    /**
     * Scope: Jadwal PATIENT (mandiri - personal medications)
     */
    public function scopePatientSchedules($query)
    {
        return $query->where('source_type', 'PATIENT')
                     ->where('source', 'mandiri');
    }

    /**
     * Scope: Jadwal berdasarkan source_type
     */
    public function scopeBySourceType($query, $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }
}