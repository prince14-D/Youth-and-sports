@extends('layouts.app')
@section('title', 'Audit Logs')
@section('page-title', 'System Audit Trail')
@section('breadcrumb', 'HRMS › Audit Logs')

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>System Audit Trail</h1>
            <p>Comprehensive record of all database and authentication events inside the system.</p>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4">
        <div class="card-header"><div class="card-header-title"><i class="bi bi-filter"></i>Filter Trail</div></div>
        <div class="card-body">
            <form method="GET" action="{{ route('audit.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Action</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">All Actions</option>
                        @foreach(['created', 'updated', 'deleted', 'login', 'logout', 'login_failed'] as $act)
                        <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Model Type</label>
                    <input type="text" name="model_type" class="form-control form-control-sm" placeholder="e.g. Employee" value="{{ request('model_type') }}">
                </div>
                <div class="col-md-2.5">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2.5">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-navy btn-sm flex-fill">Apply Filters</button>
                    @if(request()->anyFilled(['action', 'model_type', 'date_from', 'date_to']))
                    <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Trail Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Object</th>
                            <th>Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td style="white-space:nowrap;font-size:12px;">{{ $log->created_at?->format('d M Y, h:i A') }}</td>
                            <td>
                                <div><strong>{{ $log->user_name ?? 'System' }}</strong></div>
                                @if($log->user_id)
                                <div style="font-size:10px;color:var(--text-muted);">ID: {{ $log->user_id }}</div>
                                @endif
                            </td>
                            <td>
                                @php
                                $badgeColor = match($log->action) {
                                    'created' => 'success',
                                    'updated' => 'warning text-dark',
                                    'deleted' => 'danger',
                                    'login' => 'info',
                                    'logout' => 'secondary',
                                    'login_failed' => 'danger',
                                    default => 'secondary'
                                };
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}">{{ strtoupper($log->action) }}</span>
                            </td>
                            <td>
                                @if($log->model_type)
                                <code style="font-size:12px;background:#f0f2f7;padding:2px 6px;border-radius:4px;">
                                    {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                                </code>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td style="font-size:13px;max-width:350px;word-break:break-all;">
                                {{ $log->description }}
                            </td>
                            <td style="font-size:12px;">{{ $log->ip_address }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5" style="color:var(--text-muted);">
                                <i class="bi bi-journal-text fs-2 d-block mb-2"></i>No audit logs recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
            <div class="px-3 py-2 border-top">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
