<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('contact'));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'consent' => ['boolean'],
            'consent_source' => ['nullable', 'string', 'max:255'],
            'tags' => ['array'],
            'tags.*' => ['string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['consent' => $this->boolean('consent')]);
    }
}
