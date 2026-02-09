@extends('layouts.app')
@section('title', 'Marketing Review')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Marketing Review</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Marketing Review</h4>
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
                    <option value="pending_marketing" {{ request('status') == 'pending_marketing' ? 'selected' : '' }}>Pending Marketing</option>
                    <option value="marketing_approved" {{ request('status') == 'marketing_approved' ? 'selected' : '' }}>Marketing Approved</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('marketing-review.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th class="text-center">Success</th>
                        <th class="text-center">Failed</th>
                        <th>Status</th>
                        <th>UW Approved By</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                    <tr>
                        <td><strong>{{ $batch->batch_number }}</strong></td>
                        <td>{{ $batch->month_label }}</td>
                        <td class="text-center">{{ $batch->total_records }}</td>
                        <td class="text-center"><span class="badge bg-secondary">{{ $batch->marketing_approved_count > 0 ? 0 : $batch->uw_approved_count }}</span></td>
                        <td class="text-center"><span class="badge bg-dark">{{ $batch->marketing_approved_count }}</span></td>
                        <td class="text-center"><span class="badge bg-dark">{{ $batch->success_count }}</span></td>
                        <td class="text-center"><span class="badge bg-light text-dark border">{{ $batch->failed_count }}</span></td>
                        <td><span class="badge bg-{{ $batch->status == 'pending_marketing' ? 'secondary' : 'dark' }}">{{ ucfirst(str_replace('_', ' ', $batch->status)) }}</span></td>
                        <td><small>{{ $batch->uwApprover?->name ?? '-' }}</small></td>
                        <td class="text-center">
                            <a href="{{ route('marketing-review.show', $batch) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye me-1"></i>Review</a>
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
            <h6 class="mt-3">No batches for Marketing review</h6>
            <p class="small">Batches will appear here after UW approval.</p>
        </div>
        @endif
    </div>
</div>
@endsection
