@extends('layouts.app')
@section('title', 'UW Review - ' . $batch->batch_number)
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('uw-review.index') }}">UW Review</a></li><li class="breadcrumb-item active">{{ $batch->batch_number }}</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $batch->batch_number }}</h4>
        <small class="text-muted">{{ $batch->month_label }} | {{ $batch->total_records }} records</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('uw-review.download', $batch) }}" class="btn btn-outline-dark"><i class="bi bi-download me-2"></i>Download Excel</a>
        @if($batch->status === 'pending_uw')
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-upload me-2"></i>Reupload</button>
        @endif
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Total</div>
            <div class="h5 mb-0">{{ $batch->total_records }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Pending</div>
            <div class="h5 mb-0">{{ $batch->total_records - $batch->uw_approved_count - $batch->uw_rejected_count }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2 bg-dark text-white">
            <div class="small opacity-75">Approved</div>
            <div class="h5 mb-0">{{ $batch->uw_approved_count }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Rejected</div>
            <div class="h5 mb-0">{{ $batch->uw_rejected_count }}</div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        @if($batch->status === 'pending_uw')
        <div class="card stat-card h-100">
            <div class="card-body d-flex gap-2 align-items-center justify-content-center">
                @if($batch->total_records - $batch->uw_approved_count - $batch->uw_rejected_count == 0)
                <form action="{{ route('uw-review.approve-batch', $batch) }}" method="POST" onsubmit="return confirm('Approve this batch and send to Marketing?')">
                    @csrf
                    <button class="btn btn-dark"><i class="bi bi-check-lg me-2"></i>Approve & Send to Marketing</button>
                </form>
                @else
                <span class="text-muted small">Review all records before approving batch</span>
                @endif
                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#rejectBatchModal"><i class="bi bi-x-lg me-1"></i>Reject Batch</button>
            </div>
        </div>
        @else
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center justify-content-center">
                <span class="badge bg-dark fs-6">{{ ucfirst(str_replace('_', ' ', $batch->status)) }}</span>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Filters -->
<div class="card stat-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search contract, policy, name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="workflow_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending_uw" {{ request('workflow_status') == 'pending_uw' ? 'selected' : '' }}>Pending UW</option>
                    <option value="uw_approved" {{ request('workflow_status') == 'uw_approved' ? 'selected' : '' }}>Approved</option>
                    <option value="uw_rejected" {{ request('workflow_status') == 'uw_rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-dark">Search</button>
                <a href="{{ route('uw-review.show', $batch) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
@if($batch->status === 'pending_uw')
<div class="card stat-card mb-4" id="bulkActions" style="display:none;">
    <div class="card-body d-flex justify-content-between align-items-center">
        <span><strong id="selectedCount">0</strong> records selected</span>
        <div class="d-flex gap-2">
            <form action="{{ route('uw-review.bulk-approve') }}" method="POST" id="bulkApproveForm">
                @csrf
                <input type="hidden" name="record_ids" id="approveIds">
                <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-check-lg me-1"></i>Approve Selected</button>
            </form>
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#bulkRejectModal"><i class="bi bi-x-lg me-1"></i>Reject Selected</button>
        </div>
    </div>
</div>
@endif

<!-- Data Table -->
<div class="card stat-card">
    <div class="card-body p-0">
        @if($records->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        @if($batch->status === 'pending_uw')
                        <th width="40"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        @endif
                        <th>Contract No</th>
                        <th>Policy No</th>
                        <th>Name</th>
                        <th>Vehicle</th>
                        <th>Coverage</th>
                        <th>Premium</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                    <tr>
                        @if($batch->status === 'pending_uw')
                        <td><input type="checkbox" class="form-check-input record-checkbox" value="{{ $record->id }}" {{ $record->workflow_status !== 'pending_uw' ? 'disabled' : '' }}></td>
                        @endif
                        <td><code><strong>{{ $record->no_kontrak }}</strong></code></td>
                        <td><small class="text-muted">{{ $record->no_polis }}</small></td>
                        <td><small>{{ Str::limit($record->nama_tertanggung, 20) }}</small></td>
                        <td><small>{{ $record->ano }}</small></td>
                        <td><small>{{ $record->jenis_coverage }}</small></td>
                        <td><small>{{ number_format($record->premi, 0) }}</small></td>
                        <td><span class="badge bg-{{ $record->workflow_status_color }}">{{ $record->workflow_status_label }}</span></td>
                        <td class="text-center">
                            @if($record->workflow_status === 'pending_uw' && $batch->status === 'pending_uw')
                            <div class="btn-group btn-group-sm">
                                <form action="{{ route('uw-review.approve-record', $record) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-outline-dark" title="Approve"><i class="bi bi-check-lg"></i></button>
                                </form>
                                <button class="btn btn-outline-secondary" title="Reject" onclick="openRejectModal({{ $record->id }})"><i class="bi bi-x-lg"></i></button>
                            </div>
                            @else
                            @if($record->uw_rejection_reason)
                            <small class="text-muted" title="{{ $record->uw_rejection_reason }}">{{ Str::limit($record->uw_rejection_reason, 15) }}</small>
                            @else
                            -
                            @endif
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent">{{ $records->withQueryString()->links() }}</div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox" style="font-size:3rem"></i>
            <p class="mb-0 mt-3">No records found</p>
        </div>
        @endif
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('uw-review.upload', $batch) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Reupload Data</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p class="text-muted small">Upload revised Excel file. This will replace all existing data in this batch.</p>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Batch Modal -->
<div class="modal fade" id="rejectBatchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('uw-review.reject-batch', $batch) }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Reject Batch</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">Rejection Reason</label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Batch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Record Modal -->
<div class="modal fade" id="rejectRecordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectRecordForm" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Reject Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">Rejection Reason</label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div class="modal fade" id="bulkRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('uw-review.bulk-reject') }}" method="POST" id="bulkRejectForm">
                @csrf
                <input type="hidden" name="record_ids" id="rejectIds">
                <div class="modal-header"><h5 class="modal-title">Reject Selected Records</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">Rejection Reason (applies to all selected)</label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRejectModal(recordId) {
    document.getElementById('rejectRecordForm').action = '/uw-review/record/' + recordId + '/reject';
    new bootstrap.Modal(document.getElementById('rejectRecordModal')).show();
}

// Bulk selection
const checkboxes = document.querySelectorAll('.record-checkbox:not(:disabled)');
const selectAll = document.getElementById('selectAll');
const bulkActions = document.getElementById('bulkActions');
const selectedCount = document.getElementById('selectedCount');

function updateBulkActions() {
    const selected = document.querySelectorAll('.record-checkbox:checked');
    const count = selected.length;
    selectedCount.textContent = count;
    bulkActions.style.display = count > 0 ? 'block' : 'none';
    
    const ids = Array.from(selected).map(cb => cb.value);
    document.getElementById('approveIds').value = JSON.stringify(ids);
    document.getElementById('rejectIds').value = JSON.stringify(ids);
}

if (selectAll) {
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkActions();
    });
}

checkboxes.forEach(cb => cb.addEventListener('change', updateBulkActions));

// Fix form submission to send array
document.getElementById('bulkApproveForm')?.addEventListener('submit', function(e) {
    const ids = JSON.parse(document.getElementById('approveIds').value || '[]');
    document.getElementById('approveIds').remove();
    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'record_ids[]';
        input.value = id;
        this.appendChild(input);
    });
});

document.getElementById('bulkRejectForm')?.addEventListener('submit', function(e) {
    const ids = JSON.parse(document.getElementById('rejectIds').value || '[]');
    document.getElementById('rejectIds').remove();
    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'record_ids[]';
        input.value = id;
        this.appendChild(input);
    });
});
</script>
@endpush
