<?php

use App\SectionTypes\CtaSectionType;
use App\SectionTypes\ContactFormSectionType;
use App\SectionTypes\GallerySectionType;
use App\SectionTypes\HeroSectionType;
use App\SectionTypes\TestimonialsSectionType;
use App\SectionTypes\TextBlockSectionType;

/*
|--------------------------------------------------------------------------
| Section Type Registry
|--------------------------------------------------------------------------
|
| Maps a section_types.key to the class implementing SectionTypeContract.
| Adding a new section type is: one class implementing the contract, one
| line here, one row in the section_types table - nothing else changes.
|
*/

return [
    'hero' => HeroSectionType::class,
    'text_block' => TextBlockSectionType::class,
    'gallery' => GallerySectionType::class,
    'testimonials' => TestimonialsSectionType::class,
    'cta' => CtaSectionType::class,
    'contact_form' => ContactFormSectionType::class,
];
