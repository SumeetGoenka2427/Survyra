<?php

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\SectionType;
use App\Models\SubscriptionPlan;
use App\Models\WebsiteLead;
use App\Models\WebsiteTemplate;
use App\Models\WebsiteTheme;
use Database\Seeders\SectionTypeSeeder;
use Database\Seeders\WebsiteTemplateSeeder;
use Database\Seeders\WebsiteThemeSeeder;

beforeEach(function () {
    $this->seed(SectionTypeSeeder::class);
    $this->seed(WebsiteThemeSeeder::class);
});

function ownerFor(Client $client): ClientUser
{
    return ClientUser::factory()->create(['client_id' => $client->id, 'role' => 'owner']);
}

test('creating a website seeds a home page with a hero section', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);

    $response = $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);

    $response->assertRedirect('/portal/website');
    $website = $client->fresh()->websites()->first();

    expect($website)->not->toBeNull();
    expect($website->slug)->toBe('my-business');
    expect($website->status)->toBe('draft');
    expect($website->pages)->toHaveCount(1);
    expect($website->pages->first()->is_home)->toBeTrue();
    expect($website->pages->first()->sections)->toHaveCount(1);
    expect($website->pages->first()->sections->first()->sectionType->key)->toBe('hero');
});

test('a second website with the same name gets a unique slug', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);

    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);

    // simulate hitting the plan limit path directly via the service to create a second site
    $service = app(\App\Services\WebsiteService::class);
    $second = $service->create($client->fresh(), ['name' => 'My Business'], $owner->id);

    expect($second->slug)->toBe('my-business-2');
});

test('a client cannot create more websites than their plan allows', function () {
    $plan = SubscriptionPlan::query()->create([
        'name' => 'Starter',
        'slug' => 'starter-'.uniqid(),
        'price' => 0,
        'billing_cycle' => 'monthly',
        'max_websites' => 1,
        'is_active' => true,
    ]);
    $client = Client::factory()->create(['subscription_plan_id' => $plan->id]);
    $owner = ownerFor($client);

    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'Site One'])->assertRedirect();
    expect($client->fresh()->websites()->count())->toBe(1);

    $response = $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'Site Two']);

    $response->assertForbidden();
    expect($client->fresh()->websites()->count())->toBe(1);
});

test('a viewer cannot create or edit a website', function () {
    $client = Client::factory()->create();
    $viewer = ClientUser::factory()->create(['client_id' => $client->id, 'role' => 'viewer']);

    $this->actingAs($viewer, 'client')->post('/portal/website', ['name' => 'Blocked Site'])->assertForbidden();
});

test('pages and sections can be added and reordered', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $website = $client->fresh()->websites()->first();

    $this->actingAs($owner, 'client')->post('/portal/website/pages', ['title' => 'About'])->assertRedirect();
    $website->refresh();
    expect($website->pages()->count())->toBe(2);

    $aboutPage = $website->pages()->where('title', 'About')->first();
    expect($aboutPage->is_home)->toBeFalse();
    expect($aboutPage->slug)->toBe('about');

    // reorder pages: put About first
    $homePage = $website->pages()->where('is_home', true)->first();
    $this->actingAs($owner, 'client')->post('/portal/website/pages/reorder', [
        'items' => [
            ['id' => $aboutPage->id, 'order' => 0],
            ['id' => $homePage->id, 'order' => 1],
        ],
    ])->assertOk();

    expect($aboutPage->fresh()->order)->toBe(0);
    expect($homePage->fresh()->order)->toBe(1);

    // add a text_block section to the About page
    $textType = SectionType::query()->where('key', 'text_block')->firstOrFail();
    $this->actingAs($owner, 'client')->post("/portal/website/pages/{$aboutPage->id}/sections", [
        'section_type_id' => $textType->id,
    ])->assertRedirect();

    expect($aboutPage->fresh()->sections)->toHaveCount(1);
});

test('updating a gallery section builds the images array from repeater rows', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $website = $client->fresh()->websites()->first();
    $homePage = $website->pages()->first();

    $galleryType = SectionType::query()->where('key', 'gallery')->firstOrFail();
    $this->actingAs($owner, 'client')->post("/portal/website/pages/{$homePage->id}/sections", [
        'section_type_id' => $galleryType->id,
    ]);
    $section = $homePage->fresh()->sections()->where('section_type_id', $galleryType->id)->first();

    $this->actingAs($owner, 'client')->put("/portal/website/sections/{$section->id}", [
        'style' => 'grid',
        'images_json' => json_encode([
            ['image_path' => 'https://example.com/one.jpg', 'caption' => 'One'],
            ['image_path' => 'https://example.com/two.jpg', 'caption' => 'Two'],
        ]),
    ])->assertRedirect();

    $section->refresh();
    expect($section->content['images'])->toHaveCount(2);
    expect($section->content['images'][0]['image_path'])->toBe('https://example.com/one.jpg');
    expect($section->content['images'][1]['caption'])->toBe('Two');
});

test('a hero cta link to an internal page resolves to a real url at publish time', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $website = $client->fresh()->websites()->first();
    $homePage = $website->pages()->first();
    $this->actingAs($owner, 'client')->post('/portal/website/pages', ['title' => 'Contact']);
    $contactPage = $website->fresh()->pages()->where('title', 'Contact')->first();

    $heroSection = $homePage->sections()->first();
    $this->actingAs($owner, 'client')->put("/portal/website/sections/{$heroSection->id}", [
        'style' => 'centered',
        'heading' => 'Welcome',
        'cta_text' => 'Contact Us',
        'cta_link_type' => 'page',
        'cta_link_page_id' => $contactPage->id,
    ])->assertRedirect();

    expect($heroSection->fresh()->content['cta_link'])->toBe(['type' => 'page', 'page_id' => $contactPage->id]);

    $this->actingAs($owner, 'client')->post('/portal/website/publish');

    $response = $this->get("/site/{$website->slug}");
    $response->assertOk();
    $response->assertSee(route('website.show.page', [$website->slug, 'contact']), false);
});

test('a viewer cannot see another clients website via route model binding', function () {
    $clientA = Client::factory()->create();
    $ownerA = ownerFor($clientA);
    $this->actingAs($ownerA, 'client')->post('/portal/website', ['name' => 'Client A Site']);
    $websiteA = $clientA->fresh()->websites()->first();
    $pageA = $websiteA->pages()->first();

    $clientB = Client::factory()->create();
    $ownerB = ownerFor($clientB);

    // Client B tries to mutate Client A's page directly by ID
    $response = $this->actingAs($ownerB, 'client')->put("/portal/website/pages/{$pageA->id}", [
        'title' => 'Hijacked',
    ]);

    $response->assertForbidden();
    expect($pageA->fresh()->title)->not->toBe('Hijacked');
});

test('publishing snapshots the draft and editing afterward does not affect the live public page until republished', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $website = $client->fresh()->websites()->first();

    // Not published yet - public page 404s
    $this->get("/site/{$website->slug}")->assertNotFound();

    $this->actingAs($owner, 'client')->post('/portal/website/publish')->assertRedirect();
    $website->refresh();
    expect($website->status)->toBe('published');
    expect($website->published_snapshot)->not->toBeNull();

    $this->get("/site/{$website->slug}")->assertOk()->assertSee('Welcome to Your Business');

    // Edit the draft content without republishing
    $heroSection = $website->pages()->first()->sections()->first();
    $this->actingAs($owner, 'client')->put("/portal/website/sections/{$heroSection->id}", [
        'style' => 'centered',
        'heading' => 'DRAFT ONLY HEADING',
    ])->assertRedirect();

    // Public page still shows the old published content
    $this->get("/site/{$website->slug}")->assertOk()->assertSee('Welcome to Your Business')->assertDontSee('DRAFT ONLY HEADING');

    // Republish makes the new content live
    $this->actingAs($owner, 'client')->post('/portal/website/publish')->assertRedirect();
    $this->get("/site/{$website->slug}")->assertOk()->assertSee('DRAFT ONLY HEADING');
});

test('unpublishing makes the public page unavailable again', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $website = $client->fresh()->websites()->first();

    $this->actingAs($owner, 'client')->post('/portal/website/publish');
    $this->get("/site/{$website->slug}")->assertOk();

    $this->actingAs($owner, 'client')->post('/portal/website/unpublish')->assertRedirect();
    $this->get("/site/{$website->slug}")->assertNotFound();
});

test('submitting the public contact form creates a website lead scoped to the client', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $website = $client->fresh()->websites()->first();
    $homePage = $website->pages()->first();

    $contactType = SectionType::query()->where('key', 'contact_form')->firstOrFail();
    $this->actingAs($owner, 'client')->post("/portal/website/pages/{$homePage->id}/sections", [
        'section_type_id' => $contactType->id,
    ]);
    $section = $homePage->fresh()->sections()->where('section_type_id', $contactType->id)->first();

    $this->actingAs($owner, 'client')->post('/portal/website/publish');

    $response = $this->post("/site/{$website->slug}/contact", [
        'section_id' => $section->id,
        'page_id' => $homePage->id,
        'name' => 'Jane Visitor',
        'email' => 'jane@example.com',
        'message' => 'Interested!',
    ]);

    $response->assertRedirect();
    expect(WebsiteLead::where('client_id', $client->id)->count())->toBe(1);
    $lead = WebsiteLead::where('client_id', $client->id)->first();
    expect($lead->data['name'])->toBe('Jane Visitor');
    expect($lead->data['email'])->toBe('jane@example.com');
});

test('a filled honeypot field silently rejects the contact form submission', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $website = $client->fresh()->websites()->first();
    $homePage = $website->pages()->first();

    $contactType = SectionType::query()->where('key', 'contact_form')->firstOrFail();
    $this->actingAs($owner, 'client')->post("/portal/website/pages/{$homePage->id}/sections", [
        'section_type_id' => $contactType->id,
    ]);
    $section = $homePage->fresh()->sections()->where('section_type_id', $contactType->id)->first();
    $this->actingAs($owner, 'client')->post('/portal/website/publish');

    $response = $this->post("/site/{$website->slug}/contact", [
        'section_id' => $section->id,
        'page_id' => $homePage->id,
        'name' => 'Spam Bot',
        'email' => 'spam@example.com',
        'message' => 'buy now',
        'company_website' => 'http://spam.example.com',
    ]);

    $response->assertSessionHasErrors('company_website');
    expect(WebsiteLead::where('client_id', $client->id)->count())->toBe(0);
});

test('the leads inbox only shows the authenticated clients own leads', function () {
    $clientA = Client::factory()->create();
    $ownerA = ownerFor($clientA);
    $clientB = Client::factory()->create();
    $ownerB = ownerFor($clientB);
    $this->actingAs($ownerB, 'client')->post('/portal/website', ['name' => 'Client B Site']);
    $websiteB = $clientB->fresh()->websites()->first();

    WebsiteLead::query()->create([
        'client_id' => $clientB->id,
        'website_id' => $websiteB->id,
        'data' => ['name' => 'Other Clients Lead'],
        'status' => 'new',
    ]);

    $response = $this->actingAs($ownerA, 'client')->get('/portal/website/leads');

    $response->assertOk();
    $response->assertDontSee('Other Clients Lead');
});

test('a published page renders canonical, open graph, and structured data tags', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $website = $client->fresh()->websites()->first();

    // og:image falls back to the first hero's background_image when no
    // explicit og_image is set - give the auto-seeded hero one to exercise it.
    $heroSection = $website->pages()->first()->sections()->first();
    $this->actingAs($owner, 'client')->put("/portal/website/sections/{$heroSection->id}", [
        'style' => 'centered',
        'heading' => 'Welcome',
        'background_image' => 'https://example.com/hero.jpg',
    ]);

    $this->actingAs($owner, 'client')->post('/portal/website/publish');

    $response = $this->get("/site/{$website->slug}");

    $response->assertOk();
    $response->assertSee('rel="canonical"', false);
    $response->assertSee('og:title', false);
    $response->assertSee('og:image', false);
    $response->assertSee('twitter:card', false);
    $response->assertSee('application/ld+json', false);
    $response->assertSee('"@type":"Organization"', false);
});

test('exactly one h1 renders regardless of how many hero sections a page has', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $website = $client->fresh()->websites()->first();
    $homePage = $website->pages()->first();

    // 1 hero (the auto-seeded one) -> exactly one h1
    $this->actingAs($owner, 'client')->post('/portal/website/publish');
    $html = $this->get("/site/{$website->slug}")->getContent();
    expect(substr_count($html, '<h1'))->toBe(1);

    // add a second hero -> still exactly one h1 (second renders as h2)
    $heroType = SectionType::query()->where('key', 'hero')->firstOrFail();
    $this->actingAs($owner, 'client')->post("/portal/website/pages/{$homePage->id}/sections", [
        'section_type_id' => $heroType->id,
    ]);
    $this->actingAs($owner, 'client')->post('/portal/website/publish');
    $html = $this->get("/site/{$website->slug}")->getContent();
    expect(substr_count($html, '<h1'))->toBe(1);

    // a page with no hero at all still gets a real (hidden) h1
    $this->actingAs($owner, 'client')->post('/portal/website/pages', ['title' => 'About']);
    $aboutPage = $website->fresh()->pages()->where('title', 'About')->first();
    $textType = SectionType::query()->where('key', 'text_block')->firstOrFail();
    $this->actingAs($owner, 'client')->post("/portal/website/pages/{$aboutPage->id}/sections", [
        'section_type_id' => $textType->id,
    ]);
    $this->actingAs($owner, 'client')->post('/portal/website/publish');
    $html = $this->get("/site/{$website->slug}/about")->getContent();
    expect(substr_count($html, '<h1'))->toBe(1);
});

test('eight premium system themes are seeded and selectable', function () {
    $themes = WebsiteTheme::where('is_system', true)->pluck('name');

    expect($themes)->toHaveCount(8);
    foreach (['Healthcare', 'Corporate', 'Minimal', 'Modern', 'Restaurant', 'Luxury', 'Dark', 'Gradient'] as $name) {
        expect($themes)->toContain($name);
    }

    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $darkThemeId = WebsiteTheme::where('name', 'Dark')->where('is_system', true)->value('id');

    $this->actingAs($owner, 'client')->patch('/portal/website/theme', ['theme_id' => $darkThemeId])
        ->assertRedirect();

    expect($client->fresh()->websites()->first()->theme_id)->toBe($darkThemeId);
});

test('creating a website from an industry template materializes validated pages and sections', function () {
    $this->seed(WebsiteTemplateSeeder::class);

    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $template = WebsiteTemplate::where('key', 'restaurant-cafe')->firstOrFail();

    $this->actingAs($owner, 'client')->post('/portal/website', [
        'name' => 'My Bistro',
        'template_id' => $template->id,
    ])->assertRedirect();

    $website = $client->fresh()->websites()->first();

    expect($website->theme_id)->toBe($template->theme_id);
    expect($website->pages()->count())->toBe(count($template->structure['pages']));

    $homePage = $website->pages()->where('is_home', true)->first();
    expect($homePage->sections()->count())->toBeGreaterThan(0);
    // content came through WebsiteSectionService, so it's a real, validated
    // section - the type resolves and renders without error.
    $heroSection = $homePage->sections()->first();
    expect($heroSection->sectionType->contract()->renderComponent($heroSection->settings['style'] ?? 'default'))
        ->toContain('website-sections');

    expect($template->fresh()->usage_count)->toBe(1);
});

test('the in-builder preview reflects the live draft, works before publishing, and never creates a real lead', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $website = $client->fresh()->websites()->first();

    // Never published - public page 404s, but the preview still works
    expect($website->status)->toBe('draft');
    $this->get("/site/{$website->slug}")->assertNotFound();

    $response = $this->actingAs($owner, 'client')->get('/portal/website/preview');
    $response->assertOk();
    $response->assertSee('noindex', false);
    $response->assertSee('Welcome to Your Business');

    // Nav links inside preview point at preview routes, never public ones
    $response->assertDontSee('/site/'.$website->slug, false);

    // The contact form section (if rendered inside preview) never targets a
    // real endpoint and never creates a WebsiteLead even if "submitted".
    $homePage = $website->pages()->first();
    $contactType = SectionType::query()->where('key', 'contact_form')->firstOrFail();
    $this->actingAs($owner, 'client')->post("/portal/website/pages/{$homePage->id}/sections", [
        'section_type_id' => $contactType->id,
    ]);

    $previewResponse = $this->actingAs($owner, 'client')->get('/portal/website/preview');
    $previewResponse->assertSee('Preview only');
    $previewResponse->assertDontSee('action="'.route('website.contact.store', $website->slug), false);

    expect(WebsiteLead::count())->toBe(0);
});

test('social links are only shown in the footer for filled-in platforms', function () {
    $client = Client::factory()->create();
    $owner = ownerFor($client);
    $this->actingAs($owner, 'client')->post('/portal/website', ['name' => 'My Business']);
    $website = $client->fresh()->websites()->first();

    $this->actingAs($owner, 'client')->patch('/portal/website', [
        'name' => 'My Business',
        'social_links' => [
            'facebook' => 'https://facebook.com/mybusiness',
            'instagram' => 'https://instagram.com/mybusiness',
            'twitter' => '',
        ],
    ])->assertRedirect();

    expect($website->fresh()->social_links)->toBe([
        'facebook' => 'https://facebook.com/mybusiness',
        'instagram' => 'https://instagram.com/mybusiness',
    ]);

    $this->actingAs($owner, 'client')->post('/portal/website/publish');

    $response = $this->get("/site/{$website->slug}");
    $response->assertOk();
    $response->assertSee('https://facebook.com/mybusiness', false);
    $response->assertSee('https://instagram.com/mybusiness', false);
    $response->assertSee('bi-facebook', false);
    $response->assertSee('bi-instagram', false);
    $response->assertDontSee('bi-twitter-x', false);
    $response->assertDontSee('bi-linkedin', false);
});
