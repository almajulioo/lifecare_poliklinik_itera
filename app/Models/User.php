<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\MedicationSchedule;
use App\Models\MedicationLog;
use App\Models\ClinicPatient;
use App\Notifications\ResetPassword;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_user',
        'name',
        'email',
        'nim',
        'prodi',
        'age',
        'gender',
        'phone',
        'password',
        'fcm_token',
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

    /**
     * Kirim notification reset password custom
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
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

    /**
     * Specifies the user's FCM token
     *
     * @return string|array
     */
    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
    }

    /**
     * Bootstrap the model and its traits.
     * 
     * Otomatis membuat ClinicPatient entry saat User baru dibuat
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            // Buat ClinicPatient otomatis saat User dibuat
            ClinicPatient::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'identity_number' => $user->nim ?? null,
                'category' => $user->role_user, // mahasiswa atau pegawai
                'email' => $user->email,
                'status' => 'aktif',
            ]);
        });
    }
}