<?php

use App\Jobs\SendWeeklyDigestJob;
use App\Mail\WeeklyDigestMail;
use App\Models\Client;
use App\Models\ClientUser;
use Illuminate\Support\Facades\Mail;

test('the weekly digest email is actually sent to the client user, not silently dropped', function () {
    // Regression test: WeeklyDigestMail used to declare constructor
    // properties named $from/$to, which collide with Mailable's own
    // built-in $from/$to recipient properties - Mail::to() appeared to
    // work but the actual recipient list got silently overwritten by the
    // date-range Carbon instances, so every digest failed at send time.
    Mail::fake();

    $client = Client::factory()->create(['status' => 'active']);
    $user = ClientUser::factory()->create(['client_id' => $client->id, 'is_active' => true]);

    SendWeeklyDigestJob::dispatchSync($client->id);

    Mail::assertQueued(WeeklyDigestMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('an inactive client user does not receive the digest', function () {
    Mail::fake();

    $client = Client::factory()->create(['status' => 'active']);
    ClientUser::factory()->create(['client_id' => $client->id, 'is_active' => false]);

    SendWeeklyDigestJob::dispatchSync($client->id);

    Mail::assertNothingQueued();
});
