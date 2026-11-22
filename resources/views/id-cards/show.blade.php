@extends('layouts.app')
@section('title', ($isContractor ? 'Contractor ID Card' : 'Staff ID Card') . ' — ' . $employee->full_name)
@section('page-title', $isContractor ? 'Official Contractor ID Card' : 'Official Ministry Staff ID Card')
@section('breadcrumb', 'HRMS › ID Cards › ' . $employee->employee_no)

@section('content')
<style>
.id-card-frame {
    width: 340px;
    min-height: 530px;
    border-radius: 16px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 12px 34px rgba(15, 23, 42, 0.16);
    border: 1px solid #cbd5e1;
    display: flex;
    flex-direction: column;
    position: relative;
}
.id-card-header-staff {
    background: linear-gradient(135deg, #0b1f4b 0%, #123f7a 100%);
    color: #ffffff;
}
.id-card-header-contractor {
    background: linear-gradient(135deg, #064e3b 0%, #0f5132 100%);
    color: #ffffff;
}
.liberia-strip {
    height: 6px;
    background: linear-gradient(90deg, #bf0a30 0%, #bf0a30 33%, #ffffff 33%, #ffffff 66%, #002868 66%, #002868 100%);
}
.id-photo-ring {
    width: 118px;
    height: 118px;
    border-radius: 50%;
    padding: 4px;
    background: #ffffff;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
    margin: -54px auto 12px;
    position: relative;
    z-index: 2;
}
.id-photo-ring img, .id-photo-initials {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}
.id-photo-initials {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: 800;
    color: #ffffff;
}
@media print {
    #sidebar, #topbar, .page-header, .d-print-none, nav, footer, header { display: none !important; }
    #main-content { margin: 0 !important; padding: 10px !important; }
    .id-card-frame { box-shadow: none !important; border: 1.5px solid #475569 !important; break-inside: avoid; }
    .id-card-header-staff, .id-card-header-contractor, .liberia-strip { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>

<div class="animate-in">
    <div class="page-header d-print-none">
        <div>
            <h1>{{ $isContractor ? 'Official Contractor ID Badge' : 'Official Ministry Staff ID Badge' }}</h1>
            <p>Front &amp; back printable ID card with cryptographic QR verification for <strong>{{ $employee->full_name }}</strong>.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('id-cards.pdf', $employee) }}" class="btn btn-danger rounded-pill px-3.5 py-2 fw-semibold">
                <i class="bi bi-file-earmark-pdf me-1"></i>Download Print-Ready PDF
            </a>
            <button type="button" class="btn btn-outline-navy rounded-pill px-3.5 py-2 fw-semibold" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print Cards
            </button>
            <a href="{{ $verifyUrl }}" target="_blank" class="btn btn-outline-success rounded-pill px-3.5 py-2 fw-semibold">
                <i class="bi bi-qr-code-scan me-1"></i>Test QR Verify Page
            </a>
            <a href="{{ auth()->user()->can('view employees') ? ($isContractor ? route('contractors.show', $employee) : route('employees.show', $employee)) : route('dashboard') }}" class="btn btn-navy rounded-pill px-3.5 py-2 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-center align-items-start gap-5 py-3">
        <!-- FRONT OF CARD -->
        <div>
            <div class="text-center text-muted small fw-bold text-uppercase mb-2 d-print-none">Front Side</div>
            <div class="id-card-frame">
                <div class="{{ $isContractor ? 'id-card-header-contractor' : 'id-card-header-staff' }} text-center px-3 pt-3 pb-5">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                        @if($logoDataUri)
                            <div class="bg-white rounded-2 p-1" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                <img src="{{ $logoDataUri }}" alt="MYS Logo" style="max-width:34px;max-height:34px;object-fit:contain;">
                            </div>
                        @endif
                        <div class="text-start">
                            <div style="font-size:9px;letter-spacing:1.2px;text-transform:uppercase;opacity:0.85;">Republic of Liberia</div>
                            <div class="fw-bold" style="font-size:12.5px;line-height:1.15;">MINISTRY OF YOUTH &amp; SPORTS</div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge rounded-pill px-3 py-1 fw-bold" style="background: rgba(255,255,255,0.2); font-size:10.5px; letter-spacing:1px;">
                            {{ $isContractor ? 'CONTRACTOR ID CARD' : 'OFFICIAL STAFF ID CARD' }}
                        </span>
                    </div>
                </div>
                <div class="liberia-strip"></div>

                <div class="id-photo-ring">
                    @if($photoDataUri)
                        <img src="{{ $photoDataUri }}" alt="{{ $employee->full_name }}">
                    @else
                        <div class="id-photo-initials" style="background: {{ $isContractor ? '#0f5132' : '#123f7a' }};">
                            {{ $initials }}
                        </div>
                    @endif
                </div>

                <div class="px-4 text-center flex-grow-1">
                    <h5 class="fw-bold text-navy mb-0">{{ $employee->full_name }}</h5>
                    <div class="fw-semibold small mt-1" style="color: {{ $isContractor ? '#0f5132' : '#bf0a30' }};">
                        {{ $employee->designation ?? ($isContractor ? 'Contract Personnel' : 'Ministry Staff') }}
                    </div>
                    <div class="small text-muted mb-3">{{ $employee->department?->name ?? 'Ministry Headquarters' }}</div>

                    <div class="bg-light border rounded-3 p-2.5 text-start small mb-3">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">{{ $isContractor ? 'Contractor ID' : 'Employee ID' }}</span>
                            <span class="fw-bold font-monospace text-navy">{{ $employee->employee_no }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Category</span>
                            <span class="fw-semibold">{{ $employee->employment_type }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Issued</span>
                            <span class="fw-semibold">{{ $issueDate->format('d M Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Valid Until</span>
                            <span class="fw-bold {{ $isContractor ? 'text-danger' : 'text-success' }}">{{ $validUntil }}</span>
                        </div>
                    </div>
                </div>

                <div class="{{ $isContractor ? 'id-card-header-contractor' : 'id-card-header-staff' }} text-center py-2 px-3" style="font-size:10px; letter-spacing:0.6px;">
                    OFFICIAL PROPERTY OF THE GOVERNMENT OF LIBERIA
                </div>
            </div>
        </div>

        <!-- BACK OF CARD -->
        <div>
            <div class="text-center text-muted small fw-bold text-uppercase mb-2 d-print-none">Back Side (QR Verification)</div>
            <div class="id-card-frame">
                <div class="{{ $isContractor ? 'id-card-header-contractor' : 'id-card-header-staff' }} text-center py-2.5 px-3">
                    <div class="fw-bold" style="font-size:11.5px;letter-spacing:0.8px;">IDENTITY VERIFICATION &amp; SECURITY</div>
                </div>
                <div class="liberia-strip"></div>

                <div class="p-4 text-center flex-grow-1 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-inline-block p-2 bg-white border rounded-3 shadow-xs mb-2">
                            <img src="{{ $qrDataUri }}" alt="QR Verification Code" style="width:148px;height:148px;">
                        </div>
                        <div class="small fw-bold text-navy">Scan QR to Verify Active Status</div>
                        <div class="font-monospace text-primary small fw-semibold">CODE: {{ $verifyHash }}</div>
                    </div>

                    <div class="text-start bg-light border rounded-3 p-2.5 my-3" style="font-size:11px;">
                        <div class="fw-bold text-navy mb-1"><i class="bi bi-telephone-fill text-danger me-1"></i>Emergency Contact</div>
                        @if($emergencyContact)
                            <div><strong>{{ $emergencyContact->name }}</strong> ({{ $emergencyContact->relationship }})</div>
                            <div class="text-muted">{{ $emergencyContact->phone }}</div>
                        @else
                            <div class="text-muted">Ministry HR Desk &bull; Monrovia, Liberia</div>
                        @endif
                    </div>

                    <div style="font-size:10.5px; color:#475569; line-height:1.45;" class="mb-3">
                        This identification card certifies that the bearer is an authorized {{ $isContractor ? 'contractor' : 'employee' }} of the <strong>Ministry of Youth &amp; Sports, Republic of Liberia</strong>. If found, please return to the Human Resources Division, Samuel K. Doe Sports Complex, Paynesville, Monrovia.
                    </div>

                    <div class="pt-2 border-top">
                        <div style="border-bottom:1px solid #64748b; width:170px; height:24px; margin:0 auto;"></div>
                        <div class="small fw-bold text-navy mt-1" style="font-size:11px;">Director of Human Resources</div>
                        <div class="text-muted" style="font-size:9.5px;">Authorized Issuing Authority</div>
                    </div>
                </div>

                <div class="liberia-strip"></div>
            </div>
        </div>
    </div>
</div>
@endsection
