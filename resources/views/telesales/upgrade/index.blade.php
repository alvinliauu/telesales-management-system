@extends('layouts.app')
@section('title', 'Upgrade')
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Upgrade</li></ol>@endsection

@section('content')
<h4 class="mb-4">Upgrade</h4>
<div class="card stat-card">
    <div class="card-body text-center py-5">
        <i class="bi bi-arrow-up-circle text-muted" style="font-size: 5rem;"></i>
        <h4 class="mt-4 text-muted">Upgrade Module</h4>
        <p class="text-muted mb-4">This module is coming soon.</p>
        <span class="badge bg-warning fs-6">Under Development</span>
    </div>
</div>
@endsection
