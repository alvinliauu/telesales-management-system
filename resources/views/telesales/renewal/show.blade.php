@extends('layouts.app')
@section('title', 'Call - ' . $renewal->nama_tertanggung)
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('telesales.renewal.index') }}">Renewal</a></li><li class="breadcrumb-item active">Call</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><span class="badge bg-{{ $renewal->status_badge }} me-2">{{ $renewal->status_label }}</span>{{ $renewal->nama_tertanggung }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('telesales.renewal.index', ['event_id' => $renewal->renewal_event_id]) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <a href="{{ route('telesales.renewal.next-call', ['event_id' => $renewal->renewal_event_id]) }}" class="btn btn-primary"><i class="bi bi-skip-forward me-2"></i>Next Call</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="card stat-card mb-4">
            <div class="card-header bg-transparent"><h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" width="40%">Name</td><td><strong>{{ $renewal->nama_tertanggung }}</strong></td></tr>
                    <tr><td class="text-muted">Contract</td><td><code>{{ $renewal->no_kontrak }}</code></td></tr>
                    <tr><td class="text-muted">ANO</td><td><code>{{ $renewal->ano }}</code></td></tr>
                    <tr><td class="text-muted">Policy</td><td><code>{{ $renewal->no_polis }}</code></td></tr>
                    <tr><td class="text-muted">Phone</td><td>@if($renewal->no_telepon)<a href="tel:{{ $renewal->no_telepon }}" class="btn btn-sm btn-success"><i class="bi bi-telephone me-1"></i>{{ $renewal->no_telepon }}</a>@else<span class="text-muted">N/A</span>@endif</td></tr>
                </table>
            </div>
        </div>

        <div class="card stat-card mb-4">
            <div class="card-header bg-transparent"><h6 class="mb-0"><i class="bi bi-car-front me-2"></i>Vehicle</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" width="40%">Brand</td><td><strong>{{ $renewal->merk }}</strong></td></tr>
                    <tr><td class="text-muted">Type</td><td>{{ $renewal->tipe }}</td></tr>
                    <tr><td class="text-muted">Year</td><td>{{ $renewal->tahun_kendaraan }}</td></tr>
                    <tr><td class="text-muted">Sum Insured</td><td><strong class="text-primary">Rp {{ number_format($renewal->nilai_pertanggungan, 0, ',', '.') }}</strong></td></tr>
                </table>
            </div>
        </div>

        <div class="card stat-card mb-4">
            <div class="card-header bg-transparent"><h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>Policy</h6></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" width="40%">End Date</td><td><strong>{{ $renewal->end_date?->format('d F Y') }}</strong>@if($renewal->end_date?->isPast())<span class="badge bg-danger ms-2">Expired</span>@endif</td></tr>
                    <tr><td class="text-muted">Coverage</td><td><span class="badge bg-info">{{ $renewal->jaminan_existing }}</span></td></tr>
                    <tr><td class="text-muted">Claims</td><td>@if($renewal->jumlah_klaim > 0)<span class="badge bg-warning">{{ $renewal->jumlah_klaim }} claims</span>@else<span class="badge bg-success">No claims</span>@endif</td></tr>
                </table>
            </div>
        </div>

        <div class="card stat-card border-primary">
            <div class="card-header bg-primary text-white"><h6 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Premium Options</h6></div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 bg-light rounded d-flex justify-content-between"><div><strong>Comprehensive</strong><br><small class="text-muted">Full coverage</small></div><h5 class="mb-0 text-primary">Rp {{ number_format($renewal->premi_comprehensive, 0, ',', '.') }}</h5></div>
                    <div class="p-3 bg-light rounded d-flex justify-content-between"><div><strong>TLO Package</strong><br><small class="text-muted">TLO + TJH + PAD</small></div><h5 class="mb-0 text-success">Rp {{ number_format($renewal->premi_tlo, 0, ',', '.') }}</h5></div>
                    <div class="p-3 bg-light rounded d-flex justify-content-between"><div><strong>Comprehensive Extended</strong><br><small class="text-muted">Full + extras</small></div><h5 class="mb-0 text-warning">Rp {{ number_format($renewal->premi_comprehensive_extended, 0, ',', '.') }}</h5></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card stat-card mb-4">
            <div class="card-header bg-transparent"><h6 class="mb-0"><i class="bi bi-telephone-outbound me-2"></i>Update Call Result</h6></div>
            <div class="card-body">
                <form action="{{ route('telesales.renewal.update-call', $renewal) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Call Result <span class="text-danger">*</span></label>
                            <select name="result" id="callResult" class="form-select" required>
                                <option value="">-- Select --</option>
                                <option value="answered">Answered</option>
                                <option value="no_answer">No Answer</option>
                                <option value="busy">Busy</option>
                                <option value="voicemail">Voicemail</option>
                                <option value="wrong_number">Wrong Number</option>
                                <option value="callback_requested">Callback Requested</option>
                                <option value="interested">Interested</option>
                                <option value="renewed">✓ RENEWED</option>
                                <option value="declined">✗ Declined</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="callbackGroup" style="display:none">
                            <label class="form-label">Callback Date</label>
                            <input type="datetime-local" name="callback_date" class="form-control">
                        </div>
                    </div>
                    <div id="renewedFields" style="display:none" class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Package</label>
                            <select name="selected_package" class="form-select">
                                <option value="">-- Select --</option>
                                <option value="comprehensive">Comprehensive</option>
                                <option value="tlo">TLO Package</option>
                                <option value="comprehensive_extended">Comprehensive Extended</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Agreed Premium</label>
                            <input type="number" name="agreed_premium" class="form-control" placeholder="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Call notes..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save Result</button>
                </form>
            </div>
        </div>

        @if($renewal->call_notes)
        <div class="card stat-card mb-4 border-info">
            <div class="card-header bg-info bg-opacity-10"><h6 class="mb-0 text-info"><i class="bi bi-sticky me-2"></i>Current Notes</h6></div>
            <div class="card-body"><p class="mb-1">{{ $renewal->call_notes }}</p><small class="text-muted">Last: {{ $renewal->last_call_at?->format('d/m/Y H:i') }}</small></div>
        </div>
        @endif

        <div class="card stat-card">
            <div class="card-header bg-transparent"><h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Call History</h6></div>
            <div class="card-body p-0">
                @if($renewal->callHistories->count() > 0)
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Result</th><th>Agent</th><th>Notes</th></tr></thead>
                    <tbody>
                        @foreach($renewal->callHistories->sortByDesc('called_at') as $h)
                        <tr>
                            <td>{{ $h->called_at->format('d/m/Y') }}<br><small class="text-muted">{{ $h->called_at->format('H:i') }}</small></td>
                            <td><span class="badge bg-{{ $h->result_badge }}">{{ $h->result_label }}</span></td>
                            <td>{{ $h->calledByUser->name ?? '-' }}</td>
                            <td><small>{{ Str::limit($h->notes, 40) }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center py-4"><p class="text-muted mb-0">No call history</p></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('callResult').addEventListener('change', function() {
    document.getElementById('callbackGroup').style.display = this.value === 'callback_requested' ? 'block' : 'none';
    document.getElementById('renewedFields').style.display = this.value === 'renewed' ? 'flex' : 'none';
});
</script>
@endpush
