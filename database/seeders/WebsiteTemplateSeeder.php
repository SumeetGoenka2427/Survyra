<?php

namespace Database\Seeders;

use App\Models\WebsiteTheme;
use App\Models\WebsiteTemplate;
use Illuminate\Database\Seeder;

class WebsiteTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->definitions() as $definition) {
            $themeId = WebsiteTheme::query()
                ->where('name', $definition['theme_name'])
                ->where('is_system', true)
                ->value('id');

            WebsiteTemplate::query()->updateOrCreate(
                ['key' => $definition['key']],
                [
                    'name' => $definition['name'],
                    'industry' => $definition['industry'],
                    'description' => $definition['description'],
                    'preview_image' => $definition['preview_image'],
                    'theme_id' => $themeId,
                    'structure' => $definition['structure'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function definitions(): array
    {
        return [
            $this->healthcare(),
            $this->restaurant(),
            $this->education(),
            $this->retail(),
        ];
    }

    private function healthcare(): array
    {
        return [
            'key' => 'healthcare-clinic',
            'name' => 'Healthcare / Clinic',
            'industry' => 'Healthcare',
            'description' => 'A calming, trust-building starter site for clinics, doctors, and healthcare practices - services, about, and a contact/appointment page.',
            'preview_image' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=600&q=80',
            'theme_name' => 'Healthcare',
            'structure' => [
                'pages' => [
                    [
                        'title' => 'Home', 'is_home' => true,
                        'sections' => [
                            ['type' => 'hero', 'style' => 'split-image', 'content' => [
                                'heading' => 'Compassionate Care for Every Patient',
                                'subheading' => 'From routine checkups to specialized care, our team is here for every stage of life.',
                                'cta_text' => 'Book an Appointment', 'cta_link' => null, 'background_image' => 'https://images.unsplash.com/photo-1584982751601-97dcc096659c?w=900&q=80',
                            ]],
                            ['type' => 'testimonials', 'style' => 'cards', 'content' => [
                                'items' => [
                                    ['quote' => 'The staff here actually listen and take the time to explain everything.', 'author' => 'Happy Patient', 'role' => ''],
                                    ['quote' => 'Same-day appointments made a real difference for our family.', 'author' => 'Happy Patient', 'role' => ''],
                                ],
                            ]],
                            ['type' => 'cta', 'style' => 'default', 'content' => [
                                'heading' => 'Ready to see a provider?', 'button_text' => 'Contact Us Today', 'button_link' => null, 'variant' => 'primary',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Services', 'is_home' => false,
                        'sections' => [
                            ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                                'heading' => 'Our Services',
                                'body' => "General & Family Medicine\nPreventive Care & Checkups\nVaccinations & Immunizations\nChronic Disease Management\nDiagnostics & Screenings\nUrgent Care",
                            ]],
                        ],
                    ],
                    [
                        'title' => 'About', 'is_home' => false,
                        'sections' => [
                            ['type' => 'text_block', 'style' => 'default', 'content' => [
                                'heading' => 'About Our Practice',
                                'body' => 'Replace this with your practice\'s story - your mission, your providers\' credentials, and what makes your patient experience different.',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Contact', 'is_home' => false,
                        'sections' => [
                            ['type' => 'contact_form', 'style' => 'default', 'content' => [
                                'heading' => 'Get in Touch',
                                'intro' => 'Have a question or want to book an appointment? Send us a message.',
                                'fields' => [
                                    ['key' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true],
                                    ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                                    ['key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => false],
                                    ['key' => 'message', 'label' => 'How can we help?', 'type' => 'textarea', 'required' => true],
                                ],
                            ]],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function restaurant(): array
    {
        return [
            'key' => 'restaurant-cafe',
            'name' => 'Restaurant / Cafe',
            'industry' => 'Restaurant',
            'description' => 'A warm, appetite-driven starter site for restaurants and cafes - menu highlights, gallery, and reservations.',
            'preview_image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600&q=80',
            'theme_name' => 'Restaurant',
            'structure' => [
                'pages' => [
                    [
                        'title' => 'Home', 'is_home' => true,
                        'sections' => [
                            ['type' => 'hero', 'style' => 'centered', 'content' => [
                                'heading' => 'Authentic Flavors, Homemade Love',
                                'subheading' => 'Fresh, made-to-order dishes served with genuine hospitality.',
                                'cta_text' => 'Reserve a Table', 'cta_link' => null, 'background_image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=900&q=80',
                            ]],
                            ['type' => 'testimonials', 'style' => 'cards', 'content' => [
                                'items' => [
                                    ['quote' => 'Best meal we\'ve had all year. We\'ll be back.', 'author' => 'Happy Guest', 'role' => ''],
                                    ['quote' => 'Warm service and even warmer food.', 'author' => 'Happy Guest', 'role' => ''],
                                ],
                            ]],
                            ['type' => 'cta', 'style' => 'default', 'content' => [
                                'heading' => 'Hungry yet?', 'button_text' => 'View Our Menu', 'button_link' => null, 'variant' => 'primary',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Menu', 'is_home' => false,
                        'sections' => [
                            ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                                'heading' => 'Menu Highlights',
                                'body' => "Replace with your signature dish\nReplace with another favorite\nReplace with a seasonal special\nReplace with a dessert\nReplace with a drink pairing",
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Gallery', 'is_home' => false,
                        'sections' => [
                            ['type' => 'gallery', 'style' => 'carousel', 'content' => [
                                'images' => [
                                    ['image_path' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=900&q=80', 'caption' => 'Add your own photos here'],
                                ],
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Contact', 'is_home' => false,
                        'sections' => [
                            ['type' => 'contact_form', 'style' => 'default', 'content' => [
                                'heading' => 'Reservations & Catering',
                                'intro' => 'Planning a group visit or event? Let us know.',
                                'fields' => [
                                    ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                                    ['key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => true],
                                    ['key' => 'message', 'label' => 'Party size / occasion', 'type' => 'textarea', 'required' => false],
                                ],
                            ]],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function education(): array
    {
        return [
            'key' => 'education-institute',
            'name' => 'Education / Tutoring',
            'industry' => 'Education',
            'description' => 'A clean, credibility-focused starter site for schools, tutoring centers, and coaching programs.',
            'preview_image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=600&q=80',
            'theme_name' => 'Corporate',
            'structure' => [
                'pages' => [
                    [
                        'title' => 'Home', 'is_home' => true,
                        'sections' => [
                            ['type' => 'hero', 'style' => 'split-image', 'content' => [
                                'heading' => 'Unlock Every Student\'s Potential',
                                'subheading' => 'Personalized programs taught by experienced, certified educators.',
                                'cta_text' => 'Enroll Now', 'cta_link' => null, 'background_image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=900&q=80',
                            ]],
                            ['type' => 'cta', 'style' => 'minimal', 'content' => [
                                'heading' => 'Free trial class for new students', 'button_text' => 'Claim Your Spot', 'button_link' => null, 'variant' => 'primary',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Programs', 'is_home' => false,
                        'sections' => [
                            ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                                'heading' => 'Our Programs',
                                'body' => "Replace with program one\nReplace with program two\nReplace with program three\nReplace with program four",
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Success Stories', 'is_home' => false,
                        'sections' => [
                            ['type' => 'testimonials', 'style' => 'cards', 'content' => [
                                'items' => [
                                    ['quote' => 'Replace with a real parent or student quote.', 'author' => 'Parent Name', 'role' => ''],
                                ],
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Contact', 'is_home' => false,
                        'sections' => [
                            ['type' => 'contact_form', 'style' => 'split', 'content' => [
                                'heading' => 'Book a Free Consultation',
                                'intro' => 'Tell us about your learning goals and we\'ll match you with the right program.',
                                'fields' => [
                                    ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                                    ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                                    ['key' => 'message', 'label' => 'What are you interested in?', 'type' => 'textarea', 'required' => true],
                                ],
                            ]],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function retail(): array
    {
        return [
            'key' => 'retail-storefront',
            'name' => 'Retail / Storefront',
            'industry' => 'Retail',
            'description' => 'A clean, product-forward starter site for retail shops and storefronts - collections, story, and visit info.',
            'preview_image' => 'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?w=600&q=80',
            'theme_name' => 'Minimal',
            'structure' => [
                'pages' => [
                    [
                        'title' => 'Home', 'is_home' => true,
                        'sections' => [
                            ['type' => 'hero', 'style' => 'centered', 'content' => [
                                'heading' => 'Quality You Can Feel',
                                'subheading' => 'Curated products, thoughtfully sourced.',
                                'cta_text' => 'Shop the Collection', 'cta_link' => null, 'background_image' => 'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?w=900&q=80',
                            ]],
                            ['type' => 'gallery', 'style' => 'grid', 'content' => [
                                'images' => [
                                    ['image_path' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=900&q=80', 'caption' => 'Add your own product photos'],
                                    ['image_path' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=900&q=80', 'caption' => 'Add your own product photos'],
                                ],
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Our Story', 'is_home' => false,
                        'sections' => [
                            ['type' => 'text_block', 'style' => 'default', 'content' => [
                                'heading' => 'Our Story',
                                'body' => 'Replace this with your brand\'s story - why you started, what you stand for, and what makes your products worth choosing.',
                            ]],
                        ],
                    ],
                    [
                        'title' => 'Contact', 'is_home' => false,
                        'sections' => [
                            ['type' => 'contact_form', 'style' => 'default', 'content' => [
                                'heading' => 'Visit or Reach Out',
                                'intro' => 'Questions about a product or order?',
                                'fields' => [
                                    ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                                    ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                                    ['key' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true],
                                ],
                            ]],
                            ['type' => 'text_block', 'style' => 'default', 'content' => [
                                'heading' => 'Store Location', 'body' => "Replace with your address\nReplace with your hours",
                            ]],
                        ],
                    ],
                ],
            ],
        ];
    }
}
