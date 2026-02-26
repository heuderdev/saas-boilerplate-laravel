<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Laravel\Cashier\Billable; // <--- O Poder do Pagamento

class Tenant extends BaseTenant
{
    use HasDomains, Billable;

    // Campos permitidos para edição em massa
    protected $fillable = [
        'id',
        'name',
        'owner_id',
        'data',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at'
    ];

    // Colunas que o Stancl deve tratar como "Custom Columns" e não JSON
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'owner_id',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'trial_ends_at'
        ];
    }

    // Relação: Quem é o dono?
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Relação: Quem são os membros/funcionários?
    public function members()
    {
        return $this->belongsToMany(User::class, 'tenant_user', 'tenant_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }
}
