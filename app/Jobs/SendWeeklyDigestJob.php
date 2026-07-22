<?php

namespace App\Jobs;

use App\Mail\WeeklyDigestMail;
use App\Models\Client;
use App\Models\ClientUser;
use App\Services\AiService;
use App\Services\AnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $clientId) {}

    public function handle(AiService $ai, AnalyticsService $analytics): void
    {
        $client = Client::find($this->clientId);
        if (! $client) {
            return;
        }

        $from = now()->subWeek()->startOfDay();
        $to = now()->endOfDay();

        $snapshot = $analytics->forClient($client, null, $from, $to);
        $digest = $ai->weeklyDigest($this->clientId);

        $users = ClientUser::where('client_id', $this->clientId)
            ->where('is_active', true)
            ->get();

        foreach ($users as $user) {
            Mail::to($user->email)->queue(new WeeklyDigestMail($client, $snapshot, $digest, $from, $to));
        }
    }
}