<x-portal-layout title="Team Members">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Team Members</strong>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach ($members as $member)
                        <tr>
                            <td>{{ $member->name }} @if($member->id === auth('client')->id()) <span class="badge text-bg-secondary">You</span> @endif</td>
                            <td>{{ $member->email }}</td>
                            <td><span class="badge text-bg-light text-dark border">{{ ucfirst($member->role) }}</span></td>
                            <td>
                                @if ($member->invitation_accepted_at)
                                    <span class="badge text-bg-success">Active</span>
                                @elseif ($member->invitation_token)
                                    <span class="badge text-bg-warning">Invited</span>
                                @else
                                    <span class="badge text-bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if ($member->id !== auth('client')->id())
                                    <form method="POST" action="{{ route('portal.team.destroy', $member) }}" onsubmit="return confirm('Remove this team member?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">Invite Team Member</h6>
            <form method="POST" action="{{ route('portal.team.invite') }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <input type="text" name="name" class="form-control" placeholder="Name" required value="{{ old('name') }}">
                </div>
                <div class="col-md-4">
                    <input type="email" name="email" class="form-control" placeholder="Email" required value="{{ old('email') }}">
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="editor">Editor</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Send Invite</button>
                </div>
                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
            </form>
        </div>
    </div>
</x-portal-layout>
