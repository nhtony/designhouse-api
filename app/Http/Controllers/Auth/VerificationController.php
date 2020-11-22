<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Contracts\IUser;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class VerificationController extends Controller
{
    protected $users;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(IUser $users)
    {
        $this->users = $users;
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    public function verfiy(Request $request, User $user)
    {
        // check if the url is a valid signed url
        if (!URL::hasValidSignature($request)) {
            return response()->json(["errors" => [
                "message" => "Invalid verification link or signature",
            ]], 422);
        }

        // check if user has already verified account
        if ($user->hasVerifiedEmail()) {
            return response()->json(["errors" => [
                "message" => "Email address already verified",
            ]], 422);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json(["message" => "Email successfully verify"], 200);
    }

    public function resend(Request $request, User $user)
    {
        $this->validate($request, [
            'email' => ['email', 'required'],
        ]);

        $user = $this->users->findWhereFirst('email', $request->email);
        if (!$user) {
            return response()->json(['errors' => [
                'email' => 'No user could be found with this email address',
            ]], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(["errors" => [
                "message" => "Email address already verified",
            ]], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['status' => 'verification link resent']);
    }
}
