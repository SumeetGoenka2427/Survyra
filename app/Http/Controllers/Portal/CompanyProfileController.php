<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\UpdateCompanyProfileRequest;
use App\Services\ClientService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyProfileController extends Controller
{
    public function __construct(private readonly ClientService $clients)
    {
    }

    public function edit(Request $request): View
    {
        return view('portal.company.edit', [
            'client' => $request->user()->client,
        ]);
    }

    public function update(UpdateCompanyProfileRequest $request): RedirectResponse
    {
        $this->clients->update($request->user()->client, $request->validated());

        return redirect()->route('portal.company.edit')->with('status', 'Company profile updated.');
    }
}
