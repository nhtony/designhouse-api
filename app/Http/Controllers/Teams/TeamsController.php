<?php

namespace App\Http\Controllers\Teams;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Repositories\Contracts\{ITeam, IInvitaion, IUser};

class TeamsController extends Controller
{

    protected $teams;
    protected $users;
    protected $invitaions;

    public function __construct(ITeam $teams, IUser $users, IInvitaion $invitaions)
    {
        $this->teams = $teams;
        $this->users = $users;
        $this->invitaions = $invitaions;
    }

    public function index(Request $request)
    { }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:80', 'unique:teams,name']
        ]);

        // create team in database
        $teams = $this->teams->create([
            'owner_id' => auth()->id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        // current user is inserted as team member using boot method in Team model
        return new TeamResource($teams);
    }

    public function findById(Request $request, $id)
    {
        $team = $this->teams->find($id);

        return new TeamResource($team);
    }

    public function findBySlug(Request $request, $slug)
    { }

    // Get the team that the current user belongs to
    public function fetchUserTeams()
    {
        $teams = $this->teams->fetchUserTeams();
        return TeamResource::collection($teams);
    }

    public function update(Request $request, $id)
    {
        $team = $this->teams->find($id);
        $this->authorize('update', $team);

        $this->validate($request, [
            'name' => ['required', 'string', 'max:80', 'unique:teams,name,'.$id]
        ]);

        $team = $this->teams->update($id, [
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return new TeamResource($team);
    }

    public function destroy(Request $request, $id)
    {
        $team = $this->teams->find($id);
        $this->authorize('delete', $team);

        $team->delete();

        return response()->json(['message' => 'Deleted'], 200);
    }

    public function removeFromTeam($teamId, $userId)
    {
        $team = $this->teams->find($teamId);
        $user = $this->users->find($userId);

        // check if the user own the team
        if ($user->isOwnerOfTeam($team)) {
            return response()->json(['email' => 'You are the team owner'], 401);
        }

        // check that the person sending the requet
        // is either the owner of the team or the person
        // who wants to leave the team
        if (!auth()->user()->isOwnerOfTeam($team) &&
            auth()->id() !== $user->id) {
            return response()->json(['email' => 'You can not do this'], 401);
        }

        $this->invitaions->removeUserFromTeam($team, $userId);

        return response()->json(['message' => 'Success'], 200);
    }
}
