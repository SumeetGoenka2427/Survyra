<?php

use App\Models\Client;
use App\Models\ClientUser;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('survyra_admin', 'web');
    Permission::findOrCreate('send-campaigns', 'web');
});

function makeCampaignAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('survyra_admin');
    $user->givePermissionTo('send-campaigns');

    return $user;
}

test('a survyra admin can create update and delete a contact with tags', function () {
    $admin = makeCampaignAdmin();
    $client = Client::factory()->create();

    $this->actingAs($admin)->post("/admin/clients/{$client->id}/contacts", [
        'name' => 'Jane Doe',
        'phone' => '+911234567890',
        'email' => 'jane@example.com',
        'city' => 'Mumbai',
        'consent' => '1',
        'tags' => ['regulars', 'vip'],
    ])->assertRedirect(route('admin.clients.contacts.index', $client));

    $contact = Contact::query()->where('client_id', $client->id)->firstOrFail();
    expect($contact->name)->toBe('Jane Doe');
    expect($contact->consent)->toBeTrue();
    expect($contact->tags->pluck('name')->sort()->values()->all())->toBe(['regulars', 'vip']);

    $this->actingAs($admin)->put("/admin/clients/{$client->id}/contacts/{$contact->id}", [
        'name' => 'Jane Updated',
        'consent' => '0',
        'tags' => ['vip'],
    ])->assertRedirect();

    $contact->refresh();
    expect($contact->name)->toBe('Jane Updated');
    expect($contact->consent)->toBeFalse();
    expect($contact->tags->pluck('name')->all())->toBe(['vip']);

    $this->actingAs($admin)->delete("/admin/clients/{$client->id}/contacts/{$contact->id}")->assertRedirect();
    $this->assertSoftDeleted($contact);
});

test('importing a csv creates valid contacts and reports invalid rows', function () {
    $admin = makeCampaignAdmin();
    $client = Client::factory()->create();

    $csv = "name,phone,email,city,tags,consent\n";
    $csv .= "John Valid,+911111111111,john@example.com,Delhi,\"regulars, vip\",yes\n";
    $csv .= ",,broken@example.com,,,no\n";

    $path = tempnam(sys_get_temp_dir(), 'contacts').'.csv';
    file_put_contents($path, $csv);
    $file = new UploadedFile($path, 'contacts.csv', 'text/csv', null, true);

    $response = $this->actingAs($admin)->post("/admin/clients/{$client->id}/contacts-import", [
        'file' => $file,
    ]);

    $response->assertRedirect(route('admin.clients.contacts.index', $client));
    expect(Contact::query()->where('client_id', $client->id)->count())->toBe(1);

    $imported = Contact::query()->where('client_id', $client->id)->firstOrFail();
    expect($imported->name)->toBe('John Valid');
    expect($imported->tags->pluck('name')->sort()->values()->all())->toBe(['regulars', 'vip']);

    @unlink($path);
});

test('a user without send-campaigns permission cannot access contacts', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create();

    $this->actingAs($user)->get("/admin/clients/{$client->id}/contacts")->assertForbidden();
});

test('a client portal user is redirected away from contact routes', function () {
    $clientUser = ClientUser::factory()->create();

    $this->actingAs($clientUser, 'client')->get("/admin/clients/{$clientUser->client_id}/contacts")->assertRedirect(route('admin.login'));
});
