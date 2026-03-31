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
