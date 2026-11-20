@extends('layouts.app')
@section('title', 'Internal Recruitment')
@section('page-title', 'Internal Recruitment')
@section('breadcrumb', 'HRMS › Recruitment › Employee Opportunities')
@section('content')
<div class="animate-in">
    <div class="page-header">
        <div><h1>Internal Recruitment</h1><p>Explore open opportunities and track your applications.</p></div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4">
        <h5 class="fw-bold text-navy mb-3"><i class="bi bi-briefcase me-2 text-primary"></i>Open Opportunities</h5>
        <div class="row g-3">
            @forelse($vacancies as $vacancy)
                <div class="col-12 col-lg-6">
                    <article class="border rounded-3 p-4 h-100">
                        <div class="d-flex justify-content-between gap-2"><h5 class="fw-bold text-navy mb-1">{{ $vacancy->title }}</h5><span class="badge bg-success">Open</span></div>
                        <div class="text-muted small mb-3">{{ $vacancy->department?->name ?? 'Ministry-wide' }} · {{ $vacancy->positions }} position(s) · Closes {{ $vacancy->closing_date?->format('d M Y') ?? 'No closing date' }}</div>
                        <p class="small text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($vacancy->description), 180) }}</p>
                        @can('apply vacancies')
                        <button class="btn btn-navy btn-sm" data-bs-toggle="modal" data-bs-target="#apply{{ $vacancy->id }}"><i class="bi bi-send me-1"></i>Apply</button>
                        @endcan
                    </article>
                </div>
                @can('apply vacancies')
                <div class="modal fade" id="apply{{ $vacancy->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" action="{{ route('employee.recruitment.apply', $vacancy) }}" enctype="multipart/form-data">@csrf<div class="modal-header"><h5 class="modal-title">Apply for {{ $vacancy->title }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p class="text-muted small">Your employee profile will be used for your name, email, and phone.</p><label class="form-label fw-semibold">Cover Letter</label><textarea name="cover_letter" class="form-control" rows="6" required placeholder="Explain your interest and experience for this role."></textarea><label class="form-label fw-semibold mt-3">Resume <span class="text-muted">(optional)</span></label><input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx"><div class="form-text">PDF, DOC, or DOCX up to 5 MB.</div></div><div class="modal-footer"><button type="submit" class="btn btn-navy">Submit Application</button></div></form></div></div></div>
                @endcan
            @empty
                <div class="col-12"><div class="text-center text-muted py-4">There are no open internal opportunities right now.</div></div>
            @endforelse
        </div>
    </div></div>

    <div class="card border-0 shadow-sm"><div class="card-body p-0"><div class="p-4 border-bottom"><h5 class="fw-bold text-navy mb-0"><i class="bi bi-list-check me-2 text-primary"></i>My Applications</h5></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-4">Position</th><th>Submitted</th><th>Status</th><th class="pe-4">Resume</th></tr></thead><tbody>
        @forelse($applications as $application)
            <tr><td class="ps-4"><strong>{{ $application->vacancy?->title ?? 'Vacancy removed' }}</strong><small class="d-block text-muted">{{ $application->vacancy?->department?->name ?? '—' }}</small></td><td>{{ $application->created_at?->format('d M Y') }}</td><td><span class="badge {{ in_array($application->status, ['Hired','Offered']) ? 'bg-success' : ($application->status === 'Rejected' ? 'bg-danger' : 'bg-primary') }}">{{ $application->status }}</span></td><td class="pe-4">@if($application->resume_path)<a href="{{ asset('storage/'.$application->resume_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-arrow-down"></i></a>@else<span class="text-muted">—</span>@endif</td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">You have not submitted an internal application.</td></tr>
        @endforelse
        </tbody></table></div></div></div>
</div>
@endsection
