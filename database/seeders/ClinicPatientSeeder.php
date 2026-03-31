<?php

namespace Database\Seeders;

use App\Models\ClinicPatient;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClinicPatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing users for linking
        $users = User::all();

        // Create clinic patients linked to app users (70% of users get linked)
        foreach ($users as $user) {
            if (rand(1, 100) <= 70) {
                // Determine identity_number based on user's role
                $identityNumber = null;
                if ($user->role_user === 'mahasiswa') {
                    // Use user's NIM (which should already be in format: ####/###/### )
                    $identityNumber = $user->nim;
                }
                // For pegawai or other roles, identity_number stays null

                ClinicPatient::factory()
                    ->withAppUser($user)
                    ->active()
                    ->create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'category' => $user->role_user, // Use user's role as category
                        'identity_number' => $identityNumber,
                    ]);
            }
        }

        // Create standalone clinic patients without app users (100 patients)
        ClinicPatient::factory(100)
            ->withoutAppUser()
            ->create();

        // Create some inactive clinic patients (30 patients)
        ClinicPatient::factory(30)
            ->inactive()
            ->withoutAppUser()
            ->create();

        // Ensure minimum clinic patients (at least 200 total)
        $currentCount = ClinicPatient::count();
        if ($currentCount < 200) {
            $toCreate = 200 - $currentCount;
            ClinicPatient::factory($toCreate)->create();
        }
    }
}

