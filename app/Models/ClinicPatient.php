<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicPatient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'identity_number',
        'category',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke User (optional)
     * Pasien bisa ada tanpa akun aplikasi
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check apakah pasien menggunakan aplikasi
     */
    public function isAppUser(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Get status badge color
     */
    public function getStatusColor(): string
    {
        return $this->status === 'aktif' ? 'green' : 'gray';
    }

    /**
     * Check apakah pasien memiliki jadwal minum obat aktif
     * Digunakan untuk auto-determine status pasien
     */
    public function hasActiveMedicationSchedule(): bool
    {
        if (!$this->user_id) {
            return false; // Pasien tidak punya akun aplikasi, tidak bisa punya jadwal
        }

        // Cek apakah user punya jadwal minum obat yang masih berlaku
        $activeSchedule = MedicationSchedule::where('user_id', $this->user_id)
            ->where('is_active', true)
            ->where('start_date', '<=', now()->toDateString())
            ->where(function ($query) {
                // end_date sudah lewat atau belum ada end_date
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->first();

        return $activeSchedule !== null;
    }

    /**
     * Determine status otomatis berdasarkan jadwal minum obat
     * - Jika ada jadwal aktif → "aktif"
     * - Jika tidak ada jadwal → "tidak_aktif"
     */
    public function getAutomaticStatus(): string
    {
        return $this->hasActiveMedicationSchedule() ? 'aktif' : 'tidak_aktif';
    }

    /**
     * Update status pasien jika berubah berdasarkan jadwal
     * Digunakan setiap kali ada perubahan jadwal atau hari berubah
     */
    public function syncStatusWithSchedule(): void
    {
        $newStatus = $this->getAutomaticStatus();
        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);
        }
    }

    /**
     * Get category label
     */
    public function getCategoryLabel(): string
    {
        $labels = [
            'mahasiswa' => 'Mahasiswa',
            'pegawai' => 'Pegawai',
            'umum' => 'Umum',
        ];
        return $labels[$this->category] ?? $this->category;
    }
}
