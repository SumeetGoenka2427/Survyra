<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        return view('landing.index', [
            'feedbackPlans' => [
                [
                    'name' => 'Starter',
                    'price' => 999,
                    'tagline' => 'Everything you need to start collecting feedback.',
                    'highlighted' => false,
                    'features' => [
                        'QR code + survey link',
                        'Feedback dashboard',
                        'Up to 100 responses/month',
                    ],
                ],
                [
                    'name' => 'Growth',
                    'price' => 1999,
                    'tagline' => 'Unlimited feedback, plus review growth.',
                    'highlighted' => true,
                    'features' => [
                        'Everything in Starter',
                        'Unlimited responses',
                        'Review-growth funnel',
                        'Monthly feedback report',
                        'WhatsApp feedback requests',
                    ],
                ],
            ],
            'businessTypes' => [
                ['icon' => 'bi-scissors', 'name' => 'Salons & Spas', 'description' => 'Collect feedback after appointments and understand what customers love.'],
                ['icon' => 'bi-cup-hot', 'name' => 'Restaurants & Cafes', 'description' => 'Use table or counter QR codes to capture the dining experience.'],
                ['icon' => 'bi-heart-pulse', 'name' => 'Clinics', 'description' => 'Collect patient experience feedback through simple mobile surveys.'],
                ['icon' => 'bi-bicycle', 'name' => 'Gyms', 'description' => 'Understand member satisfaction and identify improvement areas.'],
                ['icon' => 'bi-building', 'name' => 'Hotels', 'description' => 'Collect guest feedback before they check out.'],
                ['icon' => 'bi-shop', 'name' => 'Local Services', 'description' => 'Turn everyday customer interactions into useful feedback.'],
            ],
            'faqs' => [
                [
                    'q' => 'What is Survyra?',
                    'a' => 'Survyra is customer survey software for small businesses. It combines a smart survey builder, QR and link feedback, review growth and analytics in one simple dashboard.',
                ],
                [
                    'q' => 'What are the benefits of using customer surveys?',
                    'a' => 'Customer surveys replace guesswork with real data. They help you catch unhappy customers before they leave a bad review, spot recurring problems early, track satisfaction over time, and identify your happiest customers at the exact moment to ask for a public review.',
                ],
                [
                    'q' => 'How does the customer survey process work?',
                    'a' => 'A customer scans a QR code or opens a survey link, answers a short survey from their phone, and their response lands instantly in your dashboard. Happy customers are guided toward a public review; unhappy ones are routed straight to you so you can follow up.',
                ],
                [
                    'q' => 'Do I need an app?',
                    'a' => 'No. Customers can complete surveys directly from their mobile browser, and business owners view results from a web dashboard.',
                ],
                [
                    'q' => 'What kinds of survey questions can I create?',
                    'a' => 'Surveys are built around your business — choose from multiple question types (ratings, multiple choice, open text and more) and add branching logic so the next question adapts to how a customer just answered.',
                ],
                [
                    'q' => 'Can I collect negative feedback privately?',
                    'a' => 'Yes. Businesses can offer a private feedback path so concerns can be captured and addressed directly, alongside an easy path for happy customers to share their experience publicly.',
                ],
                [
                    'q' => 'Can I see and export my survey results?',
                    'a' => 'Yes. Business owners can view ratings, feedback and survey analytics from their dashboard, and export reports to PDF, Excel or CSV whenever they need them.',
                ],
                [
                    'q' => 'Can I use a QR code?',
                    'a' => 'Yes. Each survey can be shared through QR codes, links and supported messaging channels like WhatsApp.',
                ],
                [
                    'q' => 'How quickly can I start collecting feedback?',
                    'a' => 'Most businesses are set up and collecting their first survey responses within a day of signing up.',
                ],
                [
                    'q' => 'How much does Survyra cost?',
                    'a' => 'Starter plans begin at ₹999/month for QR survey feedback and a feedback dashboard. Growth, at ₹1,999/month, adds unlimited responses, review-growth tools and WhatsApp feedback requests.',
                ],
                [
                    'q' => 'What is customer feedback software?',
                    'a' => 'Customer feedback software helps businesses collect, organize and analyze customer opinions through surveys, ratings, forms and other feedback channels.',
                ],
                [
                    'q' => 'How can a small business get more customer reviews?',
                    'a' => 'A business can make review requests easy to access after a customer interaction, such as through QR codes, links or follow-up messages, while using survey feedback to identify areas for improvement first.',
                ],
                [
                    'q' => 'How can restaurants collect customer feedback?',
                    'a' => 'Restaurants and cafes typically place a QR code on the table, receipt or counter so customers can complete a short survey about their visit right after paying.',
                ],
                [
                    'q' => 'How can salons collect customer feedback?',
                    'a' => 'Salons and spas can share a QR code or survey link at checkout, or after an appointment, so customers can rate their experience while it is still fresh.',
                ],
                [
                    'q' => 'Is my customers\' data kept private?',
                    'a' => 'Yes. Customer contact details are encrypted in storage, and only authorized members of your business can access your survey data.',
                ],
            ],
        ]);
    }
}
