<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\Contact;
use Illuminate\Support\Collection as SupportCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

/**
 * Expects a heading row: name, phone, email, city, tags (comma-separated), consent (yes/no).
 */
class ContactsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    private int $imported = 0;

    public function __construct(private readonly Client $client)
    {
    }

    public function collection(SupportCollection $rows): void
    {
        foreach ($rows as $row) {
            $contact = Contact::query()->create([
                'client_id' => $this->client->id,
                'name' => (string) $row['name'],
                'phone' => isset($row['phone']) && $row['phone'] !== '' ? (string) $row['phone'] : null,
                'email' => isset($row['email']) && $row['email'] !== '' ? (string) $row['email'] : null,
                'city' => isset($row['city']) && $row['city'] !== '' ? (string) $row['city'] : null,
                'consent' => $this->parseConsent($row['consent'] ?? null),
                'consent_source' => 'csv_import',
            ]);

            $tagNames = array_filter(array_map('trim', explode(',', (string) ($row['tags'] ?? ''))));

            if ($tagNames) {
                $tagIds = collect($tagNames)->map(
                    fn (string $name) => $this->client->contactTags()->firstOrCreate(['name' => $name])->id
                );
                $contact->tags()->sync($tagIds);
            }

            $this->imported++;
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            // Not typed as 'string': PhpSpreadsheet auto-detects numeric-looking
            // cells (e.g. a phone number starting with '+') as int/float, not
            // string - the collection() method casts everything explicitly.
            'name' => ['required', 'max:255'],
            'phone' => ['nullable', 'max:30'],
            'email' => ['nullable', 'email'],
        ];
    }

    public function importedCount(): int
    {
        return $this->imported;
    }

    /**
     * @return array<int, string>
     */
    public function errors(): array
    {
        return collect($this->failures())
            ->map(fn (Failure $failure) => "Row {$failure->row()}: ".implode(', ', $failure->errors()))
            ->all();
    }

    private function parseConsent(mixed $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'yes', 'true', 'y'], true);
    }
}
