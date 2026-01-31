@extends('layouts.app')
@section('title', 'Event Packages - ' . $event->name)
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Events</a></li><li class="breadcrumb-item active">{{ $event->name }} - Packages</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $event->name }}</h4>
        <small class="text-muted">{{ $event->period }} | <span class="badge bg-{{ $event->status == 'active' ? 'success' : 'secondary' }}">{{ ucfirst($event->status) }}</span></small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back to Events</a>
        <a href="{{ route('events.packages.create', $event) }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add Package</a>
    </div>
</div>

@if($event->packages->count() > 0)
<div class="row g-4">
    @foreach($event->packages as $package)
    <div class="col-lg-6">
        <div class="card stat-card h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{{ $package->name }}</h5>
                    @if($package->description)<small class="text-muted">{{ $package->description }}</small>@endif
                </div>
                <span class="badge bg-{{ $package->is_active ? 'success' : 'secondary' }}">{{ $package->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="card-body">
                @php
                    $groupedExtensions = $package->packageExtensions->groupBy('extension_id');
                @endphp
                
                @if($groupedExtensions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Extension</th>
                                @foreach($carTypes as $ct)
                                <th class="text-center">{{ $ct->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedExtensions as $extId => $rates)
                            @php $ext = $rates->first()->extension; @endphp
                            <tr>
                                <td>
                                    @if($ext->is_main_coverage)<span class="badge bg-danger me-1">Main</span>@endif
                                    {{ $ext->name }}
                                    @if($ext->max_vehicle_age)<br><small class="text-warning"><i class="bi bi-exclamation-triangle"></i> ≤{{ $ext->max_vehicle_age }}yr</small>@endif
                                </td>
                                @foreach($carTypes as $ct)
                                @php $rate = $rates->where('car_type_id', $ct->id)->first(); @endphp
                                <td class="text-center">
                                    @if($rate)
                                        <span class="badge bg-{{ $rate->rate_type == 'percentage' ? 'primary' : 'success' }}">
                                            {{ $rate->formatted_rate }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted text-center mb-0">No extensions configured</p>
                @endif
            </div>
            <div class="card-footer bg-transparent">
                <div class="btn-group btn-group-sm">
                    <form action="{{ route('events.packages.toggle-status', [$event, $package]) }}" method="POST" class="d-inline">
                        @csrf @method('PATCH')
                        <button class="btn btn-outline-{{ $package->is_active ? 'warning' : 'success' }}">
                            <i class="bi bi-{{ $package->is_active ? 'pause' : 'play' }} me-1"></i>{{ $package->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <a href="{{ route('events.packages.edit', [$event, $package]) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <form action="{{ route('events.packages.destroy', [$event, $package]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this package?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card stat-card">
    <div class="card-body text-center py-5">
        <i class="bi bi-box text-muted" style="font-size:4rem"></i>
        <h5 class="mt-3 text-muted">No Packages</h5>
        <p class="text-muted">Create packages with extensions and rates for this event.</p>
        <a href="{{ route('events.packages.create', $event) }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add Package</a>
    </div>
</div>
@endif
@endsection
