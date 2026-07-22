<?php

namespace App\Http\Requests;

use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Campaign::class);
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'survey_id' => ['required', 'exists:surveys,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:sms,whatsapp,email'],
            'message_template' => ['required', 'string', 'max:2000'],
            'tag_ids' => ['array'],
            'tag_ids.*' => ['integer', 'exists:contact_tags,id'],
        ];
    }
}
