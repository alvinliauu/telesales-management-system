@extends('layouts.app')
@section('title', 'UW Review')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">UW Review</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">UW Review</h4>
</div>

<!-- Filters -->
<div class="card stat-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Months</option>
                    @foreach($months as $month)
                    <option value="{{ $month['value'] }}" {{ request('month') == $month['value'] ? 'selected' : '' }}>{{ $month['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending_uw" {{ request('status') == 'pending_uw' ? 'selected' : '' }}>Pending UW</option>
                    <option value="uw_approved" {{ request('status') == 'uw_approved' ? 'selected' : '' }}>UW Approved</option>
                    <option value="uw_rejected" {{ request('status') == 'uw_rejected' ? 'selected' : '' }}>UW Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('uw-review.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card stat-card">
    <div class="card-body p-0">
        @if($batches->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Batch</th>
                        <th>Month</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Pending</th>
                        <th class="text-center">Approved</th>
                        <th class="text-center">Rejected</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                    <tr>
                        <td><strong>{{ $batch->batch_number }}</strong></td>
                        <td>{{ $batch->month_label }}</td>
                        <td class="text-center">{{ $batch->total_records }}</td>
                        <td class="text-center"><span class="badge bg-secondary">{{ $batch->total_records - $batch->uw_approved_count - $batch->uw_rejected_count }}</span></td>
                        <td class="text-center"><span class="badge bg-dark">{{ $batch->uw_approved_count }}</span></td>
                        <td class="text-center"><span class="badge bg-light text-dark border">{{ $batch->uw_rejected_count }}</span></td>
                        <td><span class="badge bg-{{ $batch->status == 'pending_uw' ? 'secondary' : 'dark' }}">{{ ucfirst(str_replace('_', ' ', $batch->status)) }}</span></td>
                        <td><small>{{ $batch->created_at->format('d/m/Y') }}</small></td>
                        <td class="text-center">
                            <a href="{{ route('uw-review.show', $batch) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye me-1"></i>Review</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent">{{ $batches->links() }}</div>
        @else
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox" style="font-size:3rem"></i>
            <h6 class="mt-3">No batches for review</h6>
        </div>
        @endif
    </div>
</div>
@endsection
