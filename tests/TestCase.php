<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Pirms katra testa — aizpilda lomas (roles),
     * jo UserFactory izveido lietotājus ar role_id=2 (user).
     * Bez rolēm rodas FK constraint kļūda.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }
}
