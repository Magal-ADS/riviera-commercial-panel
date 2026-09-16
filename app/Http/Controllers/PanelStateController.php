<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PanelStateController extends Controller
{
    public function show(): JsonResponse
    {
        $globalGoals = [];
        foreach (DB::table('global_goals')->get() as $goal) {
            $globalGoals[$goal->period] = ['total' => (float) $goal->total, 'areasMeta' => []];
        }
        foreach (DB::table('area_goals')->get() as $goal) {
            $globalGoals[$goal->period] ??= ['total' => 0, 'areasMeta' => []];
            $globalGoals[$goal->period]['areasMeta'][$goal->area_id] = (float) $goal->amount;
        }

        $representativeGoals = [];
        foreach (DB::table('representative_goals')->get() as $goal) {
            $representativeGoals[$goal->period][$goal->representative_id] = (float) $goal->amount;
        }

        $manualHistory = [];
        foreach (DB::table('manual_histories')->get() as $item) {
            $manualHistory[$item->representative_id.'_'.$item->period] = (float) $item->amount;
        }

        return response()->json([
            'users' => User::query()->get()->map(fn (User $user) => [
                'id' => $user->id, 'role' => $user->role, 'email' => $user->email,
                'nome' => $user->name, 'areaId' => $user->area_id,
                'repId' => $user->representative_id, 'telefone' => $user->phone,
                'mustChangePassword' => $user->must_change_password,
            ]),
            'areas' => DB::table('areas')->get()->map(fn ($area) => ['id' => $area->id, 'nome' => $area->name]),
            'reps' => DB::table('representatives')->get()->map(fn ($rep) => [
                'id' => $rep->id, 'areaId' => $rep->area_id, 'nome' => $rep->name,
                'telefone' => $rep->phone, 'email' => $rep->email, 'cep' => $rep->postal_code,
                'endereco' => $rep->address, 'pastas' => $rep->portfolio, 'clientes' => $rep->client_focus,
            ]),
            'skus' => DB::table('skus')->get()->map(fn ($sku) => [
                'id' => $sku->id, 'nome' => $sku->name, 'categoria' => $sku->category, 'gramatura' => $sku->weight,
            ]),
            'metasGlobal' => $globalGoals,
            'metasRep' => $representativeGoals,
            'historicoManual' => $manualHistory,
            'vendas' => DB::table('sales')->get()->map(fn ($sale) => [
                'id' => $sale->id, 'period' => $sale->period, 'repId' => $sale->representative_id,
                'areaId' => $sale->area_id, 'skuId' => $sale->sku_id, 'cliente' => $sale->client,
                'valor' => (float) $sale->amount, 'volume' => (float) $sale->volume,
                'semana' => $sale->week, 'ts' => $sale->occurred_at,
            ]),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'diretor', 'gerente'], true), 403);

        $data = $request->validate([
            'users' => ['present', 'array'], 'areas' => ['present', 'array'],
            'reps' => ['present', 'array'], 'skus' => ['present', 'array'],
            'metasGlobal' => ['present', 'array'], 'metasRep' => ['present', 'array'],
            'historicoManual' => ['present', 'array'], 'vendas' => ['present', 'array'],
        ]);

        DB::transaction(function () use ($data) {
            $this->syncSimpleTable('areas', $data['areas'], fn ($item) => [
                'id' => $item['id'], 'name' => $item['nome'],
            ]);
            $this->syncSimpleTable('representatives', $data['reps'], fn ($item) => [
                'id' => $item['id'], 'area_id' => $item['areaId'], 'name' => $item['nome'],
                'phone' => $item['telefone'] ?? null, 'email' => $item['email'],
                'postal_code' => $item['cep'] ?? null, 'address' => $item['endereco'] ?? null,
                'portfolio' => $item['pastas'] ?? null, 'client_focus' => $item['clientes'] ?? null,
            ]);
            $this->syncSimpleTable('skus', $data['skus'], fn ($item) => [
                'id' => $item['id'], 'name' => $item['nome'], 'category' => $item['categoria'], 'weight' => $item['gramatura'],
            ]);

            $userIds = [];
            foreach ($data['users'] as $item) {
                $userIds[] = $item['id'];
                $user = User::find($item['id']) ?? new User(['id' => $item['id']]);
                $user->fill([
                    'name' => $item['nome'], 'email' => strtolower($item['email']), 'role' => $item['role'],
                    'area_id' => $item['areaId'] ?? null, 'representative_id' => $item['repId'] ?? null,
                    'phone' => $item['telefone'] ?? null, 'must_change_password' => $item['mustChangePassword'] ?? false,
                ]);
                if (! empty($item['pass'])) {
                    $user->password = $item['pass'];
                }
                if (! $user->exists && ! $user->password) {
                    $user->password = str()->random(32);
                }
                $user->save();
            }
            User::query()->whereNotIn('id', $userIds)->delete();

            DB::table('global_goals')->delete();
            DB::table('area_goals')->delete();
            foreach ($data['metasGlobal'] as $period => $goal) {
                DB::table('global_goals')->insert(['period' => $period, 'total' => $goal['total'] ?? 0, 'created_at' => now(), 'updated_at' => now()]);
                foreach (($goal['areasMeta'] ?? []) as $areaId => $amount) {
                    DB::table('area_goals')->insert(['period' => $period, 'area_id' => $areaId, 'amount' => $amount, 'created_at' => now(), 'updated_at' => now()]);
                }
            }

            DB::table('representative_goals')->delete();
            foreach ($data['metasRep'] as $period => $goals) {
                foreach ($goals as $representativeId => $amount) {
                    DB::table('representative_goals')->insert(['period' => $period, 'representative_id' => $representativeId, 'amount' => $amount, 'created_at' => now(), 'updated_at' => now()]);
                }
            }

            DB::table('manual_histories')->delete();
            foreach ($data['historicoManual'] as $key => $amount) {
                $separator = strrpos($key, '_');
                if ($separator === false) {
                    continue;
                }
                DB::table('manual_histories')->insert([
                    'representative_id' => substr($key, 0, $separator), 'period' => substr($key, $separator + 1),
                    'amount' => $amount, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            $this->syncSimpleTable('sales', $data['vendas'], fn ($item) => [
                'id' => $item['id'], 'period' => $item['period'], 'representative_id' => $item['repId'],
                'area_id' => $item['areaId'], 'sku_id' => $item['skuId'] ?? null,
                'client' => $item['cliente'], 'amount' => $item['valor'], 'volume' => $item['volume'] ?? 0,
                'week' => $item['semana'], 'occurred_at' => $item['ts'] ?? null,
            ]);
        });

        return response()->json(['ok' => true]);
    }

    private function syncSimpleTable(string $table, array $items, callable $map): void
    {
        $ids = [];
        foreach ($items as $item) {
            $row = $map($item);
            $ids[] = $row['id'];
            $row['updated_at'] = now();
            DB::table($table)->updateOrInsert(['id' => $row['id']], $row + ['created_at' => now()]);
        }
        DB::table($table)->whereNotIn('id', $ids)->delete();
    }
}
