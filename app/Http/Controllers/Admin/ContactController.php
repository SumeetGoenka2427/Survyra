<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportContactsRequest;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Client;
use App\Models\Contact;
use App\Services\ContactService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function __construct(private readonly ContactService $contacts)
    {
    }

    public function index(Client $client): View
    {
        $this->authorize('viewAny', Contact::class);

        return view('admin.contacts.index', [
            'client' => $client,
            'contacts' => $this->contacts->paginate($client),
        ]);
    }

    public function create(Client $client): View
    {
        $this->authorize('create', Contact::class);

        return view('admin.contacts.create', ['client' => $client]);
    }

    public function store(StoreContactRequest $request, Client $client): RedirectResponse
    {
        $this->contacts->create($client, $request->validated());

        return redirect()->route('admin.clients.contacts.index', $client)->with('status', 'Contact added.');
    }

    public function edit(Client $client, Contact $contact): View
    {
        $this->authorize('update', $contact);

        return view('admin.contacts.edit', [
            'client' => $client,
            'contact' => $this->contacts->find($contact->id),
        ]);
    }

    public function update(UpdateContactRequest $request, Client $client, Contact $contact): RedirectResponse
    {
        $this->contacts->update($contact, $request->validated());

        return redirect()->route('admin.clients.contacts.index', $client)->with('status', 'Contact updated.');
    }

    public function destroy(Client $client, Contact $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);

        $this->contacts->delete($contact);

        return redirect()->route('admin.clients.contacts.index', $client)->with('status', 'Contact removed.');
    }

    public function importForm(Client $client): View
    {
        $this->authorize('create', Contact::class);

        return view('admin.contacts.import', ['client' => $client]);
    }

    public function import(ImportContactsRequest $request, Client $client): RedirectResponse
    {
        $result = $this->contacts->importFromSpreadsheet($client, $request->file('file'));

        return redirect()->route('admin.clients.contacts.index', $client)->with([
            'status' => "Imported {$result['imported']} contact(s).",
            'importErrors' => $result['errors'],
        ]);
    }
}
