<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles; // RBAC
use Spatie\Activitylog\Traits\LogsActivity; // Auditoria
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use Notifiable, HasRoles, LogsActivity;
    // NÃO USE 'Billable' AQUI. O usuário não paga nada.

    protected $fillable = [
        'name',
        'email',
        'password',
        'global_status',
        'current_tenant_id'
    ];

    // Relação N:N com Tenants
    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user', 'user_id', 'tenant_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    // Helper para pegar o tenant atual facilmente
    public function currentTenant()
    {
        return $this->belongsTo(Tenant::class, 'current_tenant_id');
    }

    // Configuração de Auditoria (Logs)
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'global_status', 'current_tenant_id'])
            ->logOnlyDirty();
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
