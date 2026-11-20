@extends('layouts.app')
@section('title', 'Manage Vacancies')
@section('page-title', 'Manage Vacancies')
@section('breadcrumb', 'HRMS › Recruitment › Vacancies')

@section('content')
<div class="animate-in">
    <div class="page-header">
        <div>
            <h1>Job Vacancies</h1>
            <p>Publish job openings and manage position requirements for the Ministry.</p>
        </div>
        @can('create vacancies')<button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#postJobModal">
            <i class="bi bi-plus-circle me-1"></i>Post New Job
        </button>@endcan
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

    <div class="card">
        <div class="card-header"><div class="card-header-title"><i class="bi bi-briefcase"></i>Job Openings Board</div></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                            <th class="ps-4 py-3">Job Title</th>
                            <th class="py-3">Department</th>
                            <th class="py-3 text-center">Positions</th>
                            <th class="py-3 text-center">Closing Date</th>
                            <th class="py-3 text-center">Applicants</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="pe-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vacancies as $job)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td class="ps-4 py-3">
                                <div style="font-weight:600; color:#1e293b; font-size:13.5px;">{{ $job->title }}</div>
                                <div style="font-size:11px; color:#64748b;" title="{{ $job->requirements }}">
                                    Requirements: {{ Str::limit($job->requirements ?? 'None listed', 50) }}
                                </div>
                            </td>
                            <td style="font-size:13px; color:#334155;">{{ $job->department->name }}</td>
                            <td class="text-center font-monospace">{{ $job->positions }}</td>
                            <td class="text-center font-monospace" style="font-size:12.5px;">
                                {{ $job->closing_date ? $job->closing_date->format('d M Y') : 'Open Ended' }}
                            </td>
                            <td class="text-center">
                                <span class="badge" style="background-color:rgba(78,96,255,.08); color:var(--navy); font-size:12px;">
                                    {{ $job->applications_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($job->status == 'Draft')
                                <span class="badge bg-secondary">Draft</span>
                                @elseif($job->status == 'Open')
                                <span class="badge bg-success">Open</span>
                                @elseif($job->status == 'Closed')
                                <span class="badge bg-danger">Closed</span>
                                @else
                                <span class="badge bg-info text-white">Filled</span>
                                @endif
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @can('edit vacancies')<button class="btn btn-sm btn-outline-navy" data-bs-toggle="modal" data-bs-target="#editJobModal-{{ $job->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>@endcan
                                    @can('delete vacancies')<form action="{{ route('recruitment.vacancies.destroy', $job) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this vacancy?')" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>@endcan
                                </div>

                                <!-- Edit Job Modal -->
                                <div class="modal fade" id="editJobModal-{{ $job->id }}" tabindex="-1" aria-hidden="true" style="text-align:left;">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('recruitment.vacancies.update', $job) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title text-navy"><i class="bi bi-pencil me-2"></i>Edit Job Opening</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Job Title <span class="text-danger">*</span></label>
                                                        <input type="text" name="title" class="form-control" value="{{ $job->title }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Department <span class="text-danger">*</span></label>
                                                        <select name="department_id" class="form-select" required>
                                                            @foreach($departments as $dept)
                                                            <option value="{{ $dept->id }}" {{ $job->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label">Open Positions <span class="text-danger">*</span></label>
                                                            <input type="number" name="positions" class="form-control" value="{{ $job->positions }}" min="1" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label">Closing Date</label>
                                                            <input type="date" name="closing_date" class="form-control" value="{{ $job->closing_date?->format('Y-m-d') }}">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                                        <select name="status" class="form-select" required>
                                                            @foreach(['Draft','Open','Closed','Filled'] as $st)
                                                            <option value="{{ $st }}" {{ $job->status == $st ? 'selected' : '' }}>{{ $st }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Job Description</label>
                                                        <textarea name="description" class="form-control" rows="2">{{ $job->description }}</textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Job Requirements</label>
                                                        <textarea name="requirements" class="form-control" rows="2">{{ $job->requirements }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-navy">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-briefcase fs-2 d-block mb-2"></i>
                                No job vacancies listed. Click "Post New Job" to list one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Post Job Modal -->
<div class="modal fade" id="postJobModal" tabindex="-1" aria-labelledby="postJobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('recruitment.vacancies.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="postJobModalLabel"><i class="bi bi-plus-circle me-2 text-navy"></i>Post Job Opening</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Job Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Senior Administrative Assistant" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select" required>
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Open Positions <span class="text-danger">*</span></label>
                            <input type="number" name="positions" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Closing Date</label>
                            <input type="date" name="closing_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="Open">Open</option>
                            <option value="Draft">Draft</option>
                            <option value="Closed">Closed</option>
                            <option value="Filled">Filled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Job Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Describe the job role and core responsibilities..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Job Requirements</label>
                        <textarea name="requirements" class="form-control" rows="2" placeholder="List qualification rules, required years of experience, etc..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Post Job</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
