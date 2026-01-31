@extends('layouts.app')
@section('title', 'Extensions')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">Telesales Event Setup</li><li class="breadcrumb-item active">Extensions</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Extensions</h4>
    <a href="{{ route('extensions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add Extension</a>
</div>

<div class="card stat-card">
    <div class="card-body p-0">
        @if($extensions->count() > 0)
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th width="60">#</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Vehicle Age Limit</th>
                    <th>Status</th>
                    <th class="text-center" width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($extensions as $extension)
                <tr>
                    <td>{{ $extension->sort_order }}</td>
                    <td><strong>{{ $extension->name }}</strong>@if($extension->description)<br><small class="text-muted">{{ Str::limit($extension->description, 50) }}</small>@endif</td>
                    <td><code>{{ $extension->code }}</code></td>
                    <td>
                        @if($extension->is_main_coverage)
                            <span class="badge bg-danger">Main Coverage</span>
                        @else
                            <span class="badge bg-info">Add-on</span>
                        @endif
                    </td>
                    <td>
                        @if($extension->max_vehicle_age)
                            <span class="badge bg-warning">≤ {{ $extension->max_vehicle_age }} years</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $extension->is_active ? 'success' : 'secondary' }}">
                            {{ $extension->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <form action="{{ route('extensions.toggle-status', $extension) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-{{ $extension->is_active ? 'warning' : 'success' }}">
                                    <i class="bi bi-{{ $extension->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <a href="{{ route('extensions.edit', $extension) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('extensions.destroy', $extension) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="card-footer bg-transparent">{{ $extensions->links() }}</div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-shield-plus text-muted" style="font-size:4rem"></i>
            <h5 class="mt-3 text-muted">No Extensions</h5>
            <p class="text-muted">Add coverage extensions (TLO, COMPREHENSIVE, etc.)</p>
            <a href="{{ route('extensions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add Extension</a>
        </div>
        @endif
    </div>
</div>
@endsection
