<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $client = $request->attributes->get('api_client');

        $contacts = Contact::where('client_id', $client->id)
            ->select(['id', 'name', 'email', 'phone', 'consent', 'created_at'])
            ->latest()
            ->paginate(50);

        return response()->json($contacts);
    }

    public function store(Request $request): JsonResponse
    {
        $client = $request->attributes->get('api_client');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'consent' => ['boolean'],
        ]);

        $contact = Contact::create(['client_id' => $client->id, ...$data]);

        return response()->json($contact, 201);
    }
}
