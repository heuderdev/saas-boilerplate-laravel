<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Services\OnboardingService;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $input['tenant_id'] = \Illuminate\Support\Str::slug($input['company_name']);

        Validator::make(
            $input,
            [
                ...$this->profileRules(),
                'company_name' => ['required', 'string', 'max:255', 'unique:tenants,name'],
                'tenant_id' => ['required', 'string', 'unique:tenants,id'],
                'password' => $this->passwordRules(),
            ],
            [
                'tenant_id.unique' => 'Este nome de empresa já está em uso ou é muito similar a um existente.',
            ]
        )->validate();

        return (new OnboardingService())->createNewTenant($input);
    }
}
