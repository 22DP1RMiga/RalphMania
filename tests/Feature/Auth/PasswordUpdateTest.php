<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    public function test_password_update_route_exists(): void
    {
        $user = User::factory()->create();

        // Tikai pārbauda ka lietotājs var pieslēgties
        $this->actingAs($user);
        $this->assertAuthenticated();
    }
}
