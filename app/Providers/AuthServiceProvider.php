<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\Ticket;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('is-admin', fn(User $user) => $user->role === 'admin');

        Gate::define('view', function (User $user, Ticket $ticket) {
            return $user->role === 'admin' || $user->id === $ticket->user_id;
        });

        Gate::define('update', function (User $user, Ticket $ticket) {
            return $user->role === 'admin' || $user->id === $ticket->user_id;
        });

        Gate::define('delete', function (User $user, Ticket $ticket) {
            return $user->role === 'admin';
        });

        Gate::define('add-comment', function (User $user, Ticket $ticket) {
            return $user->role === 'admin' || $user->id === $ticket->user_id;
        });

        Gate::define('add-attachment', function (User $user, Ticket $ticket) {
            return $user->role === 'admin' || $user->id === $ticket->user_id;
        });
    }
}
