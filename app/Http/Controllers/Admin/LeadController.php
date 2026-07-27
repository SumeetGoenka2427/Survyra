<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(): View
    {
        return view('admin.leads.index', [
            'leads' => Lead::query()->latest()->paginate(20),
        ]);
    }

    public function updateStatus(Request $request, Lead $lead): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', Lead::STATUSES)],
        ]);

        $lead->update(['status' => $request->string('status')->toString()]);

        return back()->with('status', 'Lead status updated.');
    }
}
