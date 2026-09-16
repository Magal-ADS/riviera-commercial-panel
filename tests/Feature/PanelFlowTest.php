<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PanelFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->seed();
    }

    public function test_director_can_login_and_load_the_seeded_panel(): void
    {
        $this->postJson('/app/login', [
            'email' => 'anderson.silva@rivierapescados.com',
            'password' => 'Riviera123!',
        ])->assertOk()
            ->assertJsonPath('user.role', 'diretor')
            ->assertJsonStructure(['csrfToken']);

        $this->getJson('/app/state')
            ->assertOk()
            ->assertJsonFragment(['nome' => 'Sul'])
            ->assertJsonFragment(['nome' => 'Filé de Tilápia 500g']);
    }

    public function test_representative_is_forced_to_change_the_initial_password(): void
    {
        $this->postJson('/app/login', ['email' => 'rep@rua.com', 'password' => '123'])
            ->assertOk()
            ->assertJsonPath('requiresPasswordChange', true);

        $this->postJson('/app/reset-password', [
            'password' => 'nova-senha',
            'password_confirmation' => 'nova-senha',
        ])->assertOk()->assertJsonPath('user.mustChangePassword', false);

        $user = User::query()->where('email', 'rep@rua.com')->firstOrFail();
        $this->assertTrue(Hash::check('nova-senha', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_panel_state_is_persisted_in_relational_tables(): void
    {
        $user = User::query()->where('role', 'diretor')->firstOrFail();
        $state = $this->actingAs($user)->getJson('/app/state')->json();
        $state['areas'][] = ['id' => 'a_teste', 'nome' => 'Centro-Oeste'];
        $state['metasGlobal']['2026-09'] = ['total' => 100000, 'areasMeta' => ['a_teste' => 100000]];

        $this->putJson('/app/state', $state)->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('areas', ['id' => 'a_teste', 'name' => 'Centro-Oeste']);
        $this->assertDatabaseHas('global_goals', ['period' => '2026-09', 'total' => 100000]);
        $this->assertDatabaseHas('area_goals', ['period' => '2026-09', 'area_id' => 'a_teste', 'amount' => 100000]);
    }

    public function test_state_endpoints_require_authentication(): void
    {
        $this->getJson('/app/state')->assertUnauthorized();
        $this->putJson('/app/state', [])->assertUnauthorized();
    }

    public function test_representative_cannot_overwrite_the_panel_state(): void
    {
        $representative = User::query()->where('role', 'representante')->firstOrFail();

        $this->actingAs($representative)
            ->putJson('/app/state', [])
            ->assertForbidden();

        $this->assertDatabaseHas('areas', [
            'id' => 'a1',
            'name' => 'Sul',
        ]);
    }
}
