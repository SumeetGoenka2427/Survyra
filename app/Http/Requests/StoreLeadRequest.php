<?php

namespace App\Http\Requests;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
            'preferred_contact' => ['nullable', 'string', 'in:'.implode(',', Lead::PREFERRED_CONTACTS)],
            'interests' => ['nullable', 'array'],
            'interests.*' => ['string', 'in:'.implode(',', Lead::INTERESTS)],
            // Honeypot: a real visitor never sees or fills this field (hidden via CSS in the form).
            'company_website' => ['prohibited'],
        ];
    }
}
