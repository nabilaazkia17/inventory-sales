<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    // RefreshDatabase: setiap test mulai dari database kosong (database khusus test, bukan database aplikasi)
    use RefreshDatabase;

    private function makeUser(string $role = 'staff'): User
    {
        return User::create([
            'name' => 'Tester',
            'email' => "{$role}@test.local",
            'password' => 'password123', // otomatis di-hash oleh cast di model User
            'role' => $role,
        ]);
    }

    public function test_guest_cannot_open_dashboard(): void
    {
        // Pengunjung yang belum login harus dilempar ke halaman login
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = $this->makeUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password-yang-salah',
        ]);

        $this->assertGuest();                      // tetap belum login
        $response->assertSessionHasErrors('email'); // ada pesan error di kolom email
    }

    public function test_correct_login_opens_dashboard(): void
    {
        $user = $this->makeUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')->assertOk();
    }
}
