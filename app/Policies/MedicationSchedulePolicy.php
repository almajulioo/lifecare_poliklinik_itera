<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use App\Models\MedicationSchedule;

class MedicationSchedulePolicy
{
    /**
     * Determine if user is an admin
     */
    private function isAdmin($user): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Only ADMIN can create medication schedules
     */
    public function create($user): bool
    {
        // Only ADMIN can create schedules
        return $this->isAdmin($user);
    }

    /**
     * Only authorized users can view a schedule
     */
    public function view($user, MedicationSchedule $schedule): bool
    {
        // ADMIN can view all schedules
        if ($this->isAdmin($user)) {
            return true;
        }

        // PATIENT (User) can only view their own schedules
        if ($user instanceof User) {
            return $schedule->user_id == $user->id;
        }

        return false;
    }

    /**
     * Authorization rules for updating schedules
     */
    public function update($user, MedicationSchedule $schedule): bool
    {
        // ADMIN can update all schedules
        if ($this->isAdmin($user)) {
            return true;
        }

        // PATIENT cannot modify ADMIN-created schedules - IMPORTANT SECURITY CHECK
        if ($user instanceof User) {
            if ($schedule->source_type == 'ADMIN' || $schedule->source == 'resep') {
                return false;
            }
            // PATIENT can only update their own PATIENT schedules
            return $schedule->user_id == $user->id && $schedule->source_type == 'PATIENT';
        }

        return false;
    }

    /**
     * PATIENT cannot delete ADMIN-created schedules
     */
    public function delete($user, MedicationSchedule $schedule): bool
    {
        // ADMIN can delete all schedules
        if ($this->isAdmin($user)) {
            return true;
        }

        // PATIENT cannot delete ADMIN-created schedules - IMPORTANT SECURITY CHECK
        if ($user instanceof User) {
            if ($schedule->source_type == 'ADMIN' || $schedule->source == 'resep') {
                return false;
            }
            // PATIENT can only delete their own PATIENT schedules
            return $schedule->user_id == $user->id && $schedule->source_type == 'PATIENT';
        }

        return false;
    }

    /**
     * Both ADMIN and PATIENT can confirm medication intake on their own data
     */
    public function confirmIntake($user, MedicationSchedule $schedule): bool
    {
        // ADMIN can confirm for any patient
        if ($this->isAdmin($user)) {
            return true;
        }

        // PATIENT can confirm only their own medication
        if ($user instanceof User) {
            return $schedule->user_id == $user->id;
        }

        return false;
    }
}
