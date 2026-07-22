<?php

namespace App\Console\Commands;

use App\Models\Response;
use Illuminate\Console\Command;

class MarkAbandonedResponses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'responses:mark-abandoned {--hours=24 : Hours of inactivity before a response is considered abandoned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flag started/in-progress responses that have gone quiet as abandoned';

    public function handle(): void
    {
        $cutoff = now()->subHours((int) $this->option('hours'));

        $count = Response::query()
            ->whereIn('status', ['started', 'in_progress'])
            ->where('updated_at', '<', $cutoff)
            ->update(['status' => 'abandoned']);

        $this->info("Marked {$count} response(s) as abandoned.");
    }
}
