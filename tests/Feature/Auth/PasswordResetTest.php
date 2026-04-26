<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Atspējo CSRF pārbaudī testiem — Laravel 11
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(302);
    }
}
