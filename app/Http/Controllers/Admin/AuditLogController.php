<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAuditLog', \App\Models\User::class);

        $query = Activity::query()->latest();

        if ($request->filled('causer_type')) {
            $query->where('causer_type', $request->input('causer_type'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return view('admin.audit-log.index', [
            'logs' => $query->paginate(50),
            'causerTypes' => Activity::query()->select('causer_type')->distinct()->pluck('causer_type'),
            'subjectTypes' => Activity::query()->select('subject_type')->distinct()->pluck('subject_type'),
        ]);
    }
}