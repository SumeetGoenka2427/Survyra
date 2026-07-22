<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Mail\TeamInvitationMail;
use App\Models\ClientUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isOwner(), 403);

        return view('portal.team.index', [
            'members' => ClientUser::where('client_id', $request->user()->client_id)->latest()->get(),
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isOwner(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:client_users,email'],
            'role' => ['required', 'in:editor,viewer'],
        ]);

        $token = Str::random(40);

        $member = ClientUser::create([
            'client_id' => $request->user()->client_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(32)),
            'role' => $data['role'],
            'is_active' => false,
            'invited_by' => $request->user()->id,
            'invitation_token' => $token,
        ]);

        Mail::to($member->email)->send(new TeamInvitationMail($member, $token));

        return redirect()->route('portal.team.index')->with('status', 'Invitation sent to '.$member->email);
    }

    public function acceptInvitation(string $token): View|RedirectResponse
    {
        $member = ClientUser::where('invitation_token', $token)->firstOrFail();

        return view('portal.team.accept-invitation', ['member' => $member, 'token' => $token]);
    }

    public function completeInvitation(Request $request, string $token): RedirectResponse
    {
        $member = ClientUser::where('invitation_token', $token)->firstOrFail();

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $member->update([
            'password' => $data['password'],
            'is_active' => true,
            'invitation_token' => null,
            'invitation_accepted_at' => now(),
        ]);

        return redirect()->route('portal.login')->with('status', 'Account activated. Please log in.');
    }

    public function destroy(Request $request, ClientUser $member): RedirectResponse
    {
        abort_unless($request->user()->isOwner(), 403);
        abort_if($member->id === $request->user()->id, 422);
        abort_if($member->client_id !== $request->user()->client_id, 403);

        $member->delete();

        return redirect()->route('portal.team.index')->with('status', 'Team member removed.');
    }
}
