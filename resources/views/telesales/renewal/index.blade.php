@extends('layouts.app')
@section('title', 'Renewal')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Renewal</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Renewal Data</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('telesales.renewal.upload') }}" class="btn btn-success"><i class="bi bi-upload me-2"></i>Upload Data</a>
        @if($selectedEvent)<a href="{{ route('telesales.renewal.next-call', ['event_id' => $selectedEvent->id]) }}" class="btn btn-primary"><i class="bi bi-telephone me-2"></i>Start Calling</a>@endif
    </div>
</div>

<div class="card stat-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Event</label>
                <select name="event_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- All Events --</option>
                    @foreach($events as $event)<option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }} ({{ $event->period }})</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">-- All --</option>
                    @foreach(['pending','called','no_answer','callback','interested','renewed','declined','invalid_contact'] as $s)<option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, Contract, Policy, ANO..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('telesales.renewal.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

@if($statistics)
<div class="row g-3 mb-4">
    @php $statColors = ['total'=>'light','pending'=>'secondary','no_answer'=>'warning','callback'=>'primary','renewed'=>'success','declined'=>'danger']; @endphp
    @foreach(['total','pending','no_answer','callback','renewed','declined'] as $key)
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card bg-{{ $statColors[$key] }} {{ $key != 'total' ? 'bg-opacity-10' : '' }} border-0 text-center py-2">
            <div class="card-body py-2">
                <h5 class="mb-0 {{ $key != 'total' ? 'text-'.$statColors[$key] : '' }}">{{ number_format($statistics[$key]) }}</h5>
                <small class="text-muted">{{ ucfirst(str_replace('_',' ',$key)) }}</small>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="card stat-card">
    <div class="card-body p-0">
        @if($renewals->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Contract / ANO</th><th>Customer</th><th>Vehicle</th><th>End Date</th><th class="text-end">Sum Insured</th><th>Coverage</th><th>Status</th><th class="text-center">Action</th></tr></thead>
                <tbody>
                    @foreach($renewals as $r)
                    <tr>
                        <td><code class="text-primary">{{ $r->no_kontrak }}</code><br><small class="text-muted">ANO: {{ $r->ano }}</small></td>
                        <td><strong>{{ $r->nama_tertanggung }}</strong><br><small class="text-muted">{{ $r->no_polis }}</small></td>
                        <td>{{ $r->merk }} {{ $r->tipe }}<br><small class="text-muted">{{ $r->tahun_kendaraan }}</small></td>
                        <td>{{ $r->end_date?->format('d/m/Y') }}@if($r->end_date?->isPast())<br><span class="badge bg-danger">Expired</span>@endif</td>
                        <td class="text-end"><strong>Rp {{ number_format($r->nilai_pertanggungan, 0, ',', '.') }}</strong></td>
                        <td><span class="badge bg-info">{{ $r->jaminan_existing }}</span><br><small class="text-muted">Year {{ $r->tahun_renewal }}</small></td>
                        <td><span class="badge bg-{{ $r->status_badge }}">{{ $r->status_label }}</span>@if($r->callback_at && $r->call_status == 'callback')<br><small class="text-muted">{{ $r->callback_at->format('d/m H:i') }}</small>@endif</td>
                        <td class="text-center"><a href="{{ route('telesales.renewal.show', $r) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-telephone"></i></a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-transparent">{{ $renewals->withQueryString()->links() }}</div>
        @else
        <div class="text-center py-5"><i class="bi bi-inbox text-muted" style="font-size:4rem"></i><h5 class="mt-3 text-muted">No Data Found</h5></div>
        @endif
    </div>
</div>
@endsection
