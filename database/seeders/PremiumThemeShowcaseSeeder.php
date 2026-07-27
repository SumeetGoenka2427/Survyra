<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\QuestionType;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Models\SurveyTheme;
use App\Services\SurveyService;
use Illuminate\Database\Seeder;

/**
 * The 12 "premium survey design systems" concepts, made real: one SurveyTheme
 * row + one published demo survey per concept, all under a single "Design
 * Gallery" demo client. custom_css targets the actual selectors in
 * public/assets/css/survey-experience.css (.sq-btn, .sq-card, .survey-card,
 * .btn-survyra-primary, etc.) - not the mockup showcase's own .mk-* classes,
 * which only ever existed in the standalone HTML artifact.
 *
 * The theme's `font` column is a best-effort label only (no webfont is ever
 * loaded, so anything beyond a system-installed name like Georgia/Cambria
 * silently falls back to system-ui) - anywhere a theme's identity truly
 * depends on a specific typeface (Cortex's monospace, Atelier's italic
 * serif), custom_css sets font-family explicitly on the relevant selector
 * with a safe cross-platform stack, rather than relying on the font column.
 */
class PremiumThemeShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::query()->updateOrCreate(
            ['company_name' => 'Design Gallery'],
            [
                'industry' => 'Design Showcase',
                'email' => 'gallery@survyra-demo.test',
                'status' => 'active',
                'language' => 'en',
                'timezone' => 'UTC',
            ]
        );

        foreach ($this->themeConfigs() as $config) {
            $theme = SurveyTheme::query()->updateOrCreate(
                ['name' => $config['name'], 'is_system' => true],
                [
                    'primary_color' => $config['primary_color'],
                    'secondary_color' => $config['secondary_color'],
                    'background' => $config['background'],
                    'button_style' => $config['button_style'],
                    'font' => $config['font'],
                    'progress_bar_style' => 'bar',
                    'border_radius' => $config['border_radius'],
                    'custom_css' => $config['custom_css'],
                ]
            );

            $this->buildDemoSurvey($client, $theme, $config);
        }
    }

    private function buildDemoSurvey(Client $client, SurveyTheme $theme, array $config): void
    {
        $existing = Survey::query()->where('client_id', $client->id)->where('title', $config['survey_title'])->first();
        if ($existing) {
            return; // idempotent re-seed
        }

        $template = SurveyTemplate::factory()->create(['name' => $config['survey_title'].' Template']);

        $ratingType = QuestionType::query()->where('key', $config['rating_type'])->firstOrFail();
        $radioType = QuestionType::query()->where('key', 'radio')->firstOrFail();
        $textareaType = QuestionType::query()->where('key', 'textarea')->firstOrFail();
        $emailType = QuestionType::query()->where('key', 'email')->firstOrFail();

        $template->questions()->create([
            'question_type_id' => $ratingType->id,
            'question_text' => $config['rating_question'],
            'is_required' => true,
            'order' => 0,
        ]);
        $template->questions()->create([
            'question_type_id' => $radioType->id,
            'question_text' => $config['choice_question'],
            'options' => $config['choice_options'],
            'is_required' => true,
            'order' => 1,
        ]);
        $template->questions()->create([
            'question_type_id' => $textareaType->id,
            'question_text' => $config['text_question'],
            'is_required' => false,
            'order' => 2,
        ]);
        $template->questions()->create([
            'question_type_id' => $emailType->id,
            'question_text' => $config['contact_question'],
            'is_required' => false,
            'order' => 3,
        ]);

        $admin = \App\Models\User::query()->first() ?? \App\Models\User::factory()->create(['email' => 'gallery-admin@survyra-demo.test']);
        $survey = app(SurveyService::class)->createFromTemplate($client, $template, $config['survey_title'], $admin->id);
        $survey->update([
            'theme_id' => $theme->id,
            'welcome_screen' => $config['welcome'],
        ]);
        // createFromTemplate() already seeds default thank-you rules per
        // sentiment - update the neutral one rather than insert a duplicate.
        $survey->thankyouRules()->updateOrCreate(
            ['sentiment' => 'neutral'],
            ['thank_you_message' => $config['thanks_message']]
        );

        app(SurveyService::class)->publish($survey->fresh());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function themeConfigs(): array
    {
        return [
            [
                'name' => 'Ledger', 'primary_color' => '#7a2e2e', 'secondary_color' => '#8c877d', 'background' => '#f6f5f1',
                'button_style' => 'square', 'font' => 'Georgia', 'border_radius' => 0,
                'survey_title' => 'The Quarterly Review — Reader Feedback',
                'welcome' => ['title' => 'Help us edit The Quarterly, better', 'description' => 'Four short questions from readers like you.', 'button_text' => 'Begin'],
                'rating_type' => 'nps', 'rating_question' => 'How likely are you to recommend The Quarterly to a fellow reader?',
                'choice_question' => 'Which section do you read first?', 'choice_options' => ['Long-form essays', 'Correspondence', 'Reviews', 'The back page'],
                'text_question' => 'Anything the editors should know?', 'contact_question' => 'Where should we send the editors\' reply?',
                'thanks_message' => 'Noted, with thanks. Your answers were sent to the editorial desk this morning.',
                'custom_css' => <<<'CSS'
:root { --survey-border: #e4e0d6; --survey-text: #1c1b19; --survey-muted: #8c877d; --survey-accent: #7a2e2e; }
.sq-label { font-family: Georgia, 'Iowan Old Style', serif; font-weight: 400; font-size: 1.45rem; }
.survey-card { background: transparent !important; border: none !important; box-shadow: none !important; }
.btn-survyra-primary, .btn-outline-secondary { border-radius: 0 !important; text-transform: uppercase; letter-spacing: .08em; font-size: .78rem; font-family: -apple-system, 'Segoe UI', sans-serif; }
.sq-btn, .sq-card, .sq-pill, .sq-input-modern { border-radius: 0 !important; }
.progress { background: transparent; border-bottom: 1px solid var(--survey-border); border-radius: 0; height: 2px !important; }
.progress-bar { border-radius: 0; }
CSS,
            ],
            [
                'name' => 'Halo', 'primary_color' => '#e8b872', 'secondary_color' => '#b97a8c', 'background' => '#514a86',
                'button_style' => 'rounded', 'font' => 'system-ui', 'border_radius' => 16,
                'survey_title' => 'Still — Weekly Check-In',
                'welcome' => ['title' => 'How was your week with Still?', 'description' => 'A two-minute check-in so we can tune your sessions.', 'button_text' => "Let's begin"],
                'rating_type' => 'emoji_rating', 'rating_question' => "How did today's session leave you feeling?",
                'choice_question' => 'Which time of day do you meditate most?', 'choice_options' => ['Morning', 'Midday', 'Evening', 'It varies'],
                'text_question' => 'What would make Still feel more like you?', 'contact_question' => 'Want a personal reply from our team?',
                'thanks_message' => 'Settled. Thank you for the quiet minute — your answers shape next week\'s sessions.',
                'custom_css' => <<<'CSS'
:root { --survey-border: rgba(255,255,255,.25); --survey-text: #f8f7fb; --survey-muted: #cfc9e8; --survey-accent: #e8b872; }
body { background: radial-gradient(120% 90% at 20% 0%, #7a6fc4 0%, #514a86 46%, #b97a8c 100%) fixed !important; color: #f8f7fb; }
.survey-card { background: rgba(255,255,255,.14) !important; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,.25) !important; box-shadow: 0 20px 50px rgba(0,0,0,.25) !important; }
.sq-btn, .sq-card, .sq-pill, .sq-input-modern, .sq-emoji-card { background: rgba(255,255,255,.12) !important; color: #f8f7fb; border-color: rgba(255,255,255,.25) !important; }
.sq-floating label { background: transparent; color: #cfc9e8; }
.btn-survyra-primary { background: var(--survey-accent) !important; color: #241d10 !important; box-shadow: 0 8px 20px rgba(232,184,114,.35); }
.btn-outline-secondary { color: #f8f7fb; border-color: rgba(255,255,255,.35); }
.text-muted { color: #cfc9e8 !important; }
CSS,
            ],
            [
                'name' => 'Ferrous', 'primary_color' => '#f4c21a', 'secondary_color' => '#111111', 'background' => '#f2f0e6',
                'button_style' => 'square', 'font' => 'system-ui', 'border_radius' => 0,
                'survey_title' => 'Anvil Supply — Order Feedback',
                'welcome' => ['title' => 'Rate your last order', 'description' => '4 questions. No fluff.', 'button_text' => 'Start'],
                'rating_type' => 'csat', 'rating_question' => 'Rate the build quality (1-5)',
                'choice_question' => 'What did you buy this for?', 'choice_options' => ['Jobsite', 'Workshop', 'Home repair', 'Gift'],
                'text_question' => 'What would you change?', 'contact_question' => 'Want a callback?',
                'thanks_message' => 'Logged. Your feedback goes straight to the floor manager.',
                'custom_css' => <<<'CSS'
:root { --survey-border: #111111; --survey-text: #111111; --survey-muted: #4d4a42; --survey-accent: #f4c21a; }
.sq-label { text-transform: uppercase; font-weight: 800; letter-spacing: -.01em; }
.survey-card { background: #fff !important; border: 3px solid #111 !important; border-radius: 0 !important; box-shadow: 6px 6px 0 #111 !important; }
.sq-btn, .sq-card, .sq-pill, .sq-input-modern, .btn-survyra-primary, .btn-outline-secondary, .btn-outline-danger { border: 3px solid #111 !important; border-radius: 0 !important; box-shadow: 4px 4px 0 #111; }
.btn-survyra-primary { background: var(--survey-accent) !important; color: #111 !important; text-transform: uppercase; font-weight: 800; }
.btn-survyra-primary:active, .sq-btn:active { transform: translate(4px, 4px); box-shadow: 0 0 0 #111 !important; }
.progress { border: 2px solid #111; border-radius: 0; background: #fff; }
.progress-bar { background: var(--survey-accent) !important; border-radius: 0; }
CSS,
            ],
            [
                'name' => 'Nocturne', 'primary_color' => '#c9a461', 'secondary_color' => '#8a8177', 'background' => '#161311',
                'button_style' => 'square', 'font' => 'Georgia', 'border_radius' => 2,
                'survey_title' => 'The Aldgate Room — Member Feedback',
                'welcome' => ['title' => 'A moment of your evening', 'description' => 'Four brief questions from the Aldgate Room.', 'button_text' => 'Begin'],
                'rating_type' => 'nps', 'rating_question' => 'How likely are you to recommend the Aldgate Room to someone whose company you value?',
                'choice_question' => 'Which evening did you visit?', 'choice_options' => ["Members' Dinner", 'Late Bar', 'Private Function', 'Sunday Room'],
                'text_question' => 'Anything our host should know before your next visit?', 'contact_question' => 'May we reserve your table personally next time?',
                'thanks_message' => 'Until next time. Your evening has been noted by the house.',
                'custom_css' => <<<'CSS'
:root { --survey-border: #2c2722; --survey-text: #ede8df; --survey-muted: #8a8177; --survey-accent: #c9a461; }
body { background: #161311 !important; color: #ede8df; }
.sq-label { font-family: Georgia, serif; font-weight: 400; font-size: 1.45rem; }
.survey-card { background: #1d1915 !important; border: 1px solid #2c2722 !important; box-shadow: none !important; }
.sq-btn, .sq-card, .sq-pill, .sq-input-modern, .sq-emoji-card { background: transparent !important; border-color: #2c2722 !important; color: #ede8df !important; }
.btn-survyra-primary { background: transparent !important; border: 1px solid var(--survey-accent) !important; color: var(--survey-accent) !important; }
.btn-survyra-primary:hover { background: var(--survey-accent) !important; color: #161311 !important; }
.btn-outline-secondary { color: #ede8df; border-color: #2c2722; }
.text-muted { color: #8a8177 !important; }
CSS,
            ],
            [
                'name' => 'Meridian', 'primary_color' => '#ff6b4a', 'secondary_color' => '#6c4ab6', 'background' => '#ffffff',
                'button_style' => 'pill', 'font' => 'system-ui', 'border_radius' => 18,
                'survey_title' => 'Lumen Festival — Attendee Debrief',
                'welcome' => ['title' => 'How was Lumen this year?', 'description' => 'Quick festival debrief, then back to the afterglow.', 'button_text' => "Let's go"],
                'rating_type' => 'nps', 'rating_question' => 'How likely are you to bring a friend to Lumen next year?',
                'choice_question' => 'Which stage did you spend most time at?', 'choice_options' => ['Main Stage', 'The Grove', 'Neon Tent', 'Sunrise Stage'],
                'text_question' => 'One thing we should fix for next year?', 'contact_question' => "Want early access to next year's tickets?",
                'thanks_message' => "That's a wrap. Thanks for three days of chaos and joy.",
                'custom_css' => <<<'CSS'
:root { --survey-border: #e9e6f2; --survey-text: #161221; --survey-muted: #6b6478; --survey-accent: #ff6b4a; }
body { background: linear-gradient(145deg,#ff6b4a 0%,#6c4ab6 52%,#1f3e6e 100%) fixed !important; }
.survey-card { border-radius: 22px !important; box-shadow: 0 20px 45px -12px rgba(31,62,110,.45) !important; }
.btn-survyra-primary { border-radius: 999px !important; font-weight: 800; box-shadow: 0 8px 18px rgba(255,107,74,.4); }
.sq-btn, .sq-card, .sq-pill { border-radius: 14px !important; }
.sq-nps-circles .sq-btn { border-radius: 50% !important; }
CSS,
            ],
            [
                'name' => 'Foundry', 'primary_color' => '#0e7c7b', 'secondary_color' => '#5c6c82', 'background' => '#fbfaf8',
                'button_style' => 'square', 'font' => 'Cambria', 'border_radius' => 4,
                'survey_title' => 'Northbridge Systems — Quarterly NPS',
                'welcome' => ['title' => 'Your quarterly feedback on Northbridge', 'description' => 'Four questions from your account team.', 'button_text' => 'Begin survey'],
                'rating_type' => 'nps', 'rating_question' => 'How likely are you to recommend Northbridge to another organization in your sector?',
                'choice_question' => 'Which module does your team use most?', 'choice_options' => ['Reporting', 'Provisioning', 'Billing', 'API access'],
                'text_question' => 'What would most improve your renewal decision?', 'contact_question' => 'Should your account manager follow up directly?',
                'thanks_message' => 'Response recorded. This has been routed to your account team.',
                'custom_css' => <<<'CSS'
:root { --survey-border: #d8dce2; --survey-text: #101b2e; --survey-muted: #5c6c82; --survey-accent: #0e7c7b; }
.sq-label { font-family: Cambria, Georgia, serif; font-weight: 400; }
.survey-card { border-top: 4px solid #101b2e !important; border-radius: 4px !important; box-shadow: 0 1px 3px rgba(16,27,46,.08) !important; }
.sq-btn, .sq-card, .sq-pill, .sq-input-modern { border-radius: 4px !important; }
.btn-survyra-primary { border-radius: 4px !important; letter-spacing: .02em; }
CSS,
            ],
            [
                'name' => 'Meadow', 'primary_color' => '#5c7a63', 'secondary_color' => '#c57b5b', 'background' => '#f7f3ec',
                'button_style' => 'pill', 'font' => 'system-ui', 'border_radius' => 20,
                'survey_title' => 'Riverside Physiotherapy — Visit Check-In',
                'welcome' => ['title' => 'How are you healing?', 'description' => 'A short check-in from Riverside Physiotherapy.', 'button_text' => 'Start'],
                'rating_type' => 'rating_stars', 'rating_question' => "How would you rate today's session with your therapist?",
                'choice_question' => 'How is your mobility compared to last visit?', 'choice_options' => ['Much better', 'A little better', 'About the same', 'Worse'],
                'text_question' => "Anything you'd like your therapist to know?", 'contact_question' => 'Can we check in by phone before your next session?',
                'thanks_message' => 'Thank you for sharing. Your therapist will see this before your next visit.',
                'custom_css' => <<<'CSS'
:root { --survey-border: #e3dccb; --survey-text: #2b2823; --survey-muted: #8c8778; --survey-accent: #5c7a63; }
body { font-size: 1.05rem; }
.sq-label { font-size: 1.4rem; line-height: 1.4; }
.survey-card { border-radius: 22px !important; box-shadow: 0 10px 30px -14px rgba(92,122,99,.35) !important; }
.sq-btn, .sq-card, .sq-pill, .btn-survyra-primary, .btn-outline-secondary { border-radius: 999px !important; padding-top: .8rem !important; padding-bottom: .8rem !important; }
.sq-card { padding: 1.1rem 1.3rem !important; }
CSS,
            ],
            [
                'name' => 'Cortex', 'primary_color' => '#4fd1c5', 'secondary_color' => '#5b6472', 'background' => '#0b0e14',
                'button_style' => 'square', 'font' => 'system-ui', 'border_radius' => 2,
                'survey_title' => 'Compile — Build Calibration',
                'welcome' => ['title' => "Quick calibration: how's Compile working for you?", 'description' => 'Your answers tune what we build next.', 'button_text' => 'Run survey'],
                'rating_type' => 'nps', 'rating_question' => '0-10: how likely are you to recommend Compile to another engineer?',
                'choice_question' => 'Which model do you run most?', 'choice_options' => ['Fast', 'Balanced', 'Max quality', 'Local'],
                'text_question' => "What's the one thing slowing you down?", 'contact_question' => 'Want to join the beta channel?',
                'thanks_message' => 'Signal received. This feeds directly into next sprint\'s priorities.',
                'custom_css' => <<<'CSS'
:root { --survey-border: #1d232d; --survey-text: #e4e9f0; --survey-muted: #5b6472; --survey-accent: #4fd1c5; }
body { background-color: #0b0e14 !important; background-image: radial-gradient(#1d232d 1px, transparent 1px); background-size: 16px 16px; color: #e4e9f0; }
.survey-card { background: #0f1420 !important; border: 1px solid #1d232d !important; border-radius: 2px !important; box-shadow: none !important; }
.sq-btn, .sq-card, .sq-pill, .sq-input-modern { background: transparent !important; border-color: #1d232d !important; border-radius: 2px !important; color: #e4e9f0 !important; }
.sq-nps-row .sq-btn, .sq-scale-labels, .text-muted { font-family: ui-monospace, 'SF Mono', Menlo, monospace; }
.btn-survyra-primary { border-radius: 2px !important; font-family: ui-monospace, 'SF Mono', Menlo, monospace; text-transform: uppercase; letter-spacing: .06em; font-size: .82rem; background: transparent !important; border: 1px solid var(--survey-accent) !important; color: var(--survey-accent) !important; }
.btn-survyra-primary:hover { background: var(--survey-accent) !important; color: #08211f !important; }
.btn-outline-secondary { color: #e4e9f0; border-color: #1d232d; }
.progress { background: #1d232d; border-radius: 2px; }
CSS,
            ],
            [
                'name' => 'Atelier', 'primary_color' => '#c08a2e', 'secondary_color' => '#3d2b3f', 'background' => '#f2ead8',
                'button_style' => 'square', 'font' => 'Palatino Linotype', 'border_radius' => 2,
                'survey_title' => 'Whitfield & Clay — Piece Feedback',
                'welcome' => ['title' => 'A note from Whitfield & Clay', 'description' => 'A few short questions about your piece.', 'button_text' => 'Begin'],
                'rating_type' => 'rating_stars', 'rating_question' => 'How would you rate the piece you received?',
                'choice_question' => 'What drew you to this piece?', 'choice_options' => ['The glaze', 'The form', 'A gift', "The maker's story"],
                'text_question' => "Tell us where it's living now", 'contact_question' => 'May we notify you of the next small batch?',
                'thanks_message' => 'With our thanks. Every note is read by the studio, by hand.',
                'custom_css' => <<<'CSS'
:root { --survey-border: #ddceac; --survey-text: #3d2b3f; --survey-muted: #8a7f6b; --survey-accent: #c08a2e; }
.sq-label { font-family: Palatino, 'Palatino Linotype', Georgia, serif; font-style: italic; font-weight: 400; font-size: 1.4rem; }
.survey-card { background: #faf6ea !important; border: 1px solid #ddceac !important; outline: 1px solid #ddceac; outline-offset: -8px; box-shadow: none !important; }
.btn-survyra-primary { font-family: ui-monospace, monospace; text-transform: uppercase; font-size: .72rem; letter-spacing: .1em; border-radius: 2px !important; }
.sq-btn, .sq-card, .sq-pill, .sq-input-modern { border-radius: 2px !important; }
CSS,
            ],
            [
                'name' => 'Duotone Pop', 'primary_color' => '#1b3b6f', 'secondary_color' => '#ff5f5f', 'background' => '#ffffff',
                'button_style' => 'square', 'font' => 'system-ui', 'border_radius' => 0,
                'survey_title' => 'Snackrun — Delivery Feedback',
                'welcome' => ['title' => 'Rate your last Snackrun', 'description' => '6 quick taps. No essay.', 'button_text' => "Let's go"],
                'rating_type' => 'emoji_rating', 'rating_question' => 'How was the delivery speed?',
                'choice_question' => 'What did you order?', 'choice_options' => ['Snacks', 'Drinks', 'A whole meal', 'Dessert'],
                'text_question' => 'Anything we should fix?', 'contact_question' => 'Want a discount code?',
                'thanks_message' => "Nice one. Here's 15% off your next run — code THANKS15.",
                'custom_css' => <<<'CSS'
:root { --survey-border: #dfe1ea; --survey-text: #12141c; --survey-muted: #5b5f70; --survey-accent: #ff5f5f; }
.sq-label { font-weight: 800; text-transform: uppercase; font-size: 1.15rem; }
.survey-card { border-radius: 0 !important; box-shadow: none !important; border: none !important; }
.sq-btn, .sq-card, .sq-pill, .sq-input-modern, .btn-survyra-primary, .btn-outline-secondary { border-radius: 0 !important; }
.btn-survyra-primary { font-weight: 800; text-transform: uppercase; }
.text-muted.small { font-family: ui-monospace, monospace; text-transform: uppercase; letter-spacing: .08em; }
CSS,
            ],
            [
                'name' => 'Fieldnotes', 'primary_color' => '#b23a2e', 'secondary_color' => '#948c7a', 'background' => '#ede6d6',
                'button_style' => 'square', 'font' => 'Georgia', 'border_radius' => 1,
                'survey_title' => 'Studio Session — Workshop Notes',
                'welcome' => ['title' => "Notes on today's workshop", 'description' => 'Ten minutes, in your own words where it helps.', 'button_text' => 'Begin'],
                'rating_type' => 'emoji_rating', 'rating_question' => "How did today's pace feel?",
                'choice_question' => 'Which exercise stuck with you most?', 'choice_options' => ['Type pairing', 'Grid systems', 'Kerning drill', 'Critique circle'],
                'text_question' => 'What would you tell a friend thinking of taking this?', 'contact_question' => 'Add your name to the next cohort list?',
                'thanks_message' => "Filed away. Your notes join the workshop's own — thank you for marking them up.",
                'custom_css' => <<<'CSS'
:root { --survey-border: #d8cdb0; --survey-text: #2e2b27; --survey-muted: #948c7a; --survey-accent: #b23a2e; }
body { background-color: #ede6d6 !important; background-image: radial-gradient(#c9bc98 .6px, transparent .6px); background-size: 12px 12px; }
.survey-card { background: #f4efe2 !important; border-radius: 1px !important; box-shadow: none !important; border: 1px solid #d8cdb0 !important; }
.sq-label { text-decoration: underline; text-decoration-color: var(--survey-accent); text-decoration-thickness: 2px; text-underline-offset: 5px; }
.btn-survyra-primary { font-family: ui-monospace, monospace; text-transform: uppercase; font-size: .78rem; letter-spacing: .05em; border-radius: 1px !important; }
.sq-btn, .sq-card, .sq-pill, .sq-input-modern { border-radius: 1px !important; }
CSS,
            ],
            [
                'name' => 'Aurora Soft', 'primary_color' => '#6e5ea8', 'secondary_color' => '#726c82', 'background' => '#e6e4ec',
                'button_style' => 'rounded', 'font' => 'system-ui', 'border_radius' => 16,
                'survey_title' => 'Halcyon — Money Check-In',
                'welcome' => ['title' => "How's Halcyon handling your money?", 'description' => 'A soft two-minute check-in.', 'button_text' => 'Begin'],
                'rating_type' => 'csat', 'rating_question' => 'Rate how in-control you feel of your spending (1-5)',
                'choice_question' => 'What do you use Halcyon for most?', 'choice_options' => ['Everyday spending', 'Saving pots', 'Budget tracking', 'Bill splitting'],
                'text_question' => 'What would make budgeting feel easier?', 'contact_question' => 'Want a call with a Halcyon advisor?',
                'thanks_message' => "All set. Your feedback helps shape Halcyon's next release.",
                'custom_css' => <<<'CSS'
:root { --survey-border: transparent; --survey-text: #332f3d; --survey-muted: #726c82; --survey-accent: #6e5ea8; }
body { background: #e6e4ec !important; }
.survey-card { background: #e6e4ec !important; border: none !important; box-shadow: 10px 10px 22px #c4c1ce, -10px -10px 22px #ffffff !important; }
.sq-btn, .sq-card, .sq-pill, .sq-input-modern, .sq-emoji-card { background: #e6e4ec !important; border: none !important; box-shadow: 5px 5px 10px #c4c1ce, -5px -5px 10px #ffffff; }
.sq-option-input:checked + .sq-btn, .sq-option-input:checked + .sq-card, .sq-option-input:checked + .sq-pill { box-shadow: inset 4px 4px 8px #c4c1ce, inset -4px -4px 8px #ffffff !important; background: #e6e4ec !important; color: var(--survey-accent) !important; }
.btn-survyra-primary { background: #e6e4ec !important; color: var(--survey-accent) !important; box-shadow: 5px 5px 12px #c4c1ce, -5px -5px 12px #ffffff !important; font-weight: 700; }
CSS,
            ],
        ];
    }
}
