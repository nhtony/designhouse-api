<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Invitation  $invitation
     * @return mixed
     */
    public function delete(User $user, Invitation $invitation)
    {
        return $user->id === $invitation->sender_id;
    }

    public function resend(User $user, Invitation $invitation)
    {
        return $user->id === $invitation->sender_id;
    }

    public function response(User $user, Invitation $invitation)
    {
        return $user->email === $invitation->recipient_email;
    }
}
