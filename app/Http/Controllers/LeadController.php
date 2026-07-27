<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request): RedirectResponse
    {
        Lead::create($request->safe()->except('company_website'));

        return redirect(route('home').'#demo')
            ->with('status', "Thanks! We've got your details and will reach out shortly with your demo.");
    }
}
