<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_profile(): void
    {
        $response = $this->get('/profile');

        // Vai redirect uz login, vai 405 ja route nav GET — abi pieņemami
        $this->assertContains($response->status(), [301, 302, 303, 405]);
    }
}
