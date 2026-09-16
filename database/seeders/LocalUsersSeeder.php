<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocalUsersSeeder extends Seeder
{
    /**
     * Create predictable users intended only for local development.
     */
    public function run(): void
    {
        DB::table('areas')->upsert([[
            'id' => 'local-area',
            'name' => 'Área Local',
            'created_at' => now(),
            'updated_at' => now(),
        ]], ['id'], ['name', 'updated_at']);

        DB::table('representatives')->upsert([[
            'id' => 'local-representative',
            'area_id' => 'local-area',
            'name' => 'Representante Local',
            'phone' => '(00) 00000-0000',
            'email' => 'representante@local.test',
            'address' => 'Ambiente local',
            'portfolio' => 'Desenvolvimento',
            'client_focus' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]], ['id'], ['area_id', 'name', 'phone', 'email', 'address', 'portfolio', 'client_focus', 'updated_at']);

        $users = [
            [
                'id' => 'local-director',
                'name' => 'Diretor Local',
                'email' => 'diretor@local.test',
                'role' => 'diretor',
                'area_id' => null,
                'representative_id' => null,
                'phone' => null,
            ],
            [
                'id' => 'local-manager',
                'name' => 'Gerente Local',
                'email' => 'gerente@local.test',
                'role' => 'gerente',
                'area_id' => 'local-area',
                'representative_id' => null,
                'phone' => '(00) 00000-0000',
            ],
            [
                'id' => 'local-user-representative',
                'name' => 'Representante Local',
                'email' => 'representante@local.test',
                'role' => 'representante',
                'area_id' => 'local-area',
                'representative_id' => 'local-representative',
                'phone' => '(00) 00000-0000',
            ],
        ];

        foreach ($users as $attributes) {
            User::query()->updateOrCreate(
                ['email' => $attributes['email']],
                [...$attributes, 'password' => '123456', 'must_change_password' => false],
            );
        }
    }
}
