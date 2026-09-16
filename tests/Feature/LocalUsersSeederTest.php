<?php

namespace Tests\Feature;

use Database\Seeders\LocalUsersSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalUsersSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_users_can_log_in_with_the_expected_roles(): void
    {
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->seed(LocalUsersSeeder::class);

        $credentials = [
            'diretor@local.test' => 'diretor',
            'gerente@local.test' => 'gerente',
            'representante@local.test' => 'representante',
        ];

        foreach ($credentials as $email => $role) {
            $this->postJson('/app/login', [
                'email' => $email,
                'password' => '123456',
            ])->assertOk()
                ->assertJsonPath('authenticated', true)
                ->assertJsonPath('user.role', $role)
                ->assertJsonPath('user.mustChangePassword', false);

            $this->postJson('/app/logout')->assertOk();
        }
    }
}
