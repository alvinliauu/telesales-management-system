@extends('layouts.app')
@section('title', 'Renewal')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">Telesales</li><li class="breadcrumb-item active">Renewal</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Renewal Data</h4>
    <a href="{{ route('telesales.renewal.upload') }}" class="btn btn-dark"><i class="bi bi-upload me-2"></i>Upload Data</a>
</div>

<!-- Filters -->
<div class="card stat-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small">Event</label>
                <select name="event_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Events</option>
                    @foreach($events as $event)
                    <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                        {{ $event->name }} ({{ $event->period }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Partner Status</label>
                <select name="partner_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('partner_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="queued" {{ request('partner_status') == 'queued' ? 'selected' : '' }}>Queued</option>
                    <option value="success" {{ request('partner_status') == 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('partner_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, Policy, Contract, Plate..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-dark"><i class="bi bi-search me-1"></i>Search</button>
                <a href="{{ route('telesales.renewal.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Statistics -->
@if($statistics)
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Total</div>
            <div class="h5 mb-0">{{ number_format($statistics['total']) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Pending</div>
            <div class="h5 mb-0">{{ number_format($statistics['pending']) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Success</div>
            <div class="h5 mb-0">{{ number_format($statistics['success']) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Failed</div>
            <div class="h5 mb-0">{{ number_format($statistics['failed']) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Queued</div>
            <div class="h5 mb-0">{{ number_format($statistics['queued']) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="card stat-card text-center py-2">
            <div class="small text-muted">Success Rate</div>
            <div class="h5 mb-0">{{ $statistics['total'] > 0 ? round(($statistics['success'] / $statistics['total']) * 100, 1) : 0 }}%</div>
        </div>
    </div>
</div>
@endif

<!-- Data Table -->
<div class="card stat-card">
    <div class="card-body p-0">
        @if($renewals->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Policy</th>
                        <th>Insured Name</th>
                        <th>Vehicle</th>
                        <th>Coverage</th>
                        <th>End Date</th>
                        <th>Partner Status</th>
                        <th>Request ID</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($renewals as $renewal)
                    <tr>
                        <td>
                            <small class="d-block"><strong>{{ $renewal->no_polis }}</strong></small>
                            <small class="text-muted">{{ $renewal->no_kontrak }}</small>
                        </td>
                        <td>
                            <small class="d-block">{{ Str::limit($renewal->nama_tertanggung, 25) }}</small>
                            <small class="text-muted">{{ $renewal->no_hp }}</small>
                        </td>
                        <td>
                            <small class="d-block">{{ $renewal->ano }}</small>
                            <small class="text-muted">{{ $renewal->merk_kendaraan }} {{ $renewal->tahun_kendaraan }}</small>
                        </td>
                        <td><small>{{ $renewal->jenis_coverage }}</small></td>
                        <td>
                            <small>{{ $renewal->end_date?->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            @php
                                $statusClass = match($renewal->partner_status) {
                                    'success' => 'bg-dark',
                                    'failed' => 'bg-secondary',
                                    'queued' => 'bg-light text-dark border',
                                    default => 'bg-light text-muted border'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($renewal->partner_status) }}</span>
                            @if($renewal->partner_status === 'failed')
                            <br><small class="text-muted" title="{{ $renewal->partner_error_message }}">{{ Str::limit($renewal->partner_error_message, 20) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($renewal->partner_request_id)
                            <code class="small">{{ $renewal->partner_request_id }}</code>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('telesales.renewal.show', $renewal) }}" class="btn btn-sm btn-outline-dark" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent">
            {{ $renewals->withQueryString()->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-inbox text-muted" style="font-size:3rem"></i>
            <h5 class="mt-3 text-muted">No Data Found</h5>
            <p class="text-muted">Upload renewal data to get started.</p>
            <a href="{{ route('telesales.renewal.upload') }}" class="btn btn-dark"><i class="bi bi-upload me-2"></i>Upload Data</a>
        </div>
        @endif
    </div>
</div>
@endsection
