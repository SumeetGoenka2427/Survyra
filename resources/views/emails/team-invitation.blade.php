<!DOCTYPE html>
<html>
<body>
<p>Hi {{ $member->name }},</p>
<p>You have been invited to join <strong>{{ $member->client->company_name }}</strong> on Survyra as a <strong>{{ $member->role }}</strong>.</p>
<p><a href="{{ url('/portal/team/accept-invitation/' . $token) }}">Accept Invitation & Set Password</a></p>
<p>This link is valid until you accept it.</p>
</body>
</html>
