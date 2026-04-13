<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\MedicationSchedule;
use App\Models\MedicationLog;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_user',
        'name',
        'email',
        'nim',
        'prodi',
        'password',
        'notification_preferences',
        'timezone',
        'medical_conditions',
        'notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_preferences' => 'array',
            'medical_conditions' => 'array',
        ];
    }

    public function medicationSchedules()
    {
        return $this->hasMany(MedicationSchedule::class);
    }

    public function medicationLogs()
    {
        return $this->hasMany(MedicationLog::class);
    }

    /**
     * Relasi ke ClinicPatient (optional)
     * User bisa linked ke patient di poliklinik, atau tidak
     */
    public function clinicPatient()
    {
        return $this->hasOne(ClinicPatient::class);
    }
}