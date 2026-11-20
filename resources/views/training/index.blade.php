@extends('layouts.app')
@section('title', 'Training Management')
@section('page-title', 'Training Management')
@section('breadcrumb', 'HRMS › Training Programs')

@section('content')
<div class="animate-in">
    <div class="page-header flex-wrap">
        <div>
            <h1>Training & Capacity Building</h1>
            <p>Publish training courses and track employee capacity building achievements.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#enrollStaffModal">
                <i class="bi bi-person-plus-fill me-1"></i>Enroll Employee
            </button>
            <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#newCourseModal">
                <i class="bi bi-plus-circle-fill me-1"></i>Add New Course
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Courses List -->
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header"><div class="card-header-title"><i class="bi bi-journal-bookmark-fill"></i>Available Courses</div></div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($courses as $course)
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="fw-bold text-navy mb-1" style="font-size:14px;">{{ $course->title }}</h6>
                                <span class="badge bg-secondary font-monospace">{{ $course->type }}</span>
                            </div>
                            <div class="text-muted mb-2" style="font-size:12px;">Provider: {{ $course->provider }}</div>
                            <div class="d-flex justify-content-between text-muted" style="font-size:11px;">
                                <span><i class="bi bi-calendar-range me-1"></i>{{ $course->start_date->format('d M') }} &rarr; {{ $course->end_date->format('d M Y') }}</span>
                                <span><i class="bi bi-people me-1"></i>{{ $course->enrollments_count }} enrolled</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                            No courses available. Click "Add New Course" to list one.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollments Table -->
        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header"><div class="card-header-title"><i class="bi bi-mortarboard"></i>Capacity Logs & Progress</div></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:13px;">
                            <thead>
                                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                                    <th class="ps-3 py-3">Employee</th>
                                    <th class="py-3">Course Title</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-center">Completion</th>
                                    <th class="pe-3 py-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($enrollments as $enr)
                                <tr style="border-bottom:1px solid #f1f5f9;">
                                    <td class="ps-3 py-3">
                                        <div style="font-weight:600; color:#1e293b;">{{ $enr->employee?->full_name ?? 'Employee Record Deleted' }}</div>
                                        <div style="font-size:10.5px; color: #dc2626 !important;" class="fw-bold">{{ $enr->employee?->employee_no ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight:500; color:#334155;">{{ $enr->training?->title ?? 'Training Module' }}</div>
                                        <div style="font-size:10.5px; color:#64748b;">{{ $enr->training?->provider ?? '—' }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($enr->status == 'Completed')
                                        <span class="badge bg-success">Completed</span>
                                        @else
                                        <span class="badge bg-warning text-dark">In Progress</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-monospace" style="font-size:11.5px;">
                                        {{ $enr->completion_date ? $enr->completion_date->format('d M Y') : '—' }}
                                    </td>
                                    <td class="pe-3 py-3 text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            @if($enr->status == 'Registered')
                                            <form action="{{ route('training.complete', $enr->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Mark Completed">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            </form>
                                            @endif
                                            <form action="{{ route('training.destroy-enrollment', $enr->id) }}" method="POST" onsubmit="return confirm('Remove this enrollment?')" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Enrollment">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        No enrollments logged. Click "Enroll Employee" to log staff progress.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="newCourseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('training.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2 text-navy"></i>Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Course Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Civil Service Administration Ethics" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Course Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="Workshop">Workshop</option>
                                <option value="Seminar">Seminar</option>
                                <option value="Conference">Conference</option>
                                <option value="Online Course">Online Course</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Provider <span class="text-danger">*</span></label>
                            <input type="text" name="provider" class="form-control" placeholder="e.g. LIPA" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. LIPA Compound, Monrovia" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Course outline, goals..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Publish Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enroll Staff Modal -->
<div class="modal fade" id="enrollStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('training.enroll') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2 text-navy"></i>Enroll Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Staff</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_no }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course <span class="text-danger">*</span></label>
                        <select name="training_id" class="form-select" required>
                            <option value="">Select Training Course</option>
                            @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }} ({{ $course->provider }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Enroll</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
