@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item active">Dashboard</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Dashboard</h4>
    <span class="text-muted">{{ now()->format('l, d F Y') }}</span>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3"><i class="bi bi-calendar-event"></i></div>
                    <div><h3 class="mb-0">{{ $stats['active_events'] }}</h3><small class="text-muted">Active Events</small></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3"><i class="bi bi-hourglass-split"></i></div>
                    <div><h3 class="mb-0">{{ number_format($stats['pending_calls']) }}</h3><small class="text-muted">Pending Calls</small></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3"><i class="bi bi-check-circle"></i></div>
                    <div><h3 class="mb-0">{{ $stats['renewed_today'] }}</h3><small class="text-muted">Renewed Today</small></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3"><i class="bi bi-telephone"></i></div>
                    <div><h3 class="mb-0">{{ $stats['calls_today'] }}</h3><small class="text-muted">Calls Today</small></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card stat-card">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Active Events</h6>
                <a href="{{ route('settings.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($activeEvents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light"><tr><th>Event Name</th><th>Period</th><th>Total</th><th>Progress</th><th></th></tr></thead>
                        <tbody>
                            @foreach($activeEvents as $event)
                            @php $progress = $event->total_data > 0 ? round(($event->renewed_count / $event->total_data) * 100) : 0; @endphp
                            <tr>
                                <td><strong>{{ $event->name }}</strong><br><small class="text-muted">{{ ucfirst($event->event_type) }}</small></td>
                                <td>{{ $event->period }}</td>
                                <td>{{ number_format($event->total_data) }}</td>
                                <td style="min-width:150px"><div class="progress" style="height:8px"><div class="progress-bar bg-success" style="width:{{ $progress }}%"></div></div><small class="text-muted">{{ $event->renewed_count }}/{{ $event->total_data }} ({{ $progress }}%)</small></td>
                                <td><a href="{{ route('telesales.renewal.index', ['event_id' => $event->id]) }}" class="btn btn-sm btn-primary"><i class="bi bi-arrow-right"></i></a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5"><i class="bi bi-calendar-x text-muted" style="font-size:3rem"></i><p class="text-muted mt-3">No active events</p><a href="{{ route('settings.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Create Event</a></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card stat-card">
            <div class="card-header bg-transparent"><h6 class="mb-0"><i class="bi bi-telephone-forward me-2"></i>Callbacks Due Today</h6></div>
            <div class="card-body p-0">
                @if($callbacksToday->count() > 0)
                <ul class="list-group list-group-flush">
                    @foreach($callbacksToday as $cb)
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div><strong class="d-block">{{ $cb->nama_tertanggung }}</strong><small class="text-muted">{{ $cb->no_polis }}</small></div>
                        <a href="{{ route('telesales.renewal.show', $cb) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-telephone"></i></a>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="text-center py-4"><i class="bi bi-check-all text-success" style="font-size:2rem"></i><p class="text-muted mb-0 mt-2">No callbacks due today</p></div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!empty($statusBreakdown))
<div class="row mt-4">
    <div class="col-12">
        <div class="card stat-card">
            <div class="card-header bg-transparent"><h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Call Status Overview</h6></div>
            <div class="card-body">
                <div class="row">
                    @php $colors = ['pending'=>'secondary','called'=>'info','no_answer'=>'warning','callback'=>'primary','interested'=>'info','renewed'=>'success','declined'=>'danger','invalid_contact'=>'dark']; @endphp
                    @foreach($statusBreakdown as $status => $count)
                    <div class="col-6 col-md-3 col-lg-2 mb-3">
                        <div class="text-center p-3 rounded bg-{{ $colors[$status] ?? 'secondary' }} bg-opacity-10">
                            <h4 class="mb-1 text-{{ $colors[$status] ?? 'secondary' }}">{{ number_format($count) }}</h4>
                            <small class="text-muted">{{ ucfirst(str_replace('_',' ',$status)) }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
