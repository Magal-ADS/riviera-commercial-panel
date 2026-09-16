<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private const LEGACY_DEMO_SKU_IDS = [
        'sku1', 'sku2', 'sku3', 'sku4', 'sku5',
        'sku6', 'sku7', 'sku8', 'sku9', 'sku10',
        'sku11', 'sku12', 'sku13', 'sku14', 'sku15',
    ];

    /**
     * Fill the local panel with a deterministic, rolling 12-month commercial history.
     */
    public function run(): void
    {
        $timestamp = now();
        $areas = $this->areas($timestamp);
        $representatives = $this->representatives($timestamp);
        $skus = $this->skus($timestamp);

        DB::transaction(function () use ($areas, $representatives, $skus, $timestamp): void {
            DB::table('areas')->upsert($areas, ['id'], ['name', 'updated_at']);
            DB::table('representatives')->upsert($representatives, ['id'], [
                'area_id', 'name', 'phone', 'email', 'postal_code', 'address',
                'portfolio', 'client_focus', 'updated_at',
            ]);
            DB::table('skus')->upsert($skus, ['id'], ['name', 'category', 'weight', 'updated_at']);

            $this->seedUsers($areas, $representatives);
            $this->seedCommercialHistory($areas, $representatives, $skus, $timestamp);

            DB::table('skus')->whereIn('id', self::LEGACY_DEMO_SKU_IDS)->delete();
        });
    }

    /**
     * @return array<int, array{id: string, name: string, created_at: mixed, updated_at: mixed}>
     */
    private function areas(mixed $timestamp): array
    {
        return [
            ['id' => 'a1', 'name' => 'Sul', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['id' => 'a2', 'name' => 'Norte', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['id' => 'a3', 'name' => 'Sudeste', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['id' => 'a4', 'name' => 'Nordeste', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['id' => 'a5', 'name' => 'Centro-Oeste', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function representatives(mixed $timestamp): array
    {
        $representatives = [
            ['r1', 'a1', 'Carlos Eduardo Martins', '(41) 99821-4401', 'carlos.martins@demo.riviera.local', '80010-000', 'Curitiba/PR', 'Food service e varejo', 'Restaurantes, empórios e supermercados'],
            ['r2', 'a1', 'Fernanda Oliveira', '(51) 99144-2030', 'fernanda.oliveira@demo.riviera.local', '90010-150', 'Porto Alegre/RS', 'Atacarejo', 'Atacadistas e redes regionais'],
            ['r3', 'a1', 'Rafael Schmitt', '(48) 99672-1188', 'rafael.schmitt@demo.riviera.local', '88010-400', 'Florianópolis/SC', 'Linha premium', 'Hotéis, restaurantes e pousadas'],
            ['r4', 'a1', 'Juliana Ribeiro', '(43) 99905-6632', 'juliana.ribeiro@demo.riviera.local', '86010-180', 'Londrina/PR', 'Varejo', 'Mercados independentes'],
            ['r5', 'a2', 'Bruno Nascimento', '(92) 99218-4500', 'bruno.nascimento@demo.riviera.local', '69010-060', 'Manaus/AM', 'Distribuição', 'Distribuidores e peixarias'],
            ['r6', 'a2', 'Camila Souza', '(91) 98153-7720', 'camila.souza@demo.riviera.local', '66010-020', 'Belém/PA', 'Food service', 'Restaurantes e hotéis'],
            ['r7', 'a2', 'Diego Alves', '(69) 99320-1455', 'diego.alves@demo.riviera.local', '76801-020', 'Porto Velho/RO', 'Varejo', 'Supermercados e conveniências'],
            ['r8', 'a2', 'Patrícia Lima', '(63) 99262-8041', 'patricia.lima@demo.riviera.local', '77001-002', 'Palmas/TO', 'Atacado', 'Atacadistas e distribuidores'],
            ['r9', 'a3', 'Mariana Costa', '(11) 99742-1130', 'mariana.costa@demo.riviera.local', '01310-100', 'São Paulo/SP', 'Grandes contas', 'Redes nacionais e atacarejos'],
            ['r10', 'a3', 'Lucas Mendes', '(21) 99081-2264', 'lucas.mendes@demo.riviera.local', '20040-020', 'Rio de Janeiro/RJ', 'Food service', 'Hotéis, bares e restaurantes'],
            ['r11', 'a3', 'Aline Rodrigues', '(31) 99805-3477', 'aline.rodrigues@demo.riviera.local', '30130-110', 'Belo Horizonte/MG', 'Varejo premium', 'Empórios e supermercados'],
            ['r12', 'a3', 'Gustavo Freitas', '(27) 99233-9084', 'gustavo.freitas@demo.riviera.local', '29010-120', 'Vitória/ES', 'Distribuição', 'Distribuidores e peixarias'],
            ['r13', 'a4', 'Renata Barbosa', '(71) 99170-4521', 'renata.barbosa@demo.riviera.local', '40020-000', 'Salvador/BA', 'Food service', 'Hotéis, resorts e restaurantes'],
            ['r14', 'a4', 'Felipe Araújo', '(81) 99840-6335', 'felipe.araujo@demo.riviera.local', '50030-230', 'Recife/PE', 'Atacarejo', 'Redes regionais e atacadistas'],
            ['r15', 'a4', 'Larissa Santos', '(85) 99266-5417', 'larissa.santos@demo.riviera.local', '60060-170', 'Fortaleza/CE', 'Linha premium', 'Restaurantes e beach clubs'],
            ['r16', 'a4', 'Thiago Pereira', '(84) 99103-7250', 'thiago.pereira@demo.riviera.local', '59020-200', 'Natal/RN', 'Varejo', 'Supermercados e empórios'],
            ['r17', 'a5', 'Daniel Moraes', '(62) 99812-6304', 'daniel.moraes@demo.riviera.local', '74003-010', 'Goiânia/GO', 'Distribuição', 'Distribuidores e atacadistas'],
            ['r18', 'a5', 'Beatriz Teixeira', '(61) 99166-5208', 'beatriz.teixeira@demo.riviera.local', '70040-010', 'Brasília/DF', 'Food service', 'Restaurantes e redes hoteleiras'],
            ['r19', 'a5', 'Marcelo Nunes', '(67) 99290-1840', 'marcelo.nunes@demo.riviera.local', '79002-071', 'Campo Grande/MS', 'Varejo', 'Supermercados e casas de carne'],
            ['r20', 'a5', 'Isabela Ferreira', '(65) 99631-4072', 'isabela.ferreira@demo.riviera.local', '78005-200', 'Cuiabá/MT', 'Atacado', 'Atacadistas e distribuidores'],
        ];

        return array_map(fn (array $representative): array => [
            'id' => $representative[0],
            'area_id' => $representative[1],
            'name' => $representative[2],
            'phone' => $representative[3],
            'email' => $representative[4],
            'postal_code' => $representative[5],
            'address' => $representative[6],
            'portfolio' => $representative[7],
            'client_focus' => $representative[8],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $representatives);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function skus(mixed $timestamp): array
    {
        $skus = [
            ['file-400g', 'Filé de Tilápia', 'Filé', '400g'],
            ['file-800g', 'Filé de Tilápia', 'Filé', '800g'],
            ['file-2kg', 'Filé de Tilápia', 'Filé', '2kg'],
            ['isca-400g', 'Isca de Tilápia', 'Isca', '400g'],
            ['tirinhas-250g', 'Tirinhas de Tilápia', 'Tirinhas', '250g'],
            ['granel-10kg', 'Filé de Tilápia Granel', 'Filé', 'Pacote 10 kg'],
            ['panga-granel-10kg', 'Filé de Panga Granel', 'Filé', '10kg'],
        ];

        return array_map(fn (array $sku): array => [
            'id' => $sku[0],
            'name' => $sku[1],
            'category' => $sku[2],
            'weight' => $sku[3],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $skus);
    }

    /**
     * @param  array<int, array<string, mixed>>  $areas
     * @param  array<int, array<string, mixed>>  $representatives
     */
    private function seedUsers(array $areas, array $representatives): void
    {
        $this->seedUser([
            'id' => 'demo-director',
            'name' => 'Diretoria Demonstração',
            'email' => 'diretoria@demo.riviera.local',
            'role' => 'diretor',
            'area_id' => null,
            'representative_id' => null,
            'phone' => null,
        ]);

        $managerNames = ['João Pedro Almeida', 'Vanessa Monteiro', 'Eduardo Campos', 'Priscila Andrade', 'Rodrigo Tavares'];

        foreach ($areas as $areaIndex => $area) {
            $this->seedUser([
                'id' => 'demo-manager-'.$area['id'],
                'name' => $managerNames[$areaIndex],
                'email' => 'gestor.'.$area['id'].'@demo.riviera.local',
                'role' => 'gerente',
                'area_id' => $area['id'],
                'representative_id' => null,
                'phone' => sprintf('(11) 98800-%04d', $areaIndex + 1),
            ]);
        }

        foreach ($representatives as $representative) {
            $this->seedUser([
                'id' => 'demo-user-'.$representative['id'],
                'name' => $representative['name'],
                'email' => $representative['email'],
                'role' => 'representante',
                'area_id' => $representative['area_id'],
                'representative_id' => $representative['id'],
                'phone' => $representative['phone'],
            ]);
        }
    }

    /**
     * @param  array{id: string, name: string, email: string, role: string, area_id: ?string, representative_id: ?string, phone: ?string}  $attributes
     */
    private function seedUser(array $attributes): void
    {
        $user = User::query()->firstOrNew(['email' => $attributes['email']]);

        if (! $user->exists) {
            $user->id = $attributes['id'];
            $user->password = '123456';
        }

        $user->fill($attributes);
        $user->must_change_password = false;
        $user->save();
    }

    /**
     * @param  array<int, array<string, mixed>>  $areas
     * @param  array<int, array<string, mixed>>  $representatives
     * @param  array<int, array<string, mixed>>  $skus
     */
    private function seedCommercialHistory(array $areas, array $representatives, array $skus, mixed $timestamp): void
    {
        $globalGoals = [];
        $areaGoals = [];
        $representativeGoals = [];
        $manualHistories = [];
        $sales = [];
        $firstPeriod = now()->startOfMonth()->subMonths(11);
        $seasonality = [0.90, 0.92, 0.96, 0.98, 1.01, 1.04, 1.06, 1.03, 1.00, 1.08, 1.16, 1.24];
        $weekShares = [0.21, 0.24, 0.26, 0.29];
        $clientShares = [0.58, 0.42];
        $clients = $this->clientsByArea();

        for ($monthOffset = 0; $monthOffset < 12; $monthOffset++) {
            $periodDate = $firstPeriod->copy()->addMonths($monthOffset);
            $period = $periodDate->format('Y-m');
            $areaTotals = array_fill_keys(array_column($areas, 'id'), 0.0);
            $globalTotal = 0.0;

            foreach ($representatives as $representativeIndex => $representative) {
                $baseGoal = 105000 + (($representativeIndex % 5) * 12500);
                $growth = 1 + ($monthOffset * 0.008);
                $goal = round($baseGoal * $seasonality[$periodDate->month - 1] * $growth, 2);
                $achievementRate = 0.74 + ((($representativeIndex * 7) + ($monthOffset * 5)) % 41) / 100;
                $salesTotal = round($goal * $achievementRate, 2);

                $representativeGoals[] = [
                    'period' => $period,
                    'representative_id' => $representative['id'],
                    'amount' => $goal,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
                $manualHistories[] = [
                    'period' => $period,
                    'representative_id' => $representative['id'],
                    'amount' => $salesTotal,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];

                $areaTotals[$representative['area_id']] += $goal;
                $globalTotal += $goal;

                foreach ($weekShares as $weekIndex => $weekShare) {
                    foreach ($clientShares as $saleIndex => $clientShare) {
                        $skuIndex = ($representativeIndex + $monthOffset + ($weekIndex * 2) + $saleIndex) % count($skus);
                        $clientList = $clients[$representative['area_id']];
                        $clientIndex = ($representativeIndex + $monthOffset + $weekIndex + $saleIndex) % count($clientList);
                        $amount = round($salesTotal * $weekShare * $clientShare, 2);
                        $saleDate = $periodDate->copy()->day(($weekIndex * 7) + 4 + $saleIndex);

                        $sales[] = [
                            'id' => sprintf('demo-sale-%s-%s-%d-%d', $period, $representative['id'], $weekIndex + 1, $saleIndex + 1),
                            'period' => $period,
                            'representative_id' => $representative['id'],
                            'area_id' => $representative['area_id'],
                            'sku_id' => $skus[$skuIndex]['id'],
                            'client' => $clientList[$clientIndex],
                            'amount' => $amount,
                            'volume' => round($amount / (28 + ($skuIndex * 1.75)), 3),
                            'week' => 'S'.($weekIndex + 1),
                            'occurred_at' => $saleDate->timestamp * 1000,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }
            }

            $globalGoals[] = [
                'period' => $period,
                'total' => round($globalTotal, 2),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            foreach ($areaTotals as $areaId => $amount) {
                $areaGoals[] = [
                    'period' => $period,
                    'area_id' => $areaId,
                    'amount' => round($amount, 2),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
        }

        DB::table('global_goals')->upsert($globalGoals, ['period'], ['total', 'updated_at']);
        DB::table('area_goals')->upsert($areaGoals, ['period', 'area_id'], ['amount', 'updated_at']);
        DB::table('representative_goals')->upsert($representativeGoals, ['period', 'representative_id'], ['amount', 'updated_at']);
        DB::table('manual_histories')->upsert($manualHistories, ['period', 'representative_id'], ['amount', 'updated_at']);

        foreach (array_chunk($sales, 100) as $salesChunk) {
            DB::table('sales')->upsert($salesChunk, ['id'], [
                'period', 'representative_id', 'area_id', 'sku_id', 'client',
                'amount', 'volume', 'week', 'occurred_at', 'updated_at',
            ]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function clientsByArea(): array
    {
        return [
            'a1' => ['Mercado Bom Vizinho', 'Rede Super Sul', 'Bistrô do Porto', 'Empório Catarinense', 'Atacado Paraná'],
            'a2' => ['Casa do Pescado', 'Supermercados Amazônia', 'Hotel Rio Negro', 'Distribuidora Tapajós', 'Empório do Norte'],
            'a3' => ['Grupo Mesa Brasil', 'Rede Vila Gourmet', 'Supermercados Horizonte', 'Hotel Atlântico', 'Empório Central'],
            'a4' => ['Rede Sol Nascente', 'Resort Mar Azul', 'Atacado Nordeste', 'Restaurante Mandacaru', 'Mercado Boa Praia'],
            'a5' => ['Empório Cerrado', 'Rede Planalto', 'Churrascaria Central', 'Atacado Pantanal', 'Hotel das Águas'],
        ];
    }
}
