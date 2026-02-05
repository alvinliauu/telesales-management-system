@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item active">Dashboard</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Dashboard</h4>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-{{ $sendingAllowed ? 'dark' : 'secondary' }}">
            <i class="bi bi-{{ $sendingAllowed ? 'check-circle' : 'pause-circle' }} me-1"></i>
            Scheduler: {{ $sendingAllowed ? 'Active' : 'Paused' }}
        </span>
    </div>
</div>

<!-- Partner Submission Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small text-uppercase">Pending</div>
                        <div class="h3 mb-0 mt-1">{{ number_format($partnerStats['pending']) }}</div>
                        <small class="text-muted">Waiting to send</small>
                    </div>
                    <div class="stat-icon bg-light text-secondary">
                        <i class="bi bi-clock"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small text-uppercase">Queued</div>
                        <div class="h3 mb-0 mt-1">{{ number_format($partnerStats['queued']) }}</div>
                        <small class="text-muted">In progress</small>
                    </div>
                    <div class="stat-icon bg-light text-secondary">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small text-uppercase">Success</div>
                        <div class="h3 mb-0 mt-1">{{ number_format($partnerStats['success']) }}</div>
                        <small class="text-muted">Sent to partner</small>
                    </div>
                    <div class="stat-icon bg-light text-dark">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small text-uppercase">Failed</div>
                        <div class="h3 mb-0 mt-1">{{ number_format($partnerStats['failed']) }}</div>
                        <small class="text-muted">Need attention</small>
                    </div>
                    <div class="stat-icon bg-light text-secondary">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Failed Submissions Breakdown -->
    <div class="col-lg-6">
        <div class="card stat-card h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Failed Submissions</h6>
                <span class="badge bg-secondary">{{ $failedByError->sum('total') }} total</span>
            </div>
            <div class="card-body p-0">
                @if($failedByError->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Error</th>
                                <th>Message</th>
                                <th class="text-end">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($failedByError as $error)
                            <tr>
                                <td><code class="text-muted">{{ $error['error_code'] }}</code></td>
                                <td><small>{{ Str::limit($error['error_message'], 40) }}</small></td>
                                <td class="text-end"><span class="badge bg-secondary">{{ $error['total'] }}</span></td>
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

    <!-- Event Progress -->
    <div class="col-lg-6">
        <div class="card stat-card h-100">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-calendar-event me-2"></i>Active Events</h6>
            </div>
            <div class="card-body p-0">
                @if($eventStats->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Event</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Pending</th>
                                <th class="text-center">Success</th>
                                <th class="text-center">Failed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($eventStats as $event)
                            <tr>
                                <td>{{ $event->name }}</td>
                                <td class="text-center">{{ number_format($event->total_data) }}</td>
                                <td class="text-center"><span class="badge bg-light text-dark">{{ $event->pending_count }}</span></td>
                                <td class="text-center"><span class="badge bg-dark">{{ $event->success_count }}</span></td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $event->failed_count }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-x" style="font-size:2rem"></i>
                    <p class="mb-0 mt-2">No active events</p>
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
                                <th>Policy</th>
                                <th>Name</th>
                                <th>Request ID</th>
                                <th>Sent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSuccess as $item)
                            <tr>
                                <td><small>{{ $item->no_polis }}</small></td>
                                <td><small>{{ Str::limit($item->nama_tertanggung, 20) }}</small></td>
                                <td><code class="text-dark">{{ $item->partner_request_id }}</code></td>
                                <td><small class="text-muted">{{ $item->partner_sent_at?->diffForHumans() }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <p class="mb-0">No recent submissions</p>
                </div>
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
                                <th>Policy</th>
                                <th>Name</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentFailed as $item)
                            <tr>
                                <td><small>{{ $item->no_polis }}</small></td>
                                <td><small>{{ Str::limit($item->nama_tertanggung, 15) }}</small></td>
                                <td><small class="text-muted">{{ Str::limit($item->partner_error_message, 30) }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <p class="mb-0">No failed submissions</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Scheduler Info -->
<div class="card stat-card mt-4">
    <div class="card-body">
        <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Scheduler Rules</h6>
        <div class="row">
            <div class="col-md-6">
                <ul class="mb-0 small text-muted">
                    <li>Runs every 15 minutes (max 50 records per batch)</li>
                    <li>Paused on first 2 days of each month</li>
                    <li>Paused on last 2 days of each month</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="mb-0 small text-muted">
                    <li>Paused between 11:00 PM - 7:00 AM</li>
                    <li>Failed records retry up to 3 times</li>
                    <li>Duplicate policy errors won't retry</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
