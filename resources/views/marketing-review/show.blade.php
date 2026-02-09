@extends('layouts.app')
@section('title', 'Marketing Review - ' . $batch->batch_number)
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('marketing-review.index') }}">Marketing Review</a></li><li class="breadcrumb-item active">{{ $batch->batch_number }}</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $batch->batch_number }}</h4>
        <small class="text-muted">{{ $batch->month_label }} | Approved by {{ $batch->uwApprover?->name ?? '-' }} on {{ $batch->uw_approved_at?->format('d/m/Y H:i') }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('marketing-review.download', $batch) }}" class="btn btn-outline-dark"><i class="bi bi-download me-2"></i>Download Excel</a>
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
            <div class="h5 mb-0">{{ $batch->uw_approved_count - $batch->marketing_approved_count - $batch->sent_count - $batch->success_count - $batch->failed_count }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Mkt Approved</div>
            <div class="h5 mb-0">{{ $batch->marketing_approved_count }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2 bg-dark text-white">
            <div class="small opacity-75">Success</div>
            <div class="h5 mb-0">{{ $batch->success_count }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Failed</div>
            <div class="h5 mb-0">{{ $batch->failed_count }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        @if($batch->status === 'pending_marketing')
        <div class="card stat-card h-100">
            <div class="card-body d-flex flex-column gap-2 align-items-center justify-content-center p-2">
                <form action="{{ route('marketing-review.approve-batch', $batch) }}" method="POST" onsubmit="return confirm('Approve and send to partner?')">
                    @csrf
                    <button class="btn btn-sm btn-dark"><i class="bi bi-send me-1"></i>Approve & Send</button>
                </form>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#returnModal">Return to UW</button>
            </div>
        </div>
        @else
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center justify-content-center">
                <span class="badge bg-dark">{{ ucfirst(str_replace('_', ' ', $batch->status)) }}</span>
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
                    <option value="pending_marketing" {{ request('workflow_status') == 'pending_marketing' ? 'selected' : '' }}>Pending Marketing</option>
                    <option value="marketing_approved" {{ request('workflow_status') == 'marketing_approved' ? 'selected' : '' }}>Mkt Approved</option>
                    <option value="sent" {{ request('workflow_status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="success" {{ request('workflow_status') == 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('workflow_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-dark">Search</button>
                <a href="{{ route('marketing-review.show', $batch) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card stat-card">
    <div class="card-body p-0">
        @if($records->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Contract No</th>
                        <th>Policy No</th>
                        <th>Name</th>
                        <th>Vehicle</th>
                        <th>Coverage</th>
                        <th>Premium</th>
                        <th>Status</th>
                        <th>Request ID</th>
                        <th>Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $record)
                    <tr>
                        <td><code><strong>{{ $record->no_kontrak }}</strong></code></td>
                        <td><small class="text-muted">{{ $record->no_polis }}</small></td>
                        <td><small>{{ Str::limit($record->nama_tertanggung, 20) }}</small></td>
                        <td><small>{{ $record->ano }}</small></td>
                        <td><small>{{ $record->jenis_coverage }}</small></td>
                        <td><small>{{ number_format($record->premi, 0) }}</small></td>
                        <td>
                            <span class="badge bg-{{ $record->workflow_status_color }}">{{ $record->workflow_status_label }}</span>
                            @if($record->workflow_status === 'failed')
                            <br><small class="text-muted" title="{{ $record->partner_error_message }}">{{ Str::limit($record->partner_error_message, 20) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($record->partner_request_id)
                            <code class="small">{{ $record->partner_request_id }}</code>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><small>{{ $record->partner_sent_at?->format('d/m H:i') ?? '-' }}</small></td>
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

<!-- Return to UW Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('marketing-review.return-to-uw', $batch) }}" method="POST">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Return to Underwriter</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">Reason for returning</label>
                    <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark">Return to UW</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
