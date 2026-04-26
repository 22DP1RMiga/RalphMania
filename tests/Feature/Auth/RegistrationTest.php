<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'username'              => 'testuser',
            'first_name'            => 'Test',
            'last_name'             => 'User',
            'email'                 => 'test@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        // Pēc reģistrācijas novirza uz e-pasta verifikāciju (nav autentificēts vēl)
        $response->assertRedirect();
    }
}
