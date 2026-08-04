<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\SectionType;
use App\Models\Website;
use App\Models\WebsiteTheme;
use App\Services\WebsiteSectionService;
use App\Services\WebsiteService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WebsiteDemoSeeder extends Seeder
{
    public function run(WebsiteService $websites, WebsiteSectionService $sections): void
    {
        foreach ($this->definitions() as $definition) {
            $client = Client::query()->where('company_name', $definition['client'])->first();

            if (! $client) {
                continue;
            }

            $existing = $client->websites()->where('slug', $definition['slug'])->first();
            if ($existing) {
                continue;
            }

            $theme = WebsiteTheme::query()->create(array_merge($definition['theme'], [
                'client_id' => $client->id,
                'is_system' => false,
            ]));

            $website = Website::query()->create([
                'client_id' => $client->id,
                'theme_id' => $theme->id,
                'name' => $definition['name'],
                'slug' => $definition['slug'],
                'status' => 'draft',
                'meta_description' => $definition['meta_description'],
                'social_links' => $definition['social_links'] ?? [],
            ]);

            foreach ($definition['pages'] as $pageOrder => $pageDef) {
                $page = $website->pages()->create([
                    'title' => $pageDef['title'],
                    'slug' => $pageDef['is_home'] ? null : Str::slug($pageDef['title']),
                    'is_home' => $pageDef['is_home'],
                    'order' => $pageOrder,
                ]);

                foreach ($pageDef['sections'] as $sectionDef) {
                    $sectionType = SectionType::query()->where('key', $sectionDef['type'])->firstOrFail();

                    // Routed through the service (not a direct ->create()) so seeded
                    // content is validated against the section type's current
                    // validationRules() exactly like a real portal edit would be -
                    // stale/malformed demo content can't silently drift in.
                    $sections->create($page, $sectionType, $sectionDef['content'], $sectionDef['style']);
                }
            }

            $websites->publish($website->fresh());
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function definitions(): array
    {
        return [
            $this->sunriseFamilyClinic(),
            $this->spiceRouteBistro(),
            $this->brightMindsInstitute(),
            $this->urbanStyleRetail(),
        ];
    }

    private function sunriseFamilyClinic(): array
    {
        return [
            'client' => 'Sunrise Family Clinic',
            'name' => 'Sunrise Family Clinic',
            'slug' => 'sunrise-family-clinic',
            'meta_description' => 'Compassionate family healthcare in the heart of your community - same-day appointments, board-certified physicians, and a care team that listens.',
            'social_links' => [
                'facebook' => 'https://facebook.com/sunrisefamilyclinic',
                'instagram' => 'https://instagram.com/sunrisefamilyclinic',
                'youtube' => 'https://youtube.com/@sunrisefamilyclinic',
                'whatsapp' => 'https://wa.me/15550102938',
            ],
            'theme' => [
                'name' => 'Sunrise Clinic Theme',
                'primary_color' => '#0f9d8c',
                'secondary_color' => '#4a6572',
                'background' => '#f4fbfa',
                'heading_font' => 'Nunito Sans',
                'body_font' => 'system-ui',
                'header_style' => 'split',
                'button_style' => 'rounded',
                'border_radius' => 10,
                'container_width' => 'boxed',
            ],
            'pages' => [
                [
                    'title' => 'Home',
                    'is_home' => true,
                    'sections' => [
                        ['type' => 'hero', 'style' => 'split-image', 'content' => [
                            'heading' => 'Compassionate Care for Your Whole Family',
                            'subheading' => 'From routine checkups to urgent care, our board-certified physicians are here for every stage of life - with same-day appointments and a team that actually listens.',
                            'cta_text' => 'Book an Appointment',
                            'cta_link' => null,
                            'background_image' => 'https://images.unsplash.com/photo-1584982751601-97dcc096659c?w=900&q=80',
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'Why Families Choose Sunrise',
                            'body' => "Same-day and next-day appointments, even during flu season\nBoard-certified physicians with an average of 15 years' experience\nOn-site lab work and diagnostics - no separate trip needed\nDedicated pediatric care alongside adult and senior medicine\nDirect insurance billing with most major providers\nA patient portal for messaging, records, and refill requests",
                        ]],
                        ['type' => 'testimonials', 'style' => 'cards', 'content' => [
                            'items' => [
                                ['quote' => 'The doctors here actually listen. My son\'s asthma has been so much better managed since we switched.', 'author' => 'Priya M.', 'role' => 'Patient since 2021'],
                                ['quote' => 'Same-day appointments saved us during flu season. Highly recommend to any family in the area.', 'author' => 'Arjun K.', 'role' => 'Patient'],
                                ['quote' => 'Friendly staff, never a long wait, and they remember you by name. Five stars.', 'author' => 'Meera S.', 'role' => 'Patient since 2019'],
                                ['quote' => 'My elderly mother finally has a doctor who takes the time to explain her medications clearly.', 'author' => 'Devika R.', 'role' => 'Caregiver'],
                            ],
                        ]],
                        ['type' => 'cta', 'style' => 'default', 'content' => [
                            'heading' => 'Ready to feel better? We have same-day slots available.',
                            'button_text' => 'Schedule Your Visit',
                            'button_link' => null,
                            'variant' => 'primary',
                        ]],
                    ],
                ],
                [
                    'title' => 'Services',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'Comprehensive Care, All in One Place',
                            'body' => 'Whatever brings you in - a routine physical, a nagging cough, or ongoing management of a chronic condition - our team coordinates your care so you\'re never bounced between specialists without a plan.',
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'Our Services',
                            'body' => "General & Family Medicine\nPediatric Care (newborn through teen)\nVaccinations & Immunizations\nChronic Disease Management (diabetes, hypertension, asthma)\nOn-site Lab Work & Diagnostics\nMinor Urgent Care & Same-Day Sick Visits\nAnnual Physicals & Preventive Screenings\nWomen's Health & Wellness Exams\nSenior Care & Medication Management\nTelehealth Follow-up Visits",
                        ]],
                        ['type' => 'gallery', 'style' => 'grid', 'content' => [
                            'images' => [
                                ['image_path' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=700&q=80', 'caption' => 'Our reception area'],
                                ['image_path' => 'https://images.unsplash.com/photo-1666214280391-8ff5bd3c0bd0?w=700&q=80', 'caption' => 'A private consultation room'],
                                ['image_path' => 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=700&q=80', 'caption' => 'Our on-site lab'],
                            ],
                        ]],
                        ['type' => 'cta', 'style' => 'minimal', 'content' => [
                            'heading' => 'Not sure which service you need?',
                            'button_text' => 'Call (555) 010-2938',
                            'button_link' => null,
                            'variant' => 'primary',
                        ]],
                    ],
                ],
                [
                    'title' => 'Our Doctors',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'Meet Our Care Team',
                            'body' => 'Every physician at Sunrise is board-certified and hand-picked for one trait above all: the patience to actually listen. You\'ll see the same doctor visit after visit, not a rotating cast of strangers.',
                        ]],
                        ['type' => 'gallery', 'style' => 'grid', 'content' => [
                            'images' => [
                                ['image_path' => 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=700&q=80', 'caption' => 'Dr. Ananya Rao - Lead Physician, Family Medicine'],
                                ['image_path' => 'https://images.unsplash.com/photo-1622902046580-2b47f47f5471?w=700&q=80', 'caption' => 'Dr. Michael Chen - Pediatrics'],
                                ['image_path' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=700&q=80', 'caption' => 'Dr. Sarah Whitfield - Internal Medicine'],
                            ],
                        ]],
                        ['type' => 'testimonials', 'style' => 'single-quote', 'content' => [
                            'items' => [
                                ['quote' => 'Every patient deserves to be heard, not rushed. That\'s the standard we hold ourselves to at Sunrise - fifteen-minute-slot medicine isn\'t medicine at all.', 'author' => 'Dr. Ananya Rao', 'role' => 'Lead Physician, Family Medicine'],
                            ],
                        ]],
                    ],
                ],
                [
                    'title' => 'About',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'About Sunrise Family Clinic',
                            'body' => "For over 15 years, Sunrise Family Clinic has served our community with personalized, patient-first healthcare. What started as a single-doctor practice above the old post office has grown into a full-service family clinic - but we've never lost the small-practice feel that made patients trust us in the first place.\n\nOur physicians take the time to understand your full health history and your goals, not just the symptom in front of them today. We believe good medicine starts with genuinely knowing your patients.",
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'Our Values',
                            'body' => "Every patient gets a full 20-minute appointment, never rushed\nWe explain every diagnosis and treatment option in plain language\nWe coordinate directly with specialists so nothing falls through the cracks\nWe keep our fees transparent - no surprise billing",
                        ]],
                        ['type' => 'gallery', 'style' => 'carousel', 'content' => [
                            'images' => [
                                ['image_path' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=900&q=80', 'caption' => 'Our welcoming waiting area'],
                                ['image_path' => 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=900&q=80', 'caption' => 'Our care coordination team'],
                            ],
                        ]],
                    ],
                ],
                [
                    'title' => 'Patient Stories',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'What Our Patients Say',
                            'body' => 'Nothing means more to us than hearing directly from the families we care for.',
                        ]],
                        ['type' => 'testimonials', 'style' => 'cards', 'content' => [
                            'items' => [
                                ['quote' => 'After years of feeling like a number at our old practice, Sunrise actually knows our family by name.', 'author' => 'The Thompson Family', 'role' => 'Patients since 2020'],
                                ['quote' => 'Dr. Chen caught my daughter\'s ear infection early and saved us an ER trip. So grateful.', 'author' => 'Fatima H.', 'role' => 'Parent'],
                                ['quote' => 'Managing my diabetes finally feels manageable with a doctor who checks in between visits.', 'author' => 'Robert L.', 'role' => 'Patient since 2018'],
                                ['quote' => 'The lab results came back same-day and the doctor called me personally to explain them.', 'author' => 'Angela P.', 'role' => 'Patient'],
                                ['quote' => 'Best pediatric care in town - my kids actually look forward to their checkups.', 'author' => 'Marcus T.', 'role' => 'Parent of three'],
                            ],
                        ]],
                        ['type' => 'cta', 'style' => 'default', 'content' => [
                            'heading' => 'Join hundreds of families who trust Sunrise with their care.',
                            'button_text' => 'Become a Patient',
                            'button_link' => null,
                            'variant' => 'primary',
                        ]],
                    ],
                ],
                [
                    'title' => 'Contact',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'contact_form', 'style' => 'split', 'content' => [
                            'heading' => 'Get in Touch',
                            'intro' => 'Have a question or want to book an appointment? Send us a message and our front desk will follow up within one business day.',
                            'fields' => [
                                ['key' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true],
                                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                                ['key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => false],
                                ['key' => 'message', 'label' => 'How can we help?', 'type' => 'textarea', 'required' => true],
                            ],
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'Visit Us',
                            'body' => "123 Wellness Avenue, Suite 4, Springfield\nOpen Monday-Saturday, 8am - 7pm\nWalk-ins welcome for urgent care\nMost major insurance accepted",
                        ]],
                    ],
                ],
            ],
        ];
    }

    private function spiceRouteBistro(): array
    {
        return [
            'client' => 'Spice Route Bistro',
            'name' => 'Spice Route Bistro',
            'slug' => 'spice-route-bistro',
            'meta_description' => 'Authentic flavors, homemade love - traditional recipes passed down three generations, served with a modern twist. Dine in, takeaway, or reserve your table.',
            'social_links' => [
                'facebook' => 'https://facebook.com/spiceroutebistro',
                'instagram' => 'https://instagram.com/spiceroutebistro',
                'tiktok' => 'https://tiktok.com/@spiceroutebistro',
                'pinterest' => 'https://pinterest.com/spiceroutebistro',
                'whatsapp' => 'https://wa.me/15550183627',
            ],
            'theme' => [
                'name' => 'Spice Route Theme',
                'primary_color' => '#c1440e',
                'secondary_color' => '#8b5e34',
                'background' => '#fff8f0',
                'heading_font' => 'Poppins',
                'body_font' => 'system-ui',
                'header_style' => 'centered',
                'button_style' => 'pill',
                'border_radius' => 14,
                'container_width' => 'full',
            ],
            'pages' => [
                [
                    'title' => 'Home',
                    'is_home' => true,
                    'sections' => [
                        ['type' => 'hero', 'style' => 'centered', 'content' => [
                            'heading' => 'Authentic Flavors, Homemade Love',
                            'subheading' => 'Traditional recipes passed down three generations, served with a modern twist in a warm, welcoming dining room.',
                            'cta_text' => 'Reserve a Table',
                            'cta_link' => null,
                            'background_image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=900&q=80',
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'What Makes Us Different',
                            'body' => "Every spice blend ground fresh in-house, daily\nSourced from local farms whenever the season allows\nOur tandoor has been running since 1998 - three generations strong\nFamily recipes, never a corporate menu\nVegetarian, vegan, and gluten-free options on every page of the menu\nA dining room designed for long, unhurried meals with people you love",
                        ]],
                        ['type' => 'testimonials', 'style' => 'cards', 'content' => [
                            'items' => [
                                ['quote' => 'The butter chicken here is unmatched. We come back every single week - it\'s basically tradition now.', 'author' => 'Rohan D.', 'role' => 'Regular since 2019'],
                                ['quote' => 'Warm hospitality and even warmer naan. Every dish felt like it was made specifically for us.', 'author' => 'Kavita T.', 'role' => 'Food Blogger, @kavitaeats'],
                                ['quote' => 'Perfect spot for family dinners. Our kids ask to come back every birthday.', 'author' => 'The Sharma Family', 'role' => 'Regulars'],
                                ['quote' => 'Best biryani I\'ve had outside of Hyderabad, and I\'ve looked for it everywhere.', 'author' => 'James O.', 'role' => 'Food Critic'],
                            ],
                        ]],
                        ['type' => 'gallery', 'style' => 'carousel', 'content' => [
                            'images' => [
                                ['image_path' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=900&q=80', 'caption' => 'Butter chicken with fresh garlic naan'],
                                ['image_path' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=900&q=80', 'caption' => 'Our warm, welcoming dining room'],
                                ['image_path' => 'https://images.unsplash.com/photo-1567188040759-fb8a883dc6d8?w=900&q=80', 'caption' => 'Fresh from the tandoor, every night'],
                            ],
                        ]],
                        ['type' => 'cta', 'style' => 'default', 'content' => [
                            'heading' => 'Hungry yet? See what\'s cooking tonight.',
                            'button_text' => 'View Our Menu',
                            'button_link' => null,
                            'variant' => 'primary',
                        ]],
                    ],
                ],
                [
                    'title' => 'Menu',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'Chef\'s Favorites',
                            'body' => 'A taste of what\'s always on the table - our full menu changes seasonally, but these dishes never leave.',
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'Mains',
                            'body' => "Butter Chicken ($18) - slow-simmered in a rich tomato-cashew gravy\nHyderabadi Biryani ($20) - fragrant basmati rice, tender lamb, saffron\nPaneer Tikka Masala ($16) - char-grilled cottage cheese, house spices\nGoan Fish Curry ($22) - coconut-based curry, catch of the day\nDal Makhani ($14) - slow-cooked black lentils, finished with cream\nTandoori Mixed Grill ($26) - chicken, lamb, and prawns from the tandoor",
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'Starters & Sides',
                            'body' => "Samosa Chaat ($8) - crispy samosas, tangy chutneys, yogurt\nGarlic Naan ($4) - baked fresh in our tandoor\nMango Lassi ($5) - house-blended, not from a mix\nMasala Chai ($4) - the perfect way to finish\nOnion Bhaji ($7) - crispy onion fritters, mint chutney\nRaita ($3) - cooling cucumber yogurt",
                        ]],
                        ['type' => 'cta', 'style' => 'minimal', 'content' => [
                            'heading' => 'Have dietary restrictions? We\'ll take care of you.',
                            'button_text' => 'Ask Our Team',
                            'button_link' => null,
                            'variant' => 'primary',
                        ]],
                    ],
                ],
                [
                    'title' => 'Our Story',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'Three Generations, One Kitchen',
                            'body' => "Spice Route Bistro started in 1998 as a six-table restaurant run by the Patel family, serving the recipes passed down from grandmother Kamla's own kitchen. Today, her grandson runs the pass, but the spice blends are still ground the same way, in the same stone mortar, by hand.\n\nWe've grown - more seats, a bigger bar, a private dining room for celebrations - but the food is still cooked to feed family, because to us, that's exactly what our guests are.",
                        ]],
                        ['type' => 'gallery', 'style' => 'grid', 'content' => [
                            'images' => [
                                ['image_path' => 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?w=700&q=80', 'caption' => 'Chef Arjun Patel - Head Chef, third generation'],
                                ['image_path' => 'https://images.unsplash.com/photo-1567188040759-fb8a883dc6d8?w=700&q=80', 'caption' => 'Our tandoor, running since 1998'],
                            ],
                        ]],
                        ['type' => 'testimonials', 'style' => 'single-quote', 'content' => [
                            'items' => [
                                ['quote' => 'My grandmother taught me that food made in a hurry tastes like it was made in a hurry. We still don\'t rush anything here.', 'author' => 'Chef Arjun Patel', 'role' => 'Head Chef & Owner'],
                            ],
                        ]],
                    ],
                ],
                [
                    'title' => 'Gallery',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'gallery', 'style' => 'carousel', 'content' => [
                            'images' => [
                                ['image_path' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=900&q=80', 'caption' => 'Butter chicken with garlic naan'],
                                ['image_path' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=900&q=80', 'caption' => 'Our dining room, ready for a Friday night'],
                                ['image_path' => 'https://images.unsplash.com/photo-1567188040759-fb8a883dc6d8?w=900&q=80', 'caption' => 'Fresh from the tandoor'],
                                ['image_path' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=900&q=80', 'caption' => 'Our private dining room, set for a celebration'],
                            ],
                        ]],
                    ],
                ],
                [
                    'title' => 'Reservations',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'contact_form', 'style' => 'default', 'content' => [
                            'heading' => 'Book Your Table',
                            'intro' => 'Reserve online and we\'ll confirm within the hour. For parties of 8 or more, please call us directly.',
                            'fields' => [
                                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                                ['key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => true],
                                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => false],
                                ['key' => 'message', 'label' => 'Preferred date, time & party size', 'type' => 'textarea', 'required' => true],
                            ],
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'Good to Know',
                            'body' => "Reservations held for 15 minutes past booking time\nLarge parties (8+) please call ahead\nPrivate dining room available for celebrations\nWe happily accommodate dietary restrictions with notice",
                        ]],
                    ],
                ],
                [
                    'title' => 'Contact',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'contact_form', 'style' => 'split', 'content' => [
                            'heading' => 'Questions? Catering Inquiries?',
                            'intro' => 'Planning a group visit, event, or catering order? Let us know and our events team will reach out.',
                            'fields' => [
                                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                                ['key' => 'message', 'label' => 'Tell us about your event', 'type' => 'textarea', 'required' => false],
                            ],
                        ]],
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'Find Us',
                            'body' => "45 Market Street, Downtown Springfield\nOpen daily, 11am - 11pm\nDine-in, takeaway & delivery available\nFree parking in the lot behind the building",
                        ]],
                    ],
                ],
            ],
        ];
    }

    private function brightMindsInstitute(): array
    {
        return [
            'client' => 'Bright Minds Institute',
            'name' => 'Bright Minds Institute',
            'slug' => 'bright-minds-institute',
            'meta_description' => 'Unlock your child\'s potential with personalized learning programs - math, reading, test prep, and coding, taught by certified educators in small groups.',
            'social_links' => [
                'facebook' => 'https://facebook.com/brightmindsinstitute',
                'instagram' => 'https://instagram.com/brightmindsinstitute',
                'linkedin' => 'https://linkedin.com/company/brightmindsinstitute',
                'youtube' => 'https://youtube.com/@brightmindsinstitute',
            ],
            'theme' => [
                'name' => 'Bright Minds Theme',
                'primary_color' => '#4338ca',
                'secondary_color' => '#64748b',
                'background' => '#ffffff',
                'heading_font' => 'Inter',
                'body_font' => 'system-ui',
                'header_style' => 'split',
                'button_style' => 'square',
                'border_radius' => 4,
                'container_width' => 'boxed',
            ],
            'pages' => [
                [
                    'title' => 'Home',
                    'is_home' => true,
                    'sections' => [
                        ['type' => 'hero', 'style' => 'split-image', 'content' => [
                            'heading' => "Unlock Your Child's Potential",
                            'subheading' => 'Personalized tutoring and enrichment programs for ages 6-18, taught by certified educators in small groups of four or fewer.',
                            'cta_text' => 'Enroll Now',
                            'cta_link' => null,
                            'background_image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=900&q=80',
                        ]],
                        ['type' => 'cta', 'style' => 'minimal', 'content' => [
                            'heading' => 'Free trial class for new students - no obligation.',
                            'button_text' => 'Claim Your Spot',
                            'button_link' => null,
                            'variant' => 'primary',
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'Why Parents Choose Bright Minds',
                            'body' => "Groups capped at four students - never a lecture hall\nEvery instructor is a certified teacher, not a part-time tutor\nProgress reports after every session, not just at term's end\nFlexible scheduling around school, sports, and family life\nA diagnostic assessment before day one, so we teach to the actual gap\nProven results: our average student gains 1.5 grade levels in a semester",
                        ]],
                        ['type' => 'testimonials', 'style' => 'cards', 'content' => [
                            'items' => [
                                ['quote' => 'My daughter went from struggling with math to loving it. Her confidence completely changed, not just her grades.', 'author' => 'Neha P.', 'role' => 'Parent of a 4th grader'],
                                ['quote' => 'The SAT prep program raised my score by 200 points. I got into my first-choice school because of it.', 'author' => 'Aditya V.', 'role' => 'Student, Grade 11'],
                                ['quote' => 'Flexible scheduling and genuinely caring staff. They work around our chaotic soccer schedule every single week.', 'author' => 'Fatima H.', 'role' => 'Parent of two'],
                            ],
                        ]],
                    ],
                ],
                [
                    'title' => 'Programs',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'Programs for Every Age and Goal',
                            'body' => 'Every program starts with a free diagnostic assessment, so your child\'s learning plan is built around exactly where they are - not a one-size-fits-all curriculum.',
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'Our Programs',
                            'body' => "Math Mastery (Grades 1-12) - from basic arithmetic to AP Calculus\nReading & Writing Lab - phonics through essay composition\nScience Olympiad Prep - competition-level coaching\nSAT / ACT Test Prep - proven score-improvement curriculum\nCoding for Kids (ages 8-16) - Python, Scratch, and web basics\nHomework Help & Study Skills - daily drop-in support",
                        ]],
                        ['type' => 'gallery', 'style' => 'grid', 'content' => [
                            'images' => [
                                ['image_path' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=700&q=80', 'caption' => 'Small group tutoring, four students max'],
                                ['image_path' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=700&q=80', 'caption' => 'Hands-on science lab sessions'],
                                ['image_path' => 'https://images.unsplash.com/photo-1588072432836-e10032774350?w=700&q=80', 'caption' => 'Our weekly coding club'],
                            ],
                        ]],
                    ],
                ],
                [
                    'title' => 'Our Faculty',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'Certified Teachers, Not Part-Time Tutors',
                            'body' => 'Every instructor at Bright Minds holds a teaching credential and an average of 8 years of classroom experience. We hire fewer than 1 in 10 applicants.',
                        ]],
                        ['type' => 'gallery', 'style' => 'grid', 'content' => [
                            'images' => [
                                ['image_path' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=700&q=80', 'caption' => 'Mr. David Okafor - Math Program Lead, M.Ed.'],
                                ['image_path' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=700&q=80', 'caption' => 'Ms. Laura Bennett - Reading & Writing Lead'],
                                ['image_path' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=700&q=80', 'caption' => 'Mr. Kevin Zhao - Test Prep & Coding Lead'],
                            ],
                        ]],
                    ],
                ],
                [
                    'title' => 'Success Stories',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'testimonials', 'style' => 'cards', 'content' => [
                            'items' => [
                                ['quote' => 'My daughter went from struggling with math to loving it. Incredible tutors who never made her feel behind.', 'author' => 'Neha P.', 'role' => 'Parent'],
                                ['quote' => 'The SAT prep program raised my score by 200 points - more than I thought possible in one semester.', 'author' => 'Aditya V.', 'role' => 'Student, Grade 11'],
                                ['quote' => 'Flexible scheduling and genuinely caring staff. Highly recommend to any busy family.', 'author' => 'Fatima H.', 'role' => 'Parent'],
                                ['quote' => 'My son went from a C average to the honor roll in one semester. The progress reports kept us in the loop the whole time.', 'author' => 'Marcus J.', 'role' => 'Parent of a 7th grader'],
                                ['quote' => 'The coding class turned my daughter\'s screen time into something she\'s actually proud of building.', 'author' => 'Sunita R.', 'role' => 'Parent'],
                            ],
                        ]],
                        ['type' => 'cta', 'style' => 'default', 'content' => [
                            'heading' => 'Your child could be next. Book a free assessment today.',
                            'button_text' => 'Get Started',
                            'button_link' => null,
                            'variant' => 'primary',
                        ]],
                    ],
                ],
                [
                    'title' => 'Admissions',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'How to Enroll',
                            'body' => "Step 1: Book a free diagnostic assessment (30 minutes)\nStep 2: Receive a personalized learning plan within 48 hours\nStep 3: Attend a free trial class, no commitment required\nStep 4: Choose a schedule that fits your family\nStep 5: Get monthly progress reports and stay in the loop\nStep 6: Adjust the plan any time as your child grows",
                        ]],
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'Tuition & Fees',
                            'body' => 'Programs start at $220/month for one weekly session, with multi-session and sibling discounts available. Every plan includes a free diagnostic assessment and monthly progress reports - no hidden fees, no long-term contract required.',
                        ]],
                    ],
                ],
                [
                    'title' => 'Contact',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'contact_form', 'style' => 'split', 'content' => [
                            'heading' => 'Book a Free Consultation',
                            'intro' => "Tell us about your child's learning goals and we'll match them with the right program within one business day.",
                            'fields' => [
                                ['key' => 'name', 'label' => 'Parent / Guardian Name', 'type' => 'text', 'required' => true],
                                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                                ['key' => 'phone', 'label' => 'Phone', 'type' => 'tel', 'required' => false],
                                ['key' => 'message', 'label' => "Child's grade & subject of interest", 'type' => 'textarea', 'required' => true],
                            ],
                        ]],
                    ],
                ],
            ],
        ];
    }

    private function urbanStyleRetail(): array
    {
        return [
            'client' => 'UrbanStyle Retail Co.',
            'name' => 'UrbanStyle Retail Co.',
            'slug' => 'urbanstyle-retail',
            'meta_description' => 'Style that speaks - curated, ethically-sourced fashion for the modern individual. Discover our latest collection, in-store or online.',
            'social_links' => [
                'instagram' => 'https://instagram.com/urbanstyleretail',
                'tiktok' => 'https://tiktok.com/@urbanstyleretail',
                'pinterest' => 'https://pinterest.com/urbanstyleretail',
                'twitter' => 'https://x.com/urbanstyleretail',
            ],
            'theme' => [
                'name' => 'UrbanStyle Theme',
                'primary_color' => '#d4af37',
                'secondary_color' => '#8a8a8a',
                'background' => '#141414',
                'heading_font' => 'Playfair Display',
                'body_font' => 'system-ui',
                'header_style' => 'centered',
                'button_style' => 'pill',
                'border_radius' => 0,
                'container_width' => 'full',
            ],
            'pages' => [
                [
                    'title' => 'Home',
                    'is_home' => true,
                    'sections' => [
                        ['type' => 'hero', 'style' => 'centered', 'content' => [
                            'heading' => 'Style That Speaks',
                            'subheading' => 'Curated fashion for the modern individual, sourced from ethical manufacturers and designed to outlast the trend cycle. New drops every season.',
                            'cta_text' => 'Shop the Collection',
                            'cta_link' => null,
                            'background_image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=900&q=80',
                        ]],
                        ['type' => 'gallery', 'style' => 'carousel', 'content' => [
                            'images' => [
                                ['image_path' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=900&q=80', 'caption' => 'Autumn Collection - now available'],
                                ['image_path' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=900&q=80', 'caption' => 'Street Essentials'],
                                ['image_path' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=900&q=80', 'caption' => 'Evening Wear'],
                            ],
                        ]],
                        ['type' => 'testimonials', 'style' => 'cards', 'content' => [
                            'items' => [
                                ['quote' => 'The quality is unreal for the price point. I\'ve had my UrbanStyle jacket for three years and it still looks new.', 'author' => 'Jordan K.', 'role' => 'Verified Customer'],
                                ['quote' => 'Finally a brand that takes sustainability seriously without sacrificing on style.', 'author' => 'Amara B.', 'role' => 'Verified Customer'],
                                ['quote' => 'Customer service helped me exchange a size within a day. Will absolutely shop here again.', 'author' => 'Leo M.', 'role' => 'Verified Customer'],
                            ],
                        ]],
                        ['type' => 'cta', 'style' => 'default', 'content' => [
                            'heading' => 'Members get 15% off their first order.',
                            'button_text' => 'Join UrbanStyle',
                            'button_link' => null,
                            'variant' => 'primary',
                        ]],
                    ],
                ],
                [
                    'title' => 'Collections',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'This Season\'s Edit',
                            'body' => 'Every piece in our current collection is designed around one idea: clothes that move with your life, not against it. Ethically sourced, thoughtfully cut, and built for repeat wear.',
                        ]],
                        ['type' => 'gallery', 'style' => 'grid', 'content' => [
                            'images' => [
                                ['image_path' => 'https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?w=700&q=80', 'caption' => 'New arrivals'],
                                ['image_path' => 'https://images.unsplash.com/photo-1490114538077-0a7f8cb49891?w=700&q=80', 'caption' => 'Accessories'],
                                ['image_path' => 'https://images.unsplash.com/photo-1516762689617-e1cffcef479d?w=700&q=80', 'caption' => 'Footwear'],
                                ['image_path' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=700&q=80', 'caption' => 'Outerwear'],
                            ],
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'Shop by Category',
                            'body' => "Outerwear & Jackets\nKnitwear & Layers\nDenim & Trousers\nFootwear\nAccessories & Bags\nLimited Drops",
                        ]],
                    ],
                ],
                [
                    'title' => 'Our Story',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'Our Story',
                            'body' => "UrbanStyle was founded on one idea: fashion should feel as good as it looks. Every piece is sourced from ethical manufacturers, cut from responsibly-produced fabric, and designed to last beyond a single trend cycle.\n\nWe started as a single market stall in 2016. Today we work with independent manufacturers across three countries, all audited for fair labor practices - because looking good should never come at someone else's expense.",
                        ]],
                        ['type' => 'testimonials', 'style' => 'single-quote', 'content' => [
                            'items' => [
                                ['quote' => 'We don\'t chase trends. We build pieces people actually keep wearing five years later - that\'s the only metric that matters to us.', 'author' => 'Priya Anand', 'role' => 'Founder & Creative Director'],
                            ],
                        ]],
                    ],
                ],
                [
                    'title' => 'Lookbook',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'text_block', 'style' => 'default', 'content' => [
                            'heading' => 'Autumn/Winter Lookbook',
                            'body' => 'Styled by our team, shot on real customers - not models. This is what the collection looks like in real life.',
                        ]],
                        ['type' => 'gallery', 'style' => 'carousel', 'content' => [
                            'images' => [
                                ['image_path' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=900&q=80', 'caption' => 'Evening layers, styled with the Wool Overcoat'],
                                ['image_path' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=900&q=80', 'caption' => 'Street essentials, everyday rotation'],
                                ['image_path' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=900&q=80', 'caption' => 'Weekend casual'],
                            ],
                        ]],
                    ],
                ],
                [
                    'title' => 'Contact',
                    'is_home' => false,
                    'sections' => [
                        ['type' => 'contact_form', 'style' => 'default', 'content' => [
                            'heading' => 'Visit Our Flagship Store',
                            'intro' => 'Questions about an order, a return, or want to book a styling session?',
                            'fields' => [
                                ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                                ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                                ['key' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true],
                            ],
                        ]],
                        ['type' => 'text_block', 'style' => 'two-column', 'content' => [
                            'heading' => 'Store Location',
                            'body' => "88 Fashion District Blvd, Downtown\nOpen daily, 10am - 9pm\nFree styling consultations, walk-ins welcome\nCurbside pickup available",
                        ]],
                    ],
                ],
            ],
        ];
    }
}
