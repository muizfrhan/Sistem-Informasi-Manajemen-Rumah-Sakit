<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Permission;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->denyUnless($request->user()->hasPermission(Permission::VIEW_AUDIT_LOG));

        $logs = AuditLog::query()
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->string('event')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('user_name', 'ILIKE', '%'.$request->string('q')->toString().'%')
                ->orWhere('auditable_type', 'ILIKE', '%'.$request->string('q')->toString().'%')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'events' => ['created', 'updated', 'deleted', 'restored', 'viewed', 'login', 'login_failed', 'logout'],
            'users' => \App\Models\User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(AuditLog $auditlog): View
    {
        $this->denyUnless(auth()->user()->hasPermission(Permission::VIEW_AUDIT_LOG));

        return view('admin.audit-logs.show', [
            'log' => $auditlog,
            'audit' => app(AuditLogger::class),
        ]);
    }
}
