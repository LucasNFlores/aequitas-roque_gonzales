<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Abilities resolved by model policies instead of the super-admin bypass.
     *
     * @var list<string>
     */
    private const POLICY_ABILITIES = [
        'viewAny',
        'view',
        'create',
        'update',
        'delete',
        'restore',
        'forceDelete',
        'download',
        'admit',
        'reject',
        'assignProfessional',
        'reassignProfessional',
        'updateState',
        'viewHistory',
        'createFor',
        'manageRoles',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            if (! $user->hasRole('Administrador')) {
                return null;
            }

            return in_array($ability, self::POLICY_ABILITIES, true) ? null : true;
        });
    }
}
