<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class OnboardingService
{
    public function createNewTenant(array $input): User
    {
        return DB::transaction(function () use ($input) {
            // 1. Criar o Usuário
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'global_status' => 'active',
            ]);

            // 2. Criar o Tenant (Empresa)
            // O ID será gerado automaticamente (slug ou uuid) pelo Stancl se configurado,
            // ou podemos forçar um slug baseado no nome.
            $tenant = Tenant::create([
                'id' => \Illuminate\Support\Str::slug($input['company_name']), // Ex: Minha Empresa -> minha-empresa
                'name' => $input['company_name'],
                'owner_id' => $user->id,
                'trial_ends_at' => now()->addMinutes(5), // Trial de 14 dias
            ]);

            // 3. Vincular Usuário ao Tenant (Tabela Pivô)
            // Aqui definimos o user como 'owner' ou 'admin' no contexto visual
            $tenant->members()->attach($user->id);

            // 4. Setar o Contexto Atual
            $user->update(['current_tenant_id' => $tenant->id]);

            // Busca o Role GLOBAL (onde tenant_id é NULL)
            // Precisamos passar o objeto Role, não a string, para o Spatie aceitar
            $adminRole = Role::where('name', 'admin')
                ->whereNull('tenant_id')
                ->first();

            // 5. Atribuir Papéis (Spatie Permission)
            // IMPORTANTE: Definir o escopo do time antes de dar a permissão
            setPermissionsTeamId($tenant->id);

            // Se por acaso o seeder não rodou, cria um fallback (segurança)
            if (!$adminRole) {
                $adminRole = Role::create(['name' => 'admin', 'tenant_id' => null]);
            }

            $user->assignRole($adminRole);

            return $user;
        });
    }
}
