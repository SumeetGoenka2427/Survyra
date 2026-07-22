<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateThankyouRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('survey'));
    }

    protected function prepareForValidation(): void
    {
        foreach (['show_google_review', 'show_facebook', 'show_instagram', 'show_website', 'show_coupon', 'show_complaint_form', 'show_support_number', 'show_whatsapp_button'] as $flag) {
            $this->merge([$flag => $this->boolean($flag)]);
        }
    }

    public function rules(): array
    {
        return [
            'min_score' => ['nullable', 'integer'],
            'max_score' => ['nullable', 'integer'],
            'thank_you_message' => ['nullable', 'string', 'max:1000'],
            'show_google_review' => ['boolean'],
            'show_facebook' => ['boolean'],
            'show_instagram' => ['boolean'],
            'show_website' => ['boolean'],
            'show_coupon' => ['boolean'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'show_complaint_form' => ['boolean'],
            'show_support_number' => ['boolean'],
            'show_whatsapp_button' => ['boolean'],
            'manager_contact.name' => ['nullable', 'string', 'max:255'],
            'manager_contact.phone' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->route('sentiment') === 'negative' && $this->boolean('show_google_review')) {
                $validator->errors()->add(
                    'show_google_review',
                    'Negative feedback can never be routed to a Google Review request.'
                );
            }
        });
    }
}
