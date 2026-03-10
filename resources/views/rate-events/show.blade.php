@extends('layouts.app')

@section('title', 'Detail Event: ' . $event->name)
@section('page-title', 'Detail Event')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('rate-events.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Event
        </a>
        @if($canEdit)
        <a href="{{ route('rate-events.edit', $event) }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Event
        </a>
        @endif
    </div>

    {{-- Event Info Card --}}
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $event->name }}</h2>
                <p class="text-sm text-gray-500 font-mono">{{ $event->code }}</p>
            </div>
            {!! $event->status_badge !!}
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Periode</p>
                    <p class="text-sm text-gray-900 mt-1">
                        {{ $event->start_date->format('d M Y') }} - {{ $event->end_date->format('d M Y') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Prioritas</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $event->priority }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Dibuat Oleh</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $event->creator->name ?? '-' }}</p>
                    <p class="text-xs text-gray-500">{{ $event->created_at->format('d M Y H:i') }}</p>
                </div>
                @if($event->approver)
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Disetujui Oleh</p>
                    <p class="text-sm text-gray-900 mt-1">{{ $event->approver->name }}</p>
                    <p class="text-xs text-gray-500">{{ $event->approved_at->format('d M Y H:i') }}</p>
                </div>
                @endif
            </div>

            @if($event->description)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Deskripsi</p>
                <p class="text-sm text-gray-700">{{ $event->description }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Rules --}}
    <div class="bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Rules ({{ $event->rules->count() }})</h2>
        </div>

        @if($event->rules->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filter</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Override</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diskon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kondisi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($event->rules as $rule)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if($rule->vehicleType)
                                    <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded">{{ $rule->vehicleType->name }}</span>
                                @endif
                                @if($rule->coverageType)
                                    <span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded">{{ $rule->coverageType->code }}</span>
                                @endif
                                @if($rule->zone)
                                    <span class="px-2 py-0.5 text-xs bg-yellow-100 text-yellow-700 rounded">{{ $rule->zone->name }}</span>
                                @endif
                                @if($rule->vehiclePriceCategory)
                                    <span class="px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded">{{ $rule->vehiclePriceCategory->name }}</span>
                                @endif
                                @if($rule->transactionType)
                                    <span class="px-2 py-0.5 text-xs bg-orange-100 text-orange-700 rounded">{{ $rule->transactionType->name }}</span>
                                @endif
                                @if($rule->tsiOption)
                                    <span class="px-2 py-0.5 text-xs bg-pink-100 text-pink-700 rounded">TSI {{ $rule->tsiOption->label }}</span>
                                @endif
                                @if(!$rule->vehicleType && !$rule->coverageType && !$rule->zone && !$rule->vehiclePriceCategory && !$rule->transactionType && !$rule->tsiOption)
                                    <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">Semua</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ $rule->override_description }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($rule->discount_percent > 0)
                                <span class="text-sm text-gray-900">{{ $rule->discount_percent }}%</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($rule->only_if_previous_exists)
                                <span class="text-xs text-orange-600">Jika tahun lalu ada</span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-500">{{ $rule->notes ?: '-' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-12 text-center text-gray-500">
            <p>Belum ada rule untuk event ini.</p>
        </div>
        @endif
    </div>
</div>
@endsection
