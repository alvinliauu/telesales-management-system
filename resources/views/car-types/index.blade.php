@extends('layouts.app')
@section('title', 'Car Types')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item">Telesales Event Setup</li><li class="breadcrumb-item active">Car Types</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Car Types</h4>
    <a href="{{ route('car-types.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add Car Type</a>
</div>

<div class="card stat-card">
    <div class="card-body p-0">
        @if($carTypes->count() > 0)
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th width="60">#</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-center" width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($carTypes as $carType)
                <tr>
                    <td>{{ $carType->sort_order }}</td>
                    <td><strong>{{ $carType->name }}</strong></td>
                    <td><code>{{ $carType->code }}</code></td>
                    <td><small class="text-muted">{{ $carType->description ?? '-' }}</small></td>
                    <td>
                        <span class="badge bg-{{ $carType->is_active ? 'success' : 'secondary' }}">
                            {{ $carType->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <form action="{{ route('car-types.toggle-status', $carType) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <button class="btn btn-outline-{{ $carType->is_active ? 'warning' : 'success' }}" title="{{ $carType->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi bi-{{ $carType->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            <a href="{{ route('car-types.edit', $carType) }}" class="btn btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('car-types.destroy', $carType) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this car type?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="card-footer bg-transparent">{{ $carTypes->links() }}</div>
        @else
        <div class="text-center py-5">
            <i class="bi bi-car-front text-muted" style="font-size:4rem"></i>
            <h5 class="mt-3 text-muted">No Car Types</h5>
            <p class="text-muted">Add your first car type (e.g., EV, NON EV)</p>
            <a href="{{ route('car-types.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Add Car Type</a>
        </div>
        @endif
    </div>
</div>
@endsection
