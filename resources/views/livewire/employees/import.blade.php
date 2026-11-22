<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="text-navy fw-bold mb-1"><i class="bi bi-file-earmark-spreadsheet me-1 text-success"></i>Bulk Employee &amp; Contractor CSV Import</h4>
                <p class="text-muted small mb-0">
                    Upload your Ministry staff roster (Permanent, Contract, Casual, or Intern). Linked login accounts are created automatically with default password <code>MYSWelcome@2026!</code> (force-change on first login).
                </p>
            </div>
            <button type="button" wire:click="downloadTemplate" class="btn btn-navy btn-sm">
                <i class="bi bi-download me-1"></i>Download Ready-to-Use CSV Template
            </button>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($importSummary)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="text-navy fw-bold mb-3"><i class="bi bi-info-circle-fill me-1 text-primary"></i>Import Summary</h5>
            @if(!empty($importSummary['detectedHeaders']))
            <p class="small text-muted mb-3"><strong>Detected File Headers:</strong> {{ $importSummary['detectedHeaders'] }}</p>
            @endif
            <div class="row g-2 mb-3 text-center">
                <div class="col-6 col-md-3">
                    <div class="p-3 border rounded bg-success-subtle text-success">
                        <h3 class="mb-0 fw-bold">{{ $importSummary['successful'] }}</h3>
                        <span class="small">Imported</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 border rounded bg-warning-subtle text-warning">
                        <h3 class="mb-0 fw-bold">{{ $importSummary['skipped'] }}</h3>
                        <span class="small">Skipped (Duplicate)</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 border rounded bg-danger-subtle text-danger">
                        <h3 class="mb-0 fw-bold">{{ $importSummary['failed'] }}</h3>
                        <span class="small">Failed Rows</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 border rounded bg-primary-subtle text-primary">
                        <h3 class="mb-0 fw-bold">{{ $importSummary['emailsSent'] }}</h3>
                        <span class="small">Emails Sent ({{ $importSummary['emailsFailed'] }} failed)</span>
                    </div>
                </div>
            </div>

            @if(isset($importSummary['errors']) && count($importSummary['errors']) > 0)
            <div class="alert alert-warning mb-0" style="font-size:12px;">
                <h6 class="alert-heading fw-bold">Import Notes / Warnings:</h6>
                <ul class="mb-0 ps-3">
                    @foreach($importSummary['errors'] as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold text-navy">Upload CSV Roster</div>
                <div class="card-body">
                    <form wire:submit.prevent="import">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select CSV File <span class="text-danger">*</span></label>
                            <input type="file" wire:model="csvFile" accept=".csv,.txt" class="form-control @error('csvFile') is-invalid @enderror">
                            @error('csvFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small text-muted">Save your Excel spreadsheet as <strong>CSV UTF-8 (Comma delimited) (*.csv)</strong> before uploading (max 10 MB).</div>
                        </div>

                        <div class="form-check p-3 rounded bg-light border mb-4">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="sendWelcomeEmails" wire:model="sendWelcomeEmails">
                            <label class="form-check-label small" for="sendWelcomeEmails">
                                <strong>Send Welcome Login Emails Immediately</strong><br>
                                <span class="text-muted">Only check this after configuring your Hostinger SMTP or verified domain email in <code>.env</code>. Leave unchecked during initial dry-run imports.</span>
                            </label>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-navy px-4" wire:loading.attr="disabled">
                                <span wire:loading.remove><i class="bi bi-cloud-arrow-up me-1"></i>Start Bulk Import</span>
                                <span wire:loading><span class="spinner-border spinner-border-sm me-1" role="status"></span>Importing Records...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold text-navy">Supported CSV Columns</div>
                <div class="card-body small">
                    <p class="text-muted mb-2"><strong>Required:</strong> <code>First Name</code> + <code>Last Name</code> (or <code>Full Name</code>). All other columns are auto-mapped if present:</p>
                    <ul class="mb-2 ps-3 text-muted">
                        <li><strong>Identity:</strong> <code>Employee ID</code> (auto-generated if blank), <code>Personnel Number</code>, <code>Gender</code>, <code>Date of Birth</code> (YYYY-MM-DD)</li>
                        <li><strong>Contact:</strong> <code>Official Email</code> (auto-generated if blank), <code>Phone</code></li>
                        <li><strong>Placement:</strong> <code>Department</code> (matches name or code, e.g. <code>HR</code>, <code>FIN</code>, <code>STADIUM</code>), <code>Position</code>, <code>Employment Type</code> (<code>Permanent</code>, <code>Contract</code>, <code>Casual</code>, <code>Intern</code>)</li>
                        <li><strong>Dates:</strong> <code>Date Joined</code>, <code>Contract End Date</code> (for Contractors — auto-assigns Contractor role!)</li>
                        <li><strong>Payroll &amp; Compliance:</strong> <code>Salary LRD</code>, <code>Salary USD</code>, <code>Bank Name</code>, <code>Bank Account</code>, <code>TIN Number</code>, <code>Social Security No</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
