@extends('layouts.app')
@section('title', 'Coming Soon')
@section('page-title', $module)
@section('breadcrumb', 'HRMS › ' . $module)

@section('content')
<div class="animate-in text-center py-5">
    <div class="card p-5 mx-auto" style="max-width:550px;border-radius:20px;box-shadow:var(--shadow-md);">
        <div style="width:72px;height:72px;border-radius:50%;background:rgba(78,96,255,.1);color:var(--accent-dark);display:flex;align-items:center;justify-content:center;font-size:36px;margin:0 auto 24px;">
            <i class="bi bi-clock-history"></i>
        </div>
        <h2 style="font-family:'DM Sans',sans-serif;font-weight:700;margin-bottom:12px;">{{ $module }}</h2>
        <p class="text-muted" style="font-size:14px;line-height:1.6;margin-bottom:28px;">
            This module is scheduled for implementation in <strong>Phase {{ $phase }}</strong> of the development roadmap for the Ministry of Youth and Sports HRMS.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-navy">
                <i class="bi bi-grid-1x2 me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
