<?php

namespace App\Services;

use App\Imports\ContactsImport;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class ContactService
{
    public function paginate(Client $client, int $perPage = 20): LengthAwarePaginator
    {
        return Contact::query()
            ->where('client_id', $client->id)
            ->with('tags')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): Contact
    {
        return Contact::query()->with('tags')->findOrFail($id);
    }

    public function create(Client $client, array $data): Contact
    {
        $contact = Contact::query()->create([...$data, 'client_id' => $client->id]);

        if (! empty($data['tags'])) {
            $contact->tags()->sync($this->resolveTagIds($client, $data['tags']));
        }

        return $contact;
    }

    public function update(Contact $contact, array $data): Contact
    {
        $contact->update($data);

        if (array_key_exists('tags', $data)) {
            $contact->tags()->sync($this->resolveTagIds($contact->client, $data['tags'] ?? []));
        }

        return $contact;
    }

    public function delete(Contact $contact): void
    {
        $contact->delete();
    }

    /**
     * @return array{imported: int, errors: array<int, string>}
     */
    public function importFromSpreadsheet(Client $client, UploadedFile $file): array
    {
        $import = new ContactsImport($client);
        $import->import($file);

        return [
            'imported' => $import->importedCount(),
            'errors' => $import->errors(),
        ];
    }

    /**
     * @param  array<int, string>  $tagNames
     * @return array<int, int>
     */
    private function resolveTagIds(Client $client, array $tagNames): array
    {
        return collect($tagNames)
            ->filter()
            ->map(fn (string $name) => $client->contactTags()->firstOrCreate(['name' => trim($name)])->id)
            ->all();
    }
}
