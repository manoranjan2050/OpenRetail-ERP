<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Activity::class);

        $logs = Activity::with('causer')
            ->when($request->causer_id, fn($q, $c) => $q->where('causer_id', $c))
            ->when($request->log_name, fn($q, $l) => $q->where('log_name', $l))
            ->when($request->from, fn($q, $f) => $q->whereDate('created_at', '>=', $f))
            ->when($request->to, fn($q, $t) => $q->whereDate('created_at', '<=', $t))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('audit-log.index', [
            'logs' => $logs,
            'filters' => $request->only(['causer_id', 'log_name', 'from', 'to']),
        ]);
    }
}
