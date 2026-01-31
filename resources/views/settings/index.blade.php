@extends('layouts.app')
@section('title', 'Events')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">Telesales Event Setup</li><li class="breadcrumb-item active">Events</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Events</h4>
    <a href="{{ route('settings.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Create Event</a>
</div>

<div class="card stat-card">
    <div class="card-body p-0">
        @if($events->count() > 0)
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Event Name</th>
                    <th>Type</th>
                    <th>Period</th>
                    <th>Date Range</th>
                    <th>Status</th>
                    <th>Packages</th>
                    <th>Data</th>
                    <th class="text-center" width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                @php 
                    $statusColors = ['draft'=>'secondary','active'=>'success','completed'=>'info','cancelled'=>'danger']; 
                    $packageCount = $event->packages()->count();
                    $dataCount = $event->renewalData()->count();
                @endphp
                <tr>
                    <td>
                        <strong>{{ $event->name }}</strong>
                        @if($event->description)<br><small class="text-muted">{{ Str::limit($event->description, 40) }}</small>@endif
                    </td>
                    <td><span class="badge bg-{{ $event->event_type == 'renewal' ? 'primary' : 'info' }}">{{ ucfirst($event->event_type) }}</span></td>
                    <td>{{ $event->period }}</td>
                    <td>{{ $event->start_date->format('d/m/Y') }}<br><small class="text-muted">to {{ $event->end_date->format('d/m/Y') }}</small></td>
                    <td><span class="badge bg-{{ $statusColors[$event->status] ?? 'secondary' }}">{{ ucfirst($event->status) }}</span></td>
                    <td>
                        <a href="{{ route('events.packages.index', $event) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box me-1"></i>{{ $packageCount }} Packages
                        </a>
                    </td>
                    <td>
                        @if($dataCount > 0)
                            <span class="badge bg-primary">{{ number_format($dataCount) }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <form action="{{ route('settings.toggle-status', $event) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-{{ $event->status == 'active' ? 'warning' : 'success' }}" title="{{ $event->status == 'active' ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi bi-{{ $event->status == 'active' ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <a href="{{ route('settings.edit', $event) }}" class="btn btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($dataCount == 0)
                            <form action="{{ route('settings.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this event?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="card-footer bg-transparent">{{ $events->links() }}</div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-calendar-x text-muted" style="font-size:4rem"></i>
            <h5 class="mt-3 text-muted">No Events</h5>
            <p class="text-muted">Create your first telesales event.</p>
            <a href="{{ route('settings.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Create Event</a>
        </div>
        @endif
    </div>
</div>
@endsection
