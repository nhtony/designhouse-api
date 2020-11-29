<?php

namespace App\Repositories\Contracts;

interface IInvitaion
{
    public function addUserToTeam($team, $userId);
    public function removeUserFromTeam($team, $userId);
}
