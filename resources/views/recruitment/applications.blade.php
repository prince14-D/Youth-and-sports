@extends('layouts.app')
@section('title', 'Manage Applications')
@section('page-title', 'Job Applications')
@section('breadcrumb', 'HRMS › Recruitment › Applications')

@section('content')
<div class="animate-in">
    <div class="page-header flex-wrap">
        <div>
            <h1>Job Applications</h1>
            <p>Track, screen, and manage candidates applying for job openings.</p>
        </div>
        <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#logApplicationModal">
            <i class="bi bi-person-plus-fill me-1"></i>Log Candidate Application
        </button>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger mb-4">
        <strong><i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
            <li style="font-size:13px;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('recruitment.applications') }}" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Filter by Vacancy</label>
                    <select name="vacancy_id" class="form-select">
                        <option value="">All Openings</option>
                        @foreach($vacancies as $vac)
                        <option value="{{ $vac->id }}" {{ $vacancyId == $vac->id ? 'selected' : '' }}>{{ $vac->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach(['Applied', 'Shortlisted', 'Interviewed', 'Offered', 'Hired', 'Rejected'] as $st)
                        <option value="{{ $st }}" {{ $status == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-navy w-100"><i class="bi bi-funnel me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Candidate Grid Table -->
    <div class="card">
        <div class="card-header"><div class="card-header-title"><i class="bi bi-people"></i>Candidate Pipeline ({{ $applications->count() }})</div></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                            <th class="ps-4 py-3">Applicant</th>
                            <th class="py-3">Position Applied For</th>
                            <th class="py-3 text-center">Resume</th>
                            <th class="py-3 text-center">Pipeline Status</th>
                            <th class="py-3 text-center">Date Applied</th>
                            <th class="pe-4 py-3 text-end" style="width:25%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td class="ps-4 py-3">
                                <div style="font-weight:600; color:#1e293b; font-size:13.5px;">{{ $app->applicant_name }}</div>
                                <div style="font-size:11px; color:#64748b;">
                                    {{ $app->email }} @if($app->phone) &bull; {{ $app->phone }} @endif
                                </div>
                                    @if($app->employee)
                                        <span class="badge bg-info-subtle text-info-emphasis ms-2">Internal employee</span>
                                    @endif
                            </td>
                            <td style="font-size:13px; color:#334155;">
                                <div style="font-weight:500;">{{ $app->vacancy->title }}</div>
                                <div style="font-size:11px; color:#64748b;">{{ $app->vacancy->department->name }}</div>
                            </td>
                            <td class="text-center">
                                @if($app->resume_path)
                                <a href="{{ asset('storage/'.$app->resume_path) }}" target="_blank" class="btn btn-sm btn-outline-navy" title="Download CV">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i> View CV
                                </a>
                                @else
                                <span class="text-muted" style="font-size:12px;">Not uploaded</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($app->status == 'Applied')
                                <span class="badge bg-secondary">Applied</span>
                                @elseif($app->status == 'Shortlisted')
                                <span class="badge bg-info text-white">Shortlisted</span>
                                @elseif($app->status == 'Interviewed')
                                <span class="badge bg-primary">Interviewed</span>
                                @elseif($app->status == 'Offered')
                                <span class="badge bg-warning text-dark">Job Offered</span>
                                @elseif($app->status == 'Hired')
                                <span class="badge bg-success">Hired</span>
                                @else
                                <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td class="text-center font-monospace" style="font-size:12.5px;">
                                {{ $app->created_at->format('d M Y') }}
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <form action="{{ route('recruitment.applications.status', $app->id) }}" method="POST" style="display:inline-flex; gap:4px;">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="form-select form-select-sm" style="font-size:12px; width:130px;" onchange="this.form.submit()">
                                            @foreach(['Applied','Shortlisted','Interviewed','Offered','Hired','Rejected'] as $st)
                                            <option value="{{ $st }}" {{ $app->status == $st ? 'selected' : '' }}>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                    <form action="{{ route('recruitment.applications.destroy', $app->id) }}" method="POST" onsubmit="return confirm('Delete this application?')" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Application">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @can('schedule interviews')
                                    <button type="button" class="btn btn-sm btn-outline-navy" data-bs-toggle="modal" data-bs-target="#interviewModal-{{ $app->id }}" title="Schedule interview">
                                        <i class="bi bi-calendar-plus"></i>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-2 d-block mb-2"></i>
                                No applications received. Click "Log Candidate Application" to submit one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @foreach($applications as $app)
    @can('schedule interviews')
    <div class="modal fade" id="interviewModal-{{ $app->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"><div class="modal-content"><form method="POST" action="{{ route('recruitment.interviews.store', $app) }}">@csrf
            <div class="modal-header"><h5 class="modal-title">Schedule interview: {{ $app->applicant_name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="mb-3"><label class="form-label">Date and time</label><input type="datetime-local" name="scheduled_at" class="form-control" required></div><div class="row g-2"><div class="col-6"><label class="form-label">Duration</label><input type="number" name="duration_minutes" value="60" min="15" max="480" class="form-control" required></div><div class="col-6"><label class="form-label">Mode</label><select name="mode" class="form-select"><option>In person</option><option>Virtual</option><option>Phone</option></select></div></div><div class="mb-3 mt-3"><label class="form-label">Interviewer</label><input name="interviewer" class="form-control" required></div><div class="mb-3"><label class="form-label">Location or meeting URL</label><input name="location" class="form-control"></div><textarea name="notes" class="form-control" placeholder="Notes"></textarea></div>
            <div class="modal-footer"><button type="submit" class="btn btn-accent">Schedule and notify</button></div>
        </form></div></div>
    </div>
    @endcan
    @endforeach
</div>

<!-- Log Application Modal -->
<div class="modal fade" id="logApplicationModal" tabindex="-1" aria-labelledby="logApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('recruitment.applications.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="logApplicationModalLabel"><i class="bi bi-person-plus-fill me-2 text-navy"></i>Log Candidate Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Applied Position <span class="text-danger">*</span></label>
                        <select name="vacancy_id" class="form-select" required>
                            <option value="">Select Vacancy</option>
                            @foreach($vacancies as $vac)
                            <option value="{{ $vac->id }}">{{ $vac->title }} ({{ $vac->department->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Applicant Name <span class="text-danger">*</span></label>
                        <input type="text" name="applicant_name" class="form-control" placeholder="Candidate's full name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="candidate@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+231 XXX XXX XXXX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resume / CV</label>
                        <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                        <div class="form-text" style="font-size:11px;">PDF, Word document format. Max size 5MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Log Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
