<div>
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="text-navy"><i class="bi bi-file-earmark-excel me-1"></i>Bulk Employee Migration</h4>
            <p class="text-muted">Import existing employees into the HRMIS in bulk using a template file.</p>
            
            <button type="button" wire:click="downloadTemplate" class="btn btn-outline-navy btn-sm">
                <i class="bi bi-download me-1"></i>Download CSV Template
            </button>
        </div>
    </div>

    @if($importSummary)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="text-navy mb-3"><i class="bi bi-info-circle-fill me-1"></i>Import Summary</h5>
            @if(!empty($importSummary['detectedHeaders']))
            <p class="small text-muted mb-3"><strong>Detected File Headers:</strong> {{ $importSummary['detectedHeaders'] }}</p>
            @endif
            <div class="row g-2 mb-3 text-center">
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-success-subtle text-success">
                        <h2 class="mb-0 fw-bold">{{ $importSummary['successful'] }}</h2>
                        <span class="small">Successful</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-warning-subtle text-warning">
                        <h2 class="mb-0 fw-bold">{{ $importSummary['skipped'] }}</h2>
                        <span class="small">Skipped (Duplicate Email)</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-danger-subtle text-danger">
                        <h2 class="mb-0 fw-bold">{{ $importSummary['failed'] }}</h2>
                        <span class="small">Failed Rows</span>
                    </div>
                </div>
            </div>

            @if(isset($importSummary['errors']) && count($importSummary['errors']) > 0)
            <div class="alert alert-danger" style="font-size:12px;">
                <h6 class="alert-heading fw-bold">Import Warnings/Errors:</h6>
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

    <div class="card shadow-sm">
        <div class="card-body">
            <form wire:submit.prevent="import">
                <div class="mb-4">
                    <label class="form-label fw-bold">Select CSV File</label>
                    <input type="file" wire:model="csvFile" class="form-control @error('csvFile') is-invalid @enderror">
                    @error('csvFile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text small text-muted">Make sure the column headers match the downloaded template exactly.</div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-navy" wire:loading.attr="disabled">
                        <span wire:loading.remove>Start Migration</span>
                        <span wire:loading><span class="spinner-border spinner-border-sm me-1" role="status"></span>Processing...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
