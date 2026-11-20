@extends('layouts.app')
@section('title', 'My Notifications')
@section('page-title', 'My Notifications')
@section('breadcrumb', 'HRMS › Notifications')
@section('content')
<div class="animate-in"><div class="page-header"><div><h1>My Notifications</h1><p>Updates about your leave, excuses, applications, and interviews.</p></div><form method="POST" action="{{ route('employee.notifications.read-all') }}">@csrf<button class="btn btn-outline-navy"><i class="bi bi-check2-all me-1"></i>Mark all read</button></form></div><div class="card border-0 shadow-sm"><div class="list-group list-group-flush">@forelse($notifications as $notification)<a href="{{ route('employee.notifications.read', $notification->id) }}" class="list-group-item list-group-item-action p-4 {{ $notification->read_at ? '' : 'bg-light' }}"><div class="d-flex justify-content-between"><strong class="text-navy">{{ $notification->title }}</strong><small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small></div><div class="text-muted small mt-1">{{ $notification->message }}</div></a>@empty<div class="text-center text-muted py-5">No notifications yet.</div>@endforelse</div></div><div class="mt-3">{{ $notifications->links() }}</div></div>
@endsection
