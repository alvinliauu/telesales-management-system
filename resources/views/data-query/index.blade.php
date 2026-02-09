@extends('layouts.app')
@section('title', 'Data Query')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Data Query</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Data Query</h4>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card stat-card">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-database me-2"></i>Query Data by Month</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('data-query.query') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select Month</label>
                        <select name="month" class="form-select" required>
                            <option value="">-- Select Month --</option>
                            @foreach($availableMonths as $month)
                            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="bi bi-play-fill me-2"></i>Query Data
                    </button>
                </form>
                <div class="mt-3 small text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    This will fetch data from internal database for the selected month.
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card stat-card">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-collection me-2"></i>Query History</h6>
            </div>
            <div class="card-body p-0">
                @if($batches->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Batch Number</th>
                                <th>Month</th>
                                <th>Records</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                            <tr>
                                <td><strong>{{ $batch->batch_number }}</strong></td>
                                <td>{{ $batch->month_label }}</td>
                                <td><span class="badge bg-secondary">{{ number_format($batch->total_records) }}</span></td>
                                <td>
                                    @php
                                        $statusColor = match($batch->status) {
                                            'pending_uw' => 'secondary',
                                            'uw_approved', 'pending_marketing' => 'dark',
                                            'uw_rejected' => 'danger',
                                            'marketing_approved', 'processing', 'completed' => 'dark',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">{{ ucfirst(str_replace('_', ' ', $batch->status)) }}</span>
                                </td>
                                <td><small>{{ $batch->creator?->name ?? '-' }}</small></td>
                                <td><small>{{ $batch->created_at->format('d/m/Y H:i') }}</small></td>
                                <td class="text-center">
                                    <a href="{{ route('data-query.show', $batch) }}" class="btn btn-sm btn-outline-dark"><i class="bi bi-eye"></i></a>
                                    @if($batch->status === 'pending_uw')
                                    <form action="{{ route('data-query.destroy', $batch) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this batch?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-trash"></i></button>
                                    </form>
                                    @endif
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
                    <h6 class="mt-3">No batches yet</h6>
                    <p class="small">Query data from internal database to create a new batch.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
