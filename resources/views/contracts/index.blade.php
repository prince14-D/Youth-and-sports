@extends('layouts.app')

@section('title','Contract Management')
@section('page-title','Contract Management')
@section('breadcrumb','HRMS › Contracts')

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>Contract Management</h1>
            <p>Monitor contractors, contract expiry dates, renewal priorities, and employee profiles.</p>
        </div>
        <div class="d-flex gap-2">
            @can('create employees')
            <a href="{{ route('employees.create', ['type' => 'Contract']) }}" class="btn btn-navy">
                <i class="bi bi-file-earmark-plus me-1"></i>Register Contractor &amp; Contract
            </a>
            @endcan
            @can('manage contract renewals')
            <a href="{{ route('contracts.renewals') }}" class="btn btn-outline-navy">
                <i class="bi bi-arrow-repeat me-1"></i>Contract Renewals
            </a>
            @endcan
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Contractor</th>
                        <th>Department</th>
                        <th>Contract End</th>
                        <th>Time Remaining</th>
                        <th>Renewal Status</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                    <tr>
                        <td class="ps-4">
                            <strong>{{ $contract->full_name }}</strong>
                            <small class="d-block text-muted">{{ $contract->employee_no }} · {{ $contract->designation }}</small>
                        </td>
                        <td>{{ $contract->department?->name ?? '—' }}</td>
                        <td>{{ $contract->contract_end_date?->format('d M Y') ?? 'Not set' }}</td>
                        <td>
                            @if(!$contract->contract_end_date)
                                —
                            @elseif($contract->contract_end_date->isPast())
                                Expired
                            @else
                                {{ (int) today()->diffInDays($contract->contract_end_date) }} days
                            @endif
                        </td>
                        <td>
                            @if(!$contract->contract_end_date)
                                <span class="badge bg-warning text-dark">Date required</span>
                            @elseif($contract->contract_end_date->isPast())
                                <span class="badge bg-danger">Expired</span>
                            @elseif(today()->diffInDays($contract->contract_end_date) <= 60)
                                <span class="badge bg-warning text-dark">Renewal soon</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('contractors.show', $contract) }}" class="btn btn-sm btn-outline-navy">View</a>
                            <a href="{{ route('employees.pdf', $contract) }}" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-file-earmark-pdf"></i> PDF
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">No active contractors found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
