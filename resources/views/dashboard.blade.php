@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item active">Dashboard</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Dashboard</h4>
    <div class="d-flex align-items-center gap-3">
        <form method="GET" class="d-flex gap-2">
            <select name="month" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 180px;">
                @foreach($availableMonths as $month)
                <option value="{{ $month['value'] }}" {{ $selectedMonth == $month['value'] ? 'selected' : '' }}>
                    {{ $month['label'] }}
                </option>
                @endforeach
            </select>
        </form>
        <span class="badge bg-{{ $sendingAllowed ? 'dark' : 'secondary' }}">
            <i class="bi bi-{{ $sendingAllowed ? 'check-circle' : 'pause-circle' }} me-1"></i>
            Scheduler: {{ $sendingAllowed ? 'Active' : 'Paused' }}
        </span>
    </div>
</div>

<!-- Workflow Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Total</div>
            <div class="h5 mb-0">{{ number_format($monthlyStats['total']) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Pending UW</div>
            <div class="h5 mb-0">{{ number_format($monthlyStats['pending_uw']) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">UW Approved</div>
            <div class="h5 mb-0">{{ number_format($monthlyStats['uw_approved']) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Pending Mkt</div>
            <div class="h5 mb-0">{{ number_format($monthlyStats['pending_marketing']) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Mkt Approved</div>
            <div class="h5 mb-0">{{ number_format($monthlyStats['marketing_approved']) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card stat-card text-center py-2 bg-dark text-white">
            <div class="small opacity-75">Success</div>
            <div class="h5 mb-0">{{ number_format($monthlyStats['success']) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Failed</div>
            <div class="h5 mb-0">{{ number_format($monthlyStats['failed']) }}</div>
        </div>
    </div>
</div>

<!-- Export Buttons -->
<div class="card stat-card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1"><i class="bi bi-download me-2"></i>Export Partner Results</h6>
                <small class="text-muted">Download success/failed data for {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('dashboard.export', ['month' => $selectedMonth, 'type' => 'all']) }}" class="btn btn-sm btn-outline-dark">
                    <i class="bi bi-file-earmark-excel me-1"></i>All
                </a>
                <a href="{{ route('dashboard.export', ['month' => $selectedMonth, 'type' => 'success']) }}" class="btn btn-sm btn-dark">
                    <i class="bi bi-check-circle me-1"></i>Success Only
                </a>
                <a href="{{ route('dashboard.export', ['month' => $selectedMonth, 'type' => 'failed']) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Failed Only
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Batches -->
    <div class="col-lg-6">
        <div class="card stat-card h-100">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-collection me-2"></i>Batches - {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</h6>
            </div>
            <div class="card-body p-0">
                @if($batches->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Batch</th>
                                <th>Status</th>
                                <th class="text-center">Records</th>
                                <th class="text-center">Success</th>
                                <th class="text-center">Failed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                            <tr>
                                <td>
                                    <small><strong>{{ $batch->batch_number }}</strong></small>
                                    <br><small class="text-muted">{{ $batch->created_at->format('d/m H:i') }}</small>
                                </td>
                                <td><span class="badge bg-{{ $batch->status == 'completed' ? 'dark' : 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $batch->status)) }}</span></td>
                                <td class="text-center">{{ $batch->total_records }}</td>
                                <td class="text-center"><span class="badge bg-dark">{{ $batch->success_count }}</span></td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $batch->failed_count }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox" style="font-size:2rem"></i>
                    <p class="mb-0 mt-2">No batches for this month</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Failed Breakdown -->
    <div class="col-lg-6">
        <div class="card stat-card h-100">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Failed Breakdown</h6>
            </div>
            <div class="card-body p-0">
                @if($failedByError->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Error Code</th>
                                <th>Message</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($failedByError as $error)
                            <tr>
                                <td><code>{{ $error->partner_error_code }}</code></td>
                                <td><small>{{ Str::limit($error->partner_error_message, 35) }}</small></td>
                                <td class="text-end"><span class="badge bg-secondary">{{ $error->total }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check-circle" style="font-size:2rem"></i>
                    <p class="mb-0 mt-2">No failed submissions</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Success -->
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-check2-circle me-2"></i>Recent Success</h6>
            </div>
            <div class="card-body p-0">
                @if($recentSuccess->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Contract</th>
                                <th>Name</th>
                                <th>Request ID</th>
                                <th>Sent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSuccess as $item)
                            <tr>
                                <td><code class="small">{{ $item->no_kontrak }}</code></td>
                                <td><small>{{ Str::limit($item->nama_tertanggung, 18) }}</small></td>
                                <td><code class="small text-dark">{{ $item->partner_request_id }}</code></td>
                                <td><small class="text-muted">{{ $item->partner_sent_at?->format('d/m H:i') }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted"><p class="mb-0">No data</p></div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Failed -->
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-x-circle me-2"></i>Recent Failed</h6>
            </div>
            <div class="card-body p-0">
                @if($recentFailed->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Contract</th>
                                <th>Name</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentFailed as $item)
                            <tr>
                                <td><code class="small">{{ $item->no_kontrak }}</code></td>
                                <td><small>{{ Str::limit($item->nama_tertanggung, 15) }}</small></td>
                                <td><small class="text-muted">{{ Str::limit($item->partner_error_message, 25) }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted"><p class="mb-0">No data</p></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
