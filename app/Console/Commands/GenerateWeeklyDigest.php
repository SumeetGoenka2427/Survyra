<?php

namespace App\Console\Commands;

use App\Jobs\SendWeeklyDigestJob;
use App\Models\Client;
use Illuminate\Console\Command;

class GenerateWeeklyDigest extends Command
{
    protected $signature = 'survyra:weekly-digest';
    protected $description = 'Generate and send weekly AI digest emails to all active clients';

    public function handle(): int
    {
        $this->info('Generating weekly digests...');

        $clients = Client::where('status', 'active')->get();
        $count = 0;

        foreach ($clients as $client) {
            if ($client->surveys()->where('status', 'published')->exists()) {
                SendWeeklyDigestJob::dispatch($client->id);
                $count++;
            }
        }

        $this->info("Dispatched {$count} weekly digests.");

        return Command::SUCCESS;
    }
}