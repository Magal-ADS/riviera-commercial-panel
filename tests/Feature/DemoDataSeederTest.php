<?php

namespace Tests\Feature;

use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_fills_the_panel_with_a_rolling_twelve_month_history(): void
    {
        $this->travelTo('2026-09-16 12:00:00');
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->seed(DemoDataSeeder::class);

        $this->postJson('/app/login', [
            'email' => 'diretoria@demo.riviera.local',
            'password' => '123456',
        ])->assertOk()
            ->assertJsonPath('authenticated', true)
            ->assertJsonPath('user.role', 'diretor');

        $state = $this->getJson('/app/state')
            ->assertOk()
            ->json();

        $this->assertCount(5, $state['areas']);
        $this->assertCount(20, $state['reps']);
        $this->assertCount(7, $state['skus']);
        $this->assertCount(12, $state['metasGlobal']);
        $this->assertCount(1920, $state['vendas']);
        $this->assertArrayHasKey('2026-09', $state['metasGlobal']);
        $this->assertArrayHasKey('2025-10', $state['metasGlobal']);
    }

    public function test_running_the_demo_seeder_again_does_not_duplicate_records(): void
    {
        $this->travelTo('2026-09-16 12:00:00');
        DB::table('skus')->insert([
            'id' => 'sku15',
            'name' => 'Hambúrguer de Salmão',
            'category' => 'Congelados',
            'weight' => '360g',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seed(DemoDataSeeder::class);

        $this->seed(DemoDataSeeder::class);

        $this->assertDatabaseCount('areas', 5);
        $this->assertDatabaseCount('representatives', 20);
        $this->assertDatabaseCount('skus', 7);
        $this->assertDatabaseCount('global_goals', 12);
        $this->assertDatabaseCount('area_goals', 60);
        $this->assertDatabaseCount('representative_goals', 240);
        $this->assertDatabaseCount('manual_histories', 240);
        $this->assertDatabaseCount('sales', 1920);
        $this->assertDatabaseCount('users', 26);
        $this->assertDatabaseHas('representatives', [
            'id' => 'r9',
            'area_id' => 'a3',
            'name' => 'Mariana Costa',
        ]);
        $this->assertDatabaseHas('skus', [
            'id' => 'file-400g',
            'name' => 'Filé de Tilápia',
            'category' => 'Filé',
            'weight' => '400g',
        ]);
        $this->assertDatabaseMissing('skus', [
            'id' => 'sku15',
        ]);
    }
}
