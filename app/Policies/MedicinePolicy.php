<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use App\Models\Medicine;

class MedicinePolicy
{
    /**
     * Determine if user is an admin
     */
    private function isAdmin($user): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Only ADMIN and Users can create medicines
     * Admins create medicines with source_type ADMIN
     * Users create medicines with source_type PATIENT (for themselves)
     */
    public function create($user): bool
    {
        // ADMIN can create medicines
        if ($this->isAdmin($user)) {
            return true;
        }

        // Users (Patients) can also create medicines for themselves
        if ($user instanceof User) {
            return true;
        }

        return false;
    }

    /**
     * Authorized users can view medicines
     */
    public function view($user, Medicine $medicine): bool
    {
        // ADMIN can view all medicines
        if ($this->isAdmin($user)) {
            return true;
        }

        // PATIENT (User) can view ADMIN medicines + own medicines
        if ($user instanceof User) {
            return $medicine->source_type == 'ADMIN' || $medicine->user_id == $user->id;
        }

        return false;
    }

    /**
     * Authorization rules for updating medicines
     */
    public function update($user, Medicine $medicine): bool
    {
        // ADMIN can update all medicines
        if ($this->isAdmin($user)) {
            return true;
        }

        // PATIENT cannot modify ADMIN medicines - IMPORTANT SECURITY CHECK
        if ($user instanceof User) {
            if ($medicine->source_type == 'ADMIN') {
                return false;
            }
            // PATIENT can only update their own PATIENT medicines
            return $medicine->user_id == $user->id && $medicine->source_type == 'PATIENT';
        }

        return false;
    }

    /**
     * Authorization rules for deleting medicines
     */
    public function delete($user, Medicine $medicine): bool
    {
        // ADMIN can delete all medicines
        if ($this->isAdmin($user)) {
            return true;
        }

        // PATIENT cannot delete ADMIN medicines - IMPORTANT SECURITY CHECK
        if ($user instanceof User) {
            if ($medicine->source_type == 'ADMIN') {
                return false;
            }
            // PATIENT can only delete their own PATIENT medicines
            return $medicine->user_id == $user->id && $medicine->source_type == 'PATIENT';
        }

        return false;
    }

    /**
     * Only ADMIN can restore medicines
     */
    public function restore($user, Medicine $medicine): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Only ADMIN can force delete medicines
     */
    public function forceDelete($user, Medicine $medicine): bool
    {
        return $this->isAdmin($user);
    }
}
