<?php

namespace App\Services;

use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Survey;
use App\Notifications\CampaignSendCompleted;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CampaignService
{
    public function paginate(Client $client, int $perPage = 15): LengthAwarePaginator
    {
        return Campaign::query()
            ->where('client_id', $client->id)
            ->with('survey')
            ->withCount('recipients')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): Campaign
    {
        return Campaign::query()->with(['client', 'survey', 'recipients.contact'])->findOrFail($id);
    }

    /**
     * Builds the recipient list for a campaign from a set of contact tags,
     * silently-but-visibly excluding anyone without consent (master gap #10).
     *
     * @param  array<int, int>  $tagIds
     * @return array{campaign: Campaign, excluded_for_consent: int}
     */
    public function createWithRecipients(Client $client, Survey $survey, array $data, array $tagIds, int $createdByUserId): array
    {
        return DB::transaction(function () use ($client, $survey, $data, $tagIds, $createdByUserId) {
            $campaign = Campaign::query()->create([
                ...$data,
                'client_id' => $client->id,
                'survey_id' => $survey->id,
                'status' => 'draft',
                'created_by' => $createdByUserId,
            ]);

            $candidates = Contact::query()
                ->where('client_id', $client->id)
                ->when($tagIds, fn ($query) => $query->whereHas('tags', fn ($q) => $q->whereIn('contact_tags.id', $tagIds)))
                ->get();

            $consented = $candidates->where('consent', true);
            $excluded = $candidates->count() - $consented->count();

            foreach ($consented as $contact) {
                $campaign->recipients()->create([
                    'contact_id' => $contact->id,
                    'channel' => $campaign->type,
                    'status' => 'pending',
                ]);
            }

            return ['campaign' => $campaign, 'excluded_for_consent' => $excluded];
        });
    }

    public function send(Campaign $campaign): void
    {
        $campaign->update(['status' => 'sending']);

        SendCampaignJob::dispatch($campaign->id);
    }

    public function retryFailed(Campaign $campaign): void
    {
        $campaign->recipients()->where('status', 'failed')->update(['status' => 'pending']);
        $campaign->update(['status' => 'sending']);

        SendCampaignJob::dispatch($campaign->id);
    }

    public function refreshStats(Campaign $campaign): void
    {
        $wasAlreadyCompleted = $campaign->status === 'completed';

        $counts = $campaign->recipients()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $campaign->update(['stats' => $counts->all()]);

        if ($counts->except(['pending'])->sum() === $campaign->recipients()->count()) {
            $campaign->update(['status' => 'completed', 'sent_at' => $campaign->sent_at ?? now()]);

            if (! $wasAlreadyCompleted && $campaign->createdBy) {
                Notification::send($campaign->createdBy, new CampaignSendCompleted($campaign));
            }
        }
    }
}
