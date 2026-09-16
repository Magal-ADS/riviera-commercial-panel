<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('areas')->upsert([
            ['id' => 'a1', 'name' => 'Sul', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 'a2', 'name' => 'Norte', 'created_at' => now(), 'updated_at' => now()],
        ], ['id'], ['name', 'updated_at']);

        DB::table('representatives')->upsert([[
            'id' => 'r1', 'area_id' => 'a1', 'name' => 'Representante Padrão',
            'phone' => '(11) 99999-9999', 'email' => 'rep@rua.com', 'address' => 'Rua A',
            'portfolio' => 'Varejo', 'client_focus' => '', 'created_at' => now(), 'updated_at' => now(),
        ]], ['id'], ['area_id', 'name', 'phone', 'email', 'address', 'portfolio', 'client_focus', 'updated_at']);

        DB::table('skus')->upsert([[
            'id' => 'sku1', 'name' => 'Filé de Tilápia 500g', 'category' => 'Congelados',
            'weight' => '500g', 'created_at' => now(), 'updated_at' => now(),
        ]], ['id'], ['name', 'category', 'weight', 'updated_at']);

        $users = [
            ['id' => 'u1', 'role' => 'diretor', 'email' => 'anderson.silva@rivierapescados.com', 'password' => 'Riviera123!', 'name' => 'Anderson Silva'],
            ['id' => 'u2', 'role' => 'diretor', 'email' => 'thais.carvalho@rivierapescados.com', 'password' => 'Riviera123!', 'name' => 'Thais Carvalho'],
            ['id' => 'u3', 'role' => 'gerente', 'email' => 'usuario@rivierapescados.com', 'password' => '123456', 'name' => 'José Vitor (Gestor)', 'area_id' => 'a1', 'phone' => '(11) 99999-9999'],
            ['id' => 'u4', 'role' => 'representante', 'email' => 'rep@rua.com', 'password' => '123', 'name' => 'Representante Padrão', 'area_id' => 'a1', 'representative_id' => 'r1', 'must_change_password' => true],
        ];

        foreach ($users as $attributes) {
            User::query()->firstOrCreate(['email' => $attributes['email']], $attributes);
        }

        if (app()->environment('local')) {
            $this->call(LocalUsersSeeder::class);
            $this->call(DemoDataSeeder::class);
        }
    }
}
