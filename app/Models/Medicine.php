<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medicine extends Model
{
    use HasFactory;
    protected $fillable = ['name','dose','unit','notes', 'user_id', 'source_type'];

    public function schedules()
    {
        return $this->hasMany(MedicationSchedule::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk mendapatkan obat admin
     */
    public function scopeAdminMedicines($query)
    {
        return $query->where(function ($q) {
            $q->where('source_type', 'ADMIN')
              ->orWhere('source', 'resep')
              ->orWhereNull('source_type');
        })->whereNull('user_id');
    }

    /**
     * Scope untuk mendapatkan obat berdasarkan source_type
     */
    public function scopeBySourceType($query, $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * Scope untuk mendapatkan obat user spesifik
     */
    public function scopeUserMedicines($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk mendapatkan semua obat yang tersedia untuk user (admin + user sendiri)
     */
    public function scopeAvailableForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id') // Obat admin
              ->orWhere('user_id', $userId); // Atau obat user sendiri
        });
    }
}