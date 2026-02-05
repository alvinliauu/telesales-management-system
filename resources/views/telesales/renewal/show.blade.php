@extends('layouts.app')
@section('title', 'Detail - ' . $renewal->nama_tertanggung)
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('telesales.renewal.index') }}">Renewal</a></li><li class="breadcrumb-item active">Detail</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Data Detail</h4>
    <a href="{{ route('telesales.renewal.index', ['event_id' => $renewal->renewal_event_id]) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to List
    </a>
</div>

<div class="row g-4">
    <!-- Main Info -->
    <div class="col-lg-8">
        <div class="card stat-card">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Policy Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-muted">Insured Name</label>
                        <div class="fw-semibold">{{ $renewal->nama_tertanggung }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Phone Number</label>
                        <div class="fw-semibold">{{ $renewal->no_hp ?? '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Policy Number</label>
                        <div><code>{{ $renewal->no_polis }}</code></div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted">Contract Number</label>
                        <div><code>{{ $renewal->no_kontrak }}</code></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card stat-card mt-4">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-car-front me-2"></i>Vehicle Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small text-muted">Plate Number</label>
                        <div class="fw-semibold">{{ $renewal->ano }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Brand / Model</label>
                        <div>{{ $renewal->merk_kendaraan }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Year</label>
                        <div>{{ $renewal->tahun_kendaraan }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card stat-card mt-4">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-shield me-2"></i>Coverage Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="small text-muted">Coverage Type</label>
                        <div>{{ $renewal->jenis_coverage }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Sum Insured (TSI)</label>
                        <div>Rp {{ number_format($renewal->tsi, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Premium</label>
                        <div>Rp {{ number_format($renewal->premi, 0, ',', '.') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Start Date</label>
                        <div>{{ $renewal->start_date?->format('d/m/Y') ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">End Date</label>
                        <div>{{ $renewal->end_date?->format('d/m/Y') ?? '-' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Event</label>
                        <div>{{ $renewal->renewalEvent?->name ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Partner Status Sidebar -->
    <div class="col-lg-4">
        <div class="card stat-card">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-send me-2"></i>Partner Status</h6>
            </div>
            <div class="card-body">
                @php
                    $statusClass = match($renewal->partner_status) {
                        'success' => 'bg-dark',
                        'failed' => 'bg-secondary',
                        'queued' => 'bg-light text-dark border',
                        default => 'bg-light text-muted border'
                    };
                @endphp
                
                <div class="text-center mb-4">
                    <span class="badge {{ $statusClass }} fs-6 px-3 py-2">
                        {{ ucfirst($renewal->partner_status) }}
                    </span>
                </div>

                <div class="mb-3">
                    <label class="small text-muted">Request ID</label>
                    <div>
                        @if($renewal->partner_request_id)
                        <code class="fs-6">{{ $renewal->partner_request_id }}</code>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                @if($renewal->partner_sent_at)
                <div class="mb-3">
                    <label class="small text-muted">Sent At</label>
                    <div>{{ $renewal->partner_sent_at->format('d/m/Y H:i') }}</div>
                </div>
                @endif

                @if($renewal->partner_status === 'failed')
                <div class="mb-3">
                    <label class="small text-muted">Error Code</label>
                    <div><code>{{ $renewal->partner_error_code }}</code></div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted">Error Message</label>
                    <div class="alert alert-secondary small mb-0">
                        {{ $renewal->partner_error_message }}
                    </div>
                </div>
                <div class="mb-0">
                    <label class="small text-muted">Retry Count</label>
                    <div>{{ $renewal->partner_retry_count }} / 3</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Additional Info -->
        <div class="card stat-card mt-4">
            <div class="card-header bg-transparent">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Additional Info</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="small text-muted">Agent</label>
                    <div>{{ $renewal->agent ?? '-' }}</div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted">Branch</label>
                    <div>{{ $renewal->cabang ?? '-' }}</div>
                </div>
                <div class="mb-0">
                    <label class="small text-muted">Uploaded</label>
                    <div>{{ $renewal->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
