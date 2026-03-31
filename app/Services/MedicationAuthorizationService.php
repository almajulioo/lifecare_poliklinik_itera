<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin;
use App\Models\Medicine;
use App\Models\MedicationSchedule;

/**
 * Service untuk mengelola otorisasi dan permission checking
 * untuk sistem Medication Management dengan role-based access control
 */
class MedicationAuthorizationService
{
    /**
     * Check apakah user adalah ADMIN
     */
    public function isAdmin($user): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Check apakah user adalah PATIENT
     */
    public function isPatient($user): bool
    {
        return $user instanceof User;
    }

    /**
     * Check apakah user dapat mengedit medicine
     * ADMIN bisa edit semua, PATIENT hanya bisa edit milik mereka sendiri
     */
    public function canEditMedicine($user, Medicine $medicine): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        // PATIENT hanya bisa edit medicine milik mereka sendiri dengan source_type PATIENT
        if ($this->isPatient($user)) {
            return $medicine->user_id === $user->id && $medicine->source_type === 'PATIENT';
        }

        return false;
    }

    /**
     * Check apakah user dapat menghapus medicine
     * ADMIN bisa hapus semua, PATIENT hanya bisa hapus milik mereka sendiri
     */
    public function canDeleteMedicine($user, Medicine $medicine): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->isPatient($user)) {
            // PATIENT tidak boleh hapus medicine ADMIN
            if ($medicine->source_type === 'ADMIN') {
                return false;
            }
            // PATIENT hanya bisa hapus medicine milik mereka sendiri
            return $medicine->user_id === $user->id && $medicine->source_type === 'PATIENT';
        }

        return false;
    }

    /**
     * Check apakah user dapat membuat medication schedule
     * Hanya ADMIN yang bisa membuat schedule (prescriptions)
     */
    public function canCreateSchedule($user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Check apakah user dapat melihat schedule
     * ADMIN lihat semua, PATIENT lihat milik mereka saja
     */
    public function canViewSchedule($user, MedicationSchedule $schedule): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->isPatient($user)) {
            return $schedule->user_id === $user->id;
        }

        return false;
    }

    /**
     * Check apakah user dapat mengedit schedule
     * ADMIN bisa edit semua, PATIENT tidak boleh edit ADMIN prescriptions
     */
    public function canEditSchedule($user, MedicationSchedule $schedule): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->isPatient($user)) {
            // PATIENT tidak boleh edit ADMIN prescriptions
            if ($schedule->source_type === 'ADMIN' || $schedule->source === 'resep') {
                return false;
            }

        // PATIENT hanya bisa edit schedule milik mereka sendiri
        return $schedule->user_id === $user->id && $schedule->source_type === 'PATIENT';
    }

    /**
     * Check apakah user dapat menghapus schedule
     * ADMIN bisa hapus semua, PATIENT tidak boleh hapus ADMIN prescriptions
     * PATIENT hanya bisa soft-delete dengan mengubah is_active = false
     */
    public function canDeleteSchedule($user, MedicationSchedule $schedule): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        if ($this->isPatient($user)) {
            // PATIENT tidak boleh delete ADMIN prescriptions
            if ($schedule->source_type === 'ADMIN' || $schedule->source === 'resep') {
                return false;
            }

        // PATIENT hanya bisa delete schedule milik mereka sendiri
        return $schedule->user_id === $user->id && $schedule->source_type === 'PATIENT';
    }

    /**
     * Check apakah user dapat confirm medication intake
     * ADMIN confirm untuk siapa saja, PATIENT hanya confirm milik mereka
     */
    public function canConfirmIntake($user, MedicationSchedule $schedule): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        // PATIENT hanya bisa confirm medication mereka sendiri
        if ($this->isPatient($user)) {
            return $schedule->user_id === $user->id;
        }

        return false;
    }

    /**
     * Check apakah user dapat melihat medicine
     * ADMIN lihat semua, PATIENT lihat medicine ADMIN + milik mereka sendiri
     */
    public function canViewMedicine($user, Medicine $medicine): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        // PATIENT bisa lihat medicine ADMIN (prescribed) atau milik mereka sendiri
        if ($this->isPatient($user)) {
            return $medicine->source_type === 'ADMIN' || $medicine->user_id === $user->id;
        }

        return false;
    }

    /**
     * Get list of medicines yang bisa dilihat user
     */
    public function getVisibleMedicines(User $user)
    {
        if ($this->isAdmin($user)) {
            return Medicine::all();
        }

        // PATIENT lihat ADMIN medicines + milik mereka
        return Medicine::where(function ($query) use ($user) {
            $query->where('source_type', 'ADMIN')
                  ->orWhere('user_id', $user->id);
        })->get();
    }

    /**
     * Get list of editable medicines untuk user
     */
    public function getEditableMedicines(User $user)
    {
        if ($this->isAdmin($user)) {
            return Medicine::all();
        }

        // PATIENT hanya bisa edit milik mereka sendiri dengan source_type PATIENT
        return Medicine::where('user_id', $user->id)
                      ->where('source_type', 'PATIENT')
                      ->get();
    }
}
