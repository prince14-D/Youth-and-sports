<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Verification | Ministry of Youth &amp; Sports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="card border-0 shadow-lg overflow-hidden" style="max-width:460px; width:100%; border-radius:16px;">
        <div class="text-white text-center p-4" style="background: linear-gradient(135deg, #0b1f4b, #123f7a);">
            @if(file_exists(public_path('images/logo.png')))
                <img src="{{ asset('images/logo.png') }}" alt="MYS" class="bg-white rounded-3 p-1.5 mb-2" style="width:52px;height:52px;object-fit:contain;">
            @endif
            <div class="small text-uppercase opacity-75" style="letter-spacing:1.2px;">Republic of Liberia</div>
            <h5 class="fw-bold mb-0">Ministry of Youth &amp; Sports</h5>
            <div class="small opacity-85">Official Staff &amp; Contractor ID Verification</div>
        </div>
        <div style="height:5px; background: linear-gradient(90deg, #bf0a30 33%, #fff 33% 66%, #002868 66%);"></div>

        <div class="card-body p-4 text-center">
            @if($isAuthentic && $employee)
                @php
                    $isActive = $employee->employment_status === 'Active';
                    $isExpiredContract = $employee->isContractor() && $employee->contract_end_date && $employee->contract_end_date->isPast();
                @endphp
                <div class="mb-3">
                    @if($isActive && !$isExpiredContract)
                        <span class="badge bg-success rounded-pill px-3 py-2 fs-6"><i class="bi bi-patch-check-fill me-1"></i>VERIFIED ACTIVE PERSONNEL</span>
                    @elseif($isExpiredContract)
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fs-6"><i class="bi bi-exclamation-triangle-fill me-1"></i>CONTRACT EXPIRED</span>
                    @else
                        <span class="badge bg-danger rounded-pill px-3 py-2 fs-6"><i class="bi bi-x-octagon-fill me-1"></i>STATUS: {{ strtoupper($employee->employment_status) }}</span>
                    @endif
                </div>

                @if($employee->photo_path && file_exists(public_path("storage/{$employee->photo_path}")))
                    <img src="{{ $employee->photo_url }}" alt="{{ $employee->full_name }}" class="rounded-circle border border-3 border-primary mb-2" style="width:96px;height:96px;object-fit:cover;">
                @else
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold mb-2" style="width:96px;height:96px;font-size:28px;">
                        {{ strtoupper(substr($employee->first_name,0,1) . substr($employee->last_name,0,1)) }}
                    </div>
                @endif

                <h4 class="fw-bold mb-0">{{ $employee->full_name }}</h4>
                <div class="text-danger fw-semibold small mb-3">{{ $employee->designation }}</div>

                <div class="bg-light rounded-3 border p-3 text-start small">
                    <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">ID Number</span><strong class="font-monospace">{{ $employee->employee_no }}</strong></div>
                    <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Category</span><strong>{{ $employee->employment_type }}</strong></div>
                    <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Department</span><strong>{{ $employee->department?->name ?? 'Ministry HQ' }}</strong></div>
                    <div class="d-flex justify-content-between py-1 border-bottom"><span class="text-muted">Status</span><strong>{{ $employee->employment_status }}</strong></div>
                    @if($employee->isContractor())
                        <div class="d-flex justify-content-between py-1"><span class="text-muted">Contract Valid Until</span><strong>{{ $employee->contract_end_date?->format('d M Y') ?? '—' }}</strong></div>
                    @endif
                </div>
            @else
                <div class="py-3">
                    <i class="bi bi-shield-x text-danger" style="font-size:48px;"></i>
                    <h5 class="fw-bold text-danger mt-2">Invalid or Unverified ID Badge</h5>
                    <p class="text-muted small mb-0">
                        The ID badge <code>{{ $employeeNo }}</code> could not be verified in the Ministry of Youth &amp; Sports HRMIS registry.
                    </p>
                </div>
            @endif

            <div class="text-muted mt-3" style="font-size:11px;">
                Checked on {{ $checkedAt->format('d M Y, h:i A') }} &bull; MYS HRMIS
            </div>
        </div>
    </div>
</body>
</html>
