@extends('layouts.app')
@section('title', 'Upload Renewal')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('telesales.renewal.index') }}">Renewal</a></li><li class="breadcrumb-item active">Upload</li></ol>@endsection

@section('content')
<div class="row">
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-header bg-transparent"><h5 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Upload Renewal Data</h5></div>
            <div class="card-body">
                @if($events->count() == 0)
                <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>No active events. <a href="{{ route('settings.create') }}">Create one first</a>.</div>
                @else
                <form action="{{ route('telesales.renewal.upload.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select Event <span class="text-danger">*</span></label>
                        <select name="renewal_event_id" class="form-select @error('renewal_event_id') is-invalid @enderror" required>
                            <option value="">-- Select Event --</option>
                            @foreach($events as $e)<option value="{{ $e->id }}">{{ $e->name }} - {{ $e->period }}</option>@endforeach
                        </select>
                        @error('renewal_event_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Excel File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls" required>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Accepted: .xlsx, .xls (Max 10MB)</div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-2"></i>Upload & Process</button>
                </form>
                @endif
            </div>
        </div>

        <div class="card stat-card mt-4">
            <div class="card-header bg-transparent"><h6 class="mb-0"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Expected Format</h6></div>
            <div class="card-body">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light"><tr><th>Column</th><th>Required</th></tr></thead>
                    <tbody>
                        <tr><td>No Kontrak</td><td><span class="badge bg-danger">Yes</span></td></tr>
                        <tr><td>ANO</td><td><span class="badge bg-danger">Yes</span></td></tr>
                        <tr><td>End Date</td><td><span class="badge bg-warning">Recommended</span></td></tr>
                        <tr><td>Nama Tertanggung</td><td><span class="badge bg-warning">Recommended</span></td></tr>
                        <tr><td>Merk, Tipe, Tahun</td><td><span class="badge bg-secondary">Optional</span></td></tr>
                        <tr><td>Nilai Pertanggungan</td><td><span class="badge bg-secondary">Optional</span></td></tr>
                        <tr><td>Premi Options</td><td><span class="badge bg-secondary">Optional</span></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-header bg-transparent"><h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Uploads</h6></div>
            <div class="card-body p-0">
                @if($recentUploads->count() > 0)
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>File</th><th>Event</th><th>Result</th><th>Date</th></tr></thead>
                    <tbody>
                        @foreach($recentUploads as $u)
                        <tr>
                            <td><small>{{ Str::limit($u->original_filename, 20) }}</small></td>
                            <td><small>{{ $u->renewalEvent->name ?? '-' }}</small></td>
                            <td><span class="badge bg-{{ $u->status_badge }}">{{ ucfirst($u->status) }}</span>@if($u->status=='completed')<br><small>{{ $u->success_rows }}/{{ $u->total_rows }}</small>@endif</td>
                            <td><small>{{ $u->created_at->format('d/m H:i') }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="text-center py-4"><p class="text-muted mb-0">No uploads yet</p></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
