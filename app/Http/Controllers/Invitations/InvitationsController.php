<?php

namespace App\Http\Controllers\Invitations;

use Mail;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\SendInvitationToJoinTeam;
use App\Repositories\Contracts\{IInvitaion, ITeam, IUser};


class InvitationsController extends Controller
{
    protected $invitations;
    protected $teams;
    protected $users;

    public function __construct(
        IInvitaion $invitations,
        ITeam $teams,
        IUser $users
    ) {
        $this->invitations = $invitations;
        $this->teams = $teams;
        $this->users = $users;
    }

    public function invite(Request $request, $teamId)
    {
        // get the team
        $team = $this->teams->find($teamId);

        $this->validate($request, [
            'email' => ['required', 'email']
        ]);
        $user = auth()->user();

        // check if the user own the team
        if (!$user->isOwnerOfTeam($team)) {
            return response()->json(['email' => 'You are not the team owner'], 401);
        }

        // check if the email has pending invitation
        if ($team->hasPendingInvite($request->email)) {
            return response()->json(['email' => 'Email already has a pending invite'], 422);
        }

        // get the recipient by email
        $recipient = $this->users->findByEmail($request->email);
        // if the recipient does not exist, send invitation to join the team
        if (!$recipient) {
            $this->createInvitation(false, $team, $request->email);
            return response()->json(['email' => 'Invitation sent to user'], 200);
        }

        // check if the team already has the user
        if ($team->hasUser($recipient)) {
            return response()->json(['email' => 'This user seem to be a team member'], 422);
        }

        // send invitation to the user
        $this->createInvitation(true, $team, $request->email);
        return response()->json(['email' => 'Invitation sent to user'], 200);
    }

    public function resend($id)
    {
        $invitation = $this->invitations->find($id);

        // check if the user own the team
        $this->authorize('resend', $invitation);

        $recipient = $this->users->findByEmail($invitation->recipient_email);

        Mail::to($invitation->recipient_email)
            ->send(new SendInvitationToJoinTeam($invitation, !is_null($recipient)));

        return response()->json(['email' => 'Invitation resent'], 200);
    }

    public function response(Request $request, $id)
    {
        $this->validate($request, [
            'token' => ['required'],
            'decision' => ['required']
        ]);

        $token = $request->token;
        $decision = $request->decision;
        $invitation = $this->invitations->find($id);

        // check if the invitation belongs to this user
        $this->authorize('response', $invitation);

        // check to make sure the token match
        if ($invitation->token !== $token) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // check if accept
        if ($decision !== 'deny') {
            $this->invitations->addUserToTeam($invitation->team, auth()->id());
        }

        $invitation->delete();

        return response()->json(['message' => 'Successfully'], 200);
    }

    public function destroy($id)
    {
        $invitation = $this->invitations->find($id);
        $this->authorize('delete', $invitation);
        $invitation->delete();

        return response()->json(['message' => 'Deleted'], 200);
    }

    protected function createInvitation(bool $user_exist, Team $team, string $email)
    {
        $invitation = $this->invitations->create([
            'team_id' => $team->id,
            'sender_id' => auth()->id(),
            'recipient_email' => $email,
            'token' => md5(uniqid(microtime()))
        ]);

        Mail::to($email)
            ->send(new SendInvitationToJoinTeam($invitation, $user_exist));
    }
}
