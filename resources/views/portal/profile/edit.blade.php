<x-portal-layout title="Profile">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><strong>Profile Information</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('portal.profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <x-form-input name="name" label="Name" :value="$clientUser->name" required />
                        <x-form-input name="email" label="Email" type="email" :value="$clientUser->email" required />

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><strong>Update Password</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('portal.profile.password') }}">
                        @csrf
                        @method('PUT')

                        <x-form-input name="current_password" label="Current Password" type="password" required bag="updatePassword" />
                        <x-form-input name="password" label="New Password" type="password" required bag="updatePassword" />
                        <x-form-input name="password_confirmation" label="Confirm New Password" type="password" required bag="updatePassword" />

                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-portal-layout>
