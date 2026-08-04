<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\WebsiteLead;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebsiteLeadController extends Controller
{
    public function index(Request $request): View
    {
        $leads = WebsiteLead::query()
            ->where('client_id', $request->user()->client_id)
            ->with(['page', 'section.sectionType'])
            ->latest()
            ->paginate(20);

        return view('portal.website.leads', ['leads' => $leads]);
    }

    public function markHandled(Request $request, WebsiteLead $lead): RedirectResponse
    {
        abort_if($lead->client_id !== $request->user()->client_id, 403);

        $lead->update(['status' => 'handled']);

        return back()->with('status', 'Marked as handled.');
    }
}
