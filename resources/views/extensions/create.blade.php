@extends('layouts.app')
@section('title', 'Add Extension')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('extensions.index') }}">Extensions</a></li><li class="breadcrumb-item active">Add</li></ol>@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card stat-card">
            <div class="card-header bg-transparent"><h5 class="mb-0"><i class="bi bi-plus-lg me-2"></i>Add Extension</h5></div>
            <div class="card-body">
                <form action="{{ route('extensions.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g., TLO, COMPREHENSIVE, EQVET" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Auto-generated if empty">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional description">{{ old('description') }}</textarea>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_main_coverage" id="is_main_coverage" value="1" {{ old('is_main_coverage') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_main_coverage">
                                    <strong>Main Coverage</strong>
                                    <br><small class="text-muted">Check this for TLO or COMPREHENSIVE. Main coverages cannot be combined in the same package.</small>
                                </label>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Max Vehicle Age (years)</label>
                                <input type="number" name="max_vehicle_age" class="form-control" value="{{ old('max_vehicle_age') }}" min="1" max="50" placeholder="Leave empty if no limit">
                                <div class="form-text">e.g., Set to 5 for AUTHORIZED WORKSHOP. System will show warning if vehicle exceeds this age.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save</button>
                        <a href="{{ route('extensions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
