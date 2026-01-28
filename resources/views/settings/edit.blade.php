@extends('layouts.app')
@section('title', 'Edit Event')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Event Setup</a></li><li class="breadcrumb-item active">Edit</li></ol>@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card stat-card">
            <div class="card-header bg-transparent"><h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Event</h5></div>
            <div class="card-body">
                <form action="{{ route('settings.update', $event) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Event Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $event->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="event_type" class="form-select" required>
                                <option value="renewal" {{ old('event_type', $event->event_type) == 'renewal' ? 'selected' : '' }}>Renewal</option>
                                <option value="upgrade" {{ old('event_type', $event->event_type) == 'upgrade' ? 'selected' : '' }}>Upgrade</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <select name="year" class="form-select" required>
                                @for($y = date('Y') - 1; $y <= date('Y') + 2; $y++)<option value="{{ $y }}" {{ old('year', $event->year) == $y ? 'selected' : '' }}>{{ $y }}</option>@endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Month <span class="text-danger">*</span></label>
                            <select name="month" class="form-select @error('month') is-invalid @enderror" required>
                                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m)<option value="{{ $i + 1 }}" {{ old('month', $event->month) == ($i + 1) ? 'selected' : '' }}>{{ $m }}</option>@endforeach
                            </select>
                            @error('month')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(['draft','active','completed','cancelled'] as $s)<option value="{{ $s }}" {{ old('status', $event->status) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $event->start_date->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $event->end_date->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $event->description) }}</textarea>
                    </div>
                    @php $cnt = $event->renewalData()->count(); @endphp
                    @if($cnt > 0)<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>This event has <strong>{{ number_format($cnt) }}</strong> records.</div>@endif
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Update Event</button>
                        <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
