<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Tabel yang digunakan untuk model
     */
    protected $table = 'admins';

    /**
     * Atribut yang dapat diisi secara massal
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * Atribut yang harus disembunyikan
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atribut yang harus di-cast
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}