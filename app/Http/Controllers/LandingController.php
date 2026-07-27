<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $businessSitePrice = 7999;
        $growthPrice = 1999;
        $completePrice = 2999;
        $completeFoundingPrice = 1999;

        return view('landing.index', [
            'websitePlans' => [
                [
                    'name' => 'Starter Site',
                    'price' => 4999,
                    'priceLabel' => null,
                    'tagline' => '3-4 pages to get your business online.',
                    'highlighted' => false,
                    'features' => [
                        '3-4 page mobile-responsive website',
                        'Domain + hosting for Year 1 included',
                        'WhatsApp / contact integration',
                        'Basic on-page SEO',
                        'Delivered in 5-7 days',
                    ],
                    'renewal' => 'Renews at ~₹1,999/year from Year 2',
                ],
                [
                    'name' => 'Business Site',
                    'price' => $businessSitePrice,
                    'priceLabel' => null,
                    'tagline' => '7-9 page dynamic site with an admin panel.',
                    'highlighted' => true,
                    'features' => [
                        'Everything in Starter Site',
                        '7-9 pages, dynamic content',
                        'Admin panel to edit content yourself',
                        'Domain + hosting for Year 1 included',
                        'Delivered in 7-10 days',
                    ],
                    'renewal' => 'Renews at ~₹2,499/year from Year 2',
                ],
                [
                    'name' => 'Custom',
                    'price' => null,
                    'priceLabel' => 'From ₹14,999',
                    'tagline' => 'Fully custom design and features.',
                    'highlighted' => false,
                    'features' => [
                        'Fully custom design',
                        'E-commerce, booking or other custom features',
                        'Scoped after a discovery call',
                    ],
                    'renewal' => null,
                ],
            ],
            'feedbackPlans' => [
                [
                    'name' => 'Feedback Basic',
                    'price' => 999,
                    'tagline' => 'For businesses who already have a website.',
                    'highlighted' => false,
                    'features' => [
                        'QR code + survey link',
                        'Feedback dashboard',
                        'Up to 100 responses/month',
                    ],
                ],
                [
                    'name' => 'Growth',
                    'price' => $growthPrice,
                    'tagline' => 'Unlimited feedback, plus review growth.',
                    'highlighted' => true,
                    'features' => [
                        'Everything in Feedback Basic',
                        'Unlimited responses',
                        'Review-growth funnel',
                        'Monthly feedback report',
                        'WhatsApp feedback requests',
                    ],
                ],
            ],
            'completePlan' => [
                'name' => 'Complete',
                'price' => $completePrice,
                'foundingPrice' => $completeFoundingPrice,
                'tagline' => 'Website + full feedback & review system, one monthly plan.',
                'features' => [
                    'Business Site (7-9 pages), maintained monthly',
                    'Custom domain + hosting included',
                    'Everything in Growth (unlimited responses, review funnel, monthly report, WhatsApp requests)',
                    'Basic SEO',
                    'Ongoing monthly website updates',
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
                    'a' => 'Survyra is a customer feedback and review-growth platform for small businesses. It combines customer surveys, QR feedback, review requests, analytics and a professional business website.',
                ],
                [
                    'q' => 'How does the customer survey work?',
                    'a' => 'Customers scan a QR code or open a survey link, answer a few questions and submit their feedback from their phone.',
                ],
                [
                    'q' => 'Do I need an app?',
                    'a' => 'No. Customers can complete surveys directly from their mobile browser, and business owners view results from a web dashboard.',
                ],
                [
                    'q' => 'Can I use my own domain?',
                    'a' => 'Yes. Every website plan, and the Complete plan, includes a custom domain and hosting for the first year. Feedback Basic and Growth plans assume you already have a website and domain in place.',
                ],
                [
                    'q' => 'Is a website included?',
                    'a' => 'A website can be purchased as its own one-time plan (from ₹4,999), or bundled into the monthly Complete plan alongside feedback and review tools. The Feedback Basic and Growth plans don\'t include a website — they\'re designed for businesses that already have one.',
                ],
                [
                    'q' => 'Can I collect negative feedback privately?',
                    'a' => 'Yes. Businesses can offer a private feedback path so concerns can be captured and addressed directly, alongside an easy path for happy customers to share their experience publicly.',
                ],
                [
                    'q' => 'Can I see survey results?',
                    'a' => 'Yes. Business owners can view ratings, feedback and survey analytics from their dashboard.',
                ],
                [
                    'q' => 'Can I use a QR code?',
                    'a' => 'Yes. Each survey can be shared through QR codes, links and supported messaging channels like WhatsApp.',
                ],
                [
                    'q' => 'How quickly can my website go live?',
                    'a' => 'Standard websites can typically be prepared within about a week after we receive your business information and content.',
                ],
                [
                    'q' => 'How much does Survyra cost?',
                    'a' => 'Websites start at ₹4,999 as a one-time payment. Feedback and review-growth plans start at ₹999/month for businesses that already have a website. Or get everything in one monthly plan (Complete) at ₹2,999/month — with a limited Founding Client rate of ₹1,999/month for the first 10 businesses.',
                ],
                [
                    'q' => 'What is customer feedback software?',
                    'a' => 'Customer feedback software helps businesses collect, organize and analyze customer opinions through surveys, ratings, forms and other feedback channels.',
                ],
                [
                    'q' => 'How can a small business get more customer reviews?',
                    'a' => 'A business can make review requests easy to access after a customer interaction, such as through QR codes, links or follow-up messages, while using feedback to identify areas for improvement first.',
                ],
                [
                    'q' => 'How can restaurants collect customer feedback?',
                    'a' => 'Restaurants and cafes typically place a QR code on the table, receipt or counter so customers can share feedback about their visit right after paying.',
                ],
                [
                    'q' => 'How can salons collect customer feedback?',
                    'a' => 'Salons and spas can share a QR code or link at checkout, or after an appointment, so customers can rate their experience while it is still fresh.',
                ],
                [
                    'q' => 'Is my customers\' data kept private?',
                    'a' => 'Yes. Customer contact details are encrypted in storage, and only authorized members of your business can access your feedback data.',
                ],
            ],
        ]);
    }
}
