<?php

namespace Database\Seeders;

use App\Models\SectionType;
use App\Services\SectionTypeRegistry;
use Illuminate\Database\Seeder;

class SectionTypeSeeder extends Seeder
{
    public function run(SectionTypeRegistry $registry): void
    {
        foreach ($registry->all() as $contract) {
            SectionType::query()->updateOrCreate(
                ['key' => $contract->key()],
                [
                    'label' => $contract->label(),
                    'category' => $contract->category(),
                    'is_active' => true,
                ]
            );
        }
    }
}
