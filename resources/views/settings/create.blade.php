@extends('layouts.app')
@section('title', 'Create Event')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Event Setup</a></li><li class="breadcrumb-item active">Create</li></ol>@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card stat-card">
            <div class="card-header bg-transparent"><h5 class="mb-0"><i class="bi bi-calendar-plus me-2"></i>Create New Event</h5></div>
            <div class="card-body">
                <form action="{{ route('settings.store') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Event Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g., Renewal November 2025" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="event_type" class="form-select" required>
                                <option value="renewal" {{ old('event_type') == 'renewal' ? 'selected' : '' }}>Renewal</option>
                                <option value="upgrade" {{ old('event_type') == 'upgrade' ? 'selected' : '' }}>Upgrade</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <select name="year" id="yearSelect" class="form-select" required>
                                @for($y = date('Y') - 1; $y <= date('Y') + 2; $y++)<option value="{{ $y }}" {{ old('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Month <span class="text-danger">*</span></label>
                            <select name="month" id="monthSelect" class="form-select @error('month') is-invalid @enderror" required>
                                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m)<option value="{{ $i + 1 }}" {{ old('month', date('n')) == ($i + 1) ? 'selected' : '' }}>{{ $m }}</option>@endforeach
                            </select>
                            @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="draft">Draft</option>
                                <option value="active" selected>Active</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="startDate" class="form-control" value="{{ old('start_date', date('Y-m-01')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="endDate" class="form-control" value="{{ old('end_date', date('Y-m-t')) }}" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Optional...">{{ old('description') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Create Event</button>
                        <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('yearSelect').addEventListener('change', updateDates);
document.getElementById('monthSelect').addEventListener('change', updateDates);
function updateDates() {
    const y = document.getElementById('yearSelect').value;
    const m = document.getElementById('monthSelect').value.padStart(2, '0');
    const lastDay = new Date(y, m, 0).getDate();
    document.getElementById('startDate').value = `${y}-${m}-01`;
    document.getElementById('endDate').value = `${y}-${m}-${lastDay.toString().padStart(2, '0')}`;
}
</script>
@endpush
