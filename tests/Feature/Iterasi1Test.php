<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class Iterasi1Test extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_admin()
    {
        $admin = User::factory()->create([
            'role_user' => 'pegawai',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function login_pasien()
    {
        $user = User::factory()->create([
            'role_user' => 'mahasiswa',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'user@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
    }

    /** @test */
    public function registrasi_pasien()
    {
        $response = $this->post('/register', [
            'role_user' => 'mahasiswa',
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'nim' => '123456',
            'prodi' => 'Informatika',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com',
        ]);
    }
}