<?php

namespace App\Providers;

use App\Models\{Design, Comment, Invitation, Message, Team};
use Illuminate\Support\Facades\Gate;
use App\Policies\{DesignPolicy, CommentPolicy, InvitationPolicy, MessagePolicy, TeamPolicy};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        Design::class => DesignPolicy::class,
        Comment::class => CommentPolicy::class,
        Team::class => TeamPolicy::class,
        Invitation::class => InvitationPolicy::class,
        Message::class => MessagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
