<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Contact;
use App\Models\QuestionType;
use App\Models\Response as SurveyResponse;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Models\SurveyTheme;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\NegativeFeedbackReceived;
use App\Services\CampaignService;
use App\Services\SurveyService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Notification;

class DemoClientsSeeder extends Seeder
{
    private const COMMENTS = [
        'positive' => [
            'Excellent experience overall, will definitely come back!',
            'Really impressed with how everything was handled, keep it up.',
            'Staff were friendly and very helpful throughout.',
            'Exceeded my expectations, highly recommend.',
        ],
        'neutral' => [
            'It was okay, nothing particularly stood out.',
            'Average experience, could be a bit better.',
            'Met my expectations but nothing more.',
            'Decent, but there is room for improvement.',
        ],
        'negative' => [
            'Very disappointed with the experience.',
            'Had to wait far too long, not happy about it.',
            'Would not recommend based on this visit.',
            'Several things went wrong that need fixing.',
        ],
    ];

    public function run(SurveyService $surveyService, CampaignService $campaignService): void
    {
        $survyraAdmin = User::query()->where('email', 'admin@survyra.com')->firstOrFail();
        $growthPlanId = SubscriptionPlan::query()->where('slug', 'growth')->value('id');

        foreach ($this->clientProfiles() as $profile) {
            $client = Client::query()->updateOrCreate(
                ['company_name' => $profile['company_name']],
                [
                    'industry' => $profile['industry'],
                    'email' => $profile['email'],
                    'phone' => $profile['phone'],
                    'website' => $profile['website'],
                    'address' => $profile['address'],
                    'google_review_url' => $profile['google_review_url'],
                    'facebook_url' => $profile['facebook_url'],
                    'support_number' => $profile['phone'],
                    'whatsapp_number' => $profile['phone'],
                    'brand_color' => $profile['brand_color'],
                    'secondary_color' => $profile['secondary_color'],
                    'status' => 'active',
                    'timezone' => 'Asia/Kolkata',
                    'language' => 'en',
                    'subscription_plan_id' => $growthPlanId,
                    'created_by' => $survyraAdmin->id,
                ]
            );

            $owner = ClientUser::query()->updateOrCreate(
                ['email' => $profile['owner_email']],
                [
                    'client_id' => $client->id,
                    'name' => $profile['owner_name'],
                    'password' => 'password',
                    'role' => 'owner',
                    'is_active' => true,
                ]
            );

            $survey = Survey::query()->where('client_id', $client->id)->first();

            if (! $survey) {
                $template = SurveyTemplate::query()->where('name', $profile['template'])->firstOrFail();
                $theme = SurveyTheme::query()->where('name', $profile['theme'])->firstOrFail();

                $survey = $surveyService->createFromTemplate($client, $template, $profile['survey_title'], $survyraAdmin->id);
                $survey->update(['theme_id' => $theme->id]);

                if (isset($profile['bonus_question'])) {
                    $this->addBonusQuestion($survey, $profile['bonus_question']);
                }

                $survey = $surveyService->publish($survey->fresh());
            }

            if ($survey->responses()->count() > 0) {
                continue;
            }

            $contacts = Contact::factory($profile['contact_count'])->create(['client_id' => $client->id]);

            $campaign = $this->seedCampaign($client, $survey, $contacts, $survyraAdmin, $campaignService, $profile['campaign_type']);

            $this->seedResponses($client, $survey->fresh(['questions.questionType']), $contacts, $campaign, $owner, $profile['response_count']);
        }
    }

    private function addBonusQuestion(Survey $survey, array $definition): void
    {
        $questionType = QuestionType::query()->where('key', $definition['type'])->firstOrFail();
        $nextOrder = ($survey->questions()->max('order') ?? -1) + 1;

        $survey->questions()->create([
            'question_type_id' => $questionType->id,
            'question_text' => $definition['text'],
            'options' => $definition['options'] ?? null,
            'settings' => $definition['settings'] ?? null,
            'order' => $nextOrder,
            'is_required' => false,
        ]);
    }

    private function seedCampaign(
        Client $client,
        Survey $survey,
        \Illuminate\Support\Collection $contacts,
        User $createdBy,
        CampaignService $campaignService,
        string $type
    ): \App\Models\Campaign {
        $result = $campaignService->createWithRecipients(
            $client,
            $survey,
            [
                'name' => "{$survey->title} - Launch {$type}",
                'type' => $type,
                'message_template' => "Hi {name}, we'd love your feedback! Please share your experience: {survey_link}",
                'provider' => $type === 'email' ? 'smtp' : 'msg91',
            ],
            [],
            $createdBy->id
        );

        $campaign = $result['campaign'];

        foreach ($campaign->recipients as $index => $recipient) {
            $roll = $index % 10;
            $status = match (true) {
                $roll < 7 => 'delivered',
                $roll < 9 => 'sent',
                default => 'failed',
            };

            $recipient->update([
                'status' => $status,
                'sent_at' => now()->subDays(random_int(5, 60)),
                'delivered_at' => $status === 'delivered' ? now()->subDays(random_int(4, 59)) : null,
                'error_message' => $status === 'failed' ? 'Number unreachable' : null,
            ]);
        }

        $campaignService->refreshStats($campaign);

        return $campaign->fresh(['recipients']);
    }

    private function seedResponses(
        Client $client,
        Survey $survey,
        \Illuminate\Support\Collection $contacts,
        \App\Models\Campaign $campaign,
        ClientUser $owner,
        int $count
    ): void {
        $deliveredRecipients = $campaign->recipients->where('status', 'delivered')->values();
        $negativeNotified = 0;

        for ($i = 0; $i < $count; $i++) {
            $bucket = match (true) {
                $i % 20 < 12 => 'positive',
                $i % 20 < 17 => 'neutral',
                default => 'negative',
            };

            $startedAt = now()->subDays(random_int(0, 90))->subMinutes(random_int(0, 1439));
            $useCampaignSource = $deliveredRecipients->isNotEmpty() && $i % 3 === 0;
            $recipient = $useCampaignSource ? $deliveredRecipients[$i % $deliveredRecipients->count()] : null;

            $response = SurveyResponse::query()->create([
                'client_id' => $client->id,
                'survey_id' => $survey->id,
                'contact_id' => $recipient?->contact_id,
                'campaign_id' => $recipient ? $campaign->id : null,
                'status' => 'completed',
                'device' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
                'browser' => fake()->randomElement(['Chrome', 'Safari', 'Firefox', 'Edge']),
                'ip' => fake()->ipv4(),
                'source' => $recipient ? $campaign->type : fake()->randomElement(['direct', 'qr', 'direct']),
                'started_at' => $startedAt,
                'completed_at' => $startedAt->copy()->addMinutes(random_int(1, 6)),
            ]);

            $primaryScore = null;

            foreach ($survey->questions as $question) {
                $contract = $question->questionType->contract();
                $settings = array_merge($question->settings ?? [], ['options' => $question->options ?? []]);
                $answer = $this->generateAnswer($question, $bucket);

                if ($answer === null) {
                    continue;
                }

                $score = $contract->score($answer, $settings);

                $response->answers()->create([
                    'question_id' => $question->id,
                    'answer' => $answer,
                    'score' => $score,
                ]);

                if ($question->id === $survey->primary_score_question_id) {
                    $primaryScore = $score;
                }
            }

            $response->update(['score' => $primaryScore, 'sentiment' => $bucket]);

            if ($bucket === 'negative' && $negativeNotified < 5) {
                // sendNow bypasses ShouldQueue so the demo notification bell has
                // real rows immediately, without requiring a queue worker to run.
                Notification::sendNow($owner, new NegativeFeedbackReceived($response));
                $negativeNotified++;
            }

            if ($bucket === 'positive' && $i % 4 === 0) {
                \App\Models\ReviewClick::query()->create([
                    'response_id' => $response->id,
                    'client_id' => $client->id,
                    'channel' => fake()->randomElement(['google_review', 'facebook', 'website']),
                    'clicked_at' => $response->completed_at->copy()->addMinutes(random_int(1, 30)),
                ]);
            }
        }
    }

    private function generateAnswer(SurveyQuestion $question, string $bucket): mixed
    {
        $key = $question->questionType->key;
        $options = $question->options ?? [];
        $settings = $question->settings ?? [];

        return match ($key) {
            'nps' => match ($bucket) {
                'positive' => random_int(9, 10),
                'neutral' => random_int(7, 8),
                default => random_int(0, 6),
            },
            'csat' => match ($bucket) {
                'positive' => random_int(4, 5),
                'neutral' => 3,
                default => random_int(1, 2),
            },
            'ces' => match ($bucket) {
                'positive' => random_int(6, 7),
                'neutral' => random_int(4, 5),
                default => random_int(1, 3),
            },
            'rating_stars', 'emoji_rating' => match ($bucket) {
                'positive' => random_int(4, 5),
                'neutral' => 3,
                default => random_int(1, 2),
            },
            'slider' => match ($bucket) {
                'positive' => random_int(8, (int) ($settings['scale_max'] ?? 10)),
                'neutral' => random_int(5, 7),
                default => random_int((int) ($settings['scale_min'] ?? 0), 4),
            },
            'yes_no' => $bucket === 'negative' ? 'no' : 'yes',
            'radio', 'dropdown' => $this->pickOption($options, $bucket),
            'checkbox' => fake()->randomElements($options, min(count($options), random_int(1, max(1, count($options) - 1)))),
            'textarea' => fake()->randomElement(self::COMMENTS[$bucket]),
            'matrix' => collect(range(0, count($options) - 1))->mapWithKeys(fn ($row) => [
                $row => match ($bucket) {
                    'positive' => random_int(4, 5),
                    'neutral' => 3,
                    default => random_int(1, 2),
                },
            ])->all(),
            'ranking' => collect($options)->shuffle()->values()->all(),
            default => null,
        };
    }

    private function pickOption(array $options, string $bucket): ?string
    {
        if ($options === []) {
            return null;
        }

        $hasYes = in_array('Yes', $options, true);
        $hasNo = in_array('No', $options, true);

        if ($hasYes && $hasNo) {
            return match ($bucket) {
                'positive' => 'Yes',
                'negative' => 'No',
                default => fake()->randomElement($options),
            };
        }

        return fake()->randomElement($options);
    }

    private function clientProfiles(): array
    {
        return [
            [
                'company_name' => 'Sunrise Family Clinic',
                'industry' => 'Healthcare',
                'email' => 'contact@sunrisefamilyclinic.test',
                'phone' => '+911234500001',
                'website' => 'https://sunrisefamilyclinic.test',
                'address' => '12 MG Road, Bengaluru, India',
                'google_review_url' => 'https://g.page/sunrise-family-clinic',
                'facebook_url' => 'https://facebook.com/sunrisefamilyclinic',
                'brand_color' => '#0f9d8c',
                'secondary_color' => '#4a6572',
                'owner_name' => 'Dr. Ananya Rao',
                'owner_email' => 'owner@sunrisefamilyclinic.test',
                'template' => 'Patient Satisfaction',
                'theme' => 'Healthcare',
                'survey_title' => 'Patient Satisfaction Survey',
                'campaign_type' => 'sms',
                'contact_count' => 40,
                'response_count' => 55,
            ],
            [
                'company_name' => 'Spice Route Bistro',
                'industry' => 'Restaurant',
                'email' => 'contact@spiceroutebistro.test',
                'phone' => '+911234500002',
                'website' => 'https://spiceroutebistro.test',
                'address' => '45 Church Street, Bengaluru, India',
                'google_review_url' => 'https://g.page/spice-route-bistro',
                'facebook_url' => 'https://facebook.com/spiceroutebistro',
                'brand_color' => '#8b5e34',
                'secondary_color' => '#c98a3e',
                'owner_name' => 'Rohan Mehta',
                'owner_email' => 'owner@spiceroutebistro.test',
                'template' => 'Dining Experience',
                'theme' => 'Cafe',
                'survey_title' => 'Dining Experience Survey',
                'campaign_type' => 'whatsapp',
                'contact_count' => 50,
                'response_count' => 65,
                'bonus_question' => [
                    'type' => 'ranking',
                    'text' => 'Rank these aspects by how important they are to you',
                    'options' => ['Food Quality', 'Service', 'Ambience', 'Price'],
                ],
            ],
            [
                'company_name' => 'CloudDesk Support',
                'industry' => 'Customer Support',
                'email' => 'contact@clouddesksupport.test',
                'phone' => '+911234500003',
                'website' => 'https://clouddesksupport.test',
                'address' => 'WeWork, Cyber City, Gurugram, India',
                'google_review_url' => 'https://g.page/clouddesk-support',
                'facebook_url' => 'https://facebook.com/clouddesksupport',
                'brand_color' => '#1f3a5f',
                'secondary_color' => '#64748b',
                'owner_name' => 'Priya Nair',
                'owner_email' => 'owner@clouddesksupport.test',
                'template' => 'CSAT Survey',
                'theme' => 'Corporate',
                'survey_title' => 'Support Ticket CSAT Survey',
                'campaign_type' => 'email',
                'contact_count' => 35,
                'response_count' => 48,
                'bonus_question' => [
                    'type' => 'slider',
                    'text' => 'Overall, how would you rate your support experience?',
                    'settings' => ['scale_min' => 0, 'scale_max' => 10, 'low_label' => 'Poor', 'high_label' => 'Excellent'],
                ],
            ],
            [
                'company_name' => 'Bright Minds Institute',
                'industry' => 'Education',
                'email' => 'contact@brightmindsinstitute.test',
                'phone' => '+911234500004',
                'website' => 'https://brightmindsinstitute.test',
                'address' => '9 College Road, Pune, India',
                'google_review_url' => 'https://g.page/bright-minds-institute',
                'facebook_url' => 'https://facebook.com/brightmindsinstitute',
                'brand_color' => '#ff6b6b',
                'secondary_color' => '#4ecdc4',
                'owner_name' => 'Kavita Iyer',
                'owner_email' => 'owner@brightmindsinstitute.test',
                'template' => 'Course Feedback',
                'theme' => 'Modern',
                'survey_title' => 'Course Feedback Survey',
                'campaign_type' => 'sms',
                'contact_count' => 30,
                'response_count' => 40,
            ],
            [
                'company_name' => 'UrbanStyle Retail Co.',
                'industry' => 'Retail',
                'email' => 'contact@urbanstyleretail.test',
                'phone' => '+911234500005',
                'website' => 'https://urbanstyleretail.test',
                'address' => '3 Linking Road, Mumbai, India',
                'google_review_url' => 'https://g.page/urbanstyle-retail',
                'facebook_url' => 'https://facebook.com/urbanstyleretail',
                'brand_color' => '#111111',
                'secondary_color' => '#6b7280',
                'owner_name' => 'Aditya Sharma',
                'owner_email' => 'owner@urbanstyleretail.test',
                'template' => 'Store Experience',
                'theme' => 'Minimal',
                'survey_title' => 'Store Visit Feedback',
                'campaign_type' => 'whatsapp',
                'contact_count' => 45,
                'response_count' => 60,
                'bonus_question' => [
                    'type' => 'matrix',
                    'text' => 'Please rate the following aspects of your visit',
                    'options' => ['Store Layout', 'Staff Friendliness', 'Product Range', 'Checkout Speed'],
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'low_label' => 'Poor', 'high_label' => 'Excellent'],
                ],
            ],
        ];
    }
}
