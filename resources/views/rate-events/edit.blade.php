@extends('layouts.app')

@section('title', 'Edit Event: ' . $event->name)
@section('page-title', 'Edit Event')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <div>
        <a href="{{ route('rate-events.index') }}" class="text-sm text-gray-500 hover:text-gray-700 inline-flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Event
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Event Info --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Event Details --}}
            <div class="bg-white border border-gray-200 rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Detail Event</h2>
                    {!! $event->status_badge !!}
                </div>

                <form action="{{ route('rate-events.update', $event) }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                        <p class="text-sm text-gray-900 font-mono bg-gray-50 px-3 py-2 rounded">{{ $event->code }}</p>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Event</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $event->name) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                        <textarea name="description" id="description" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">{{ old('description', $event->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Mulai</label>
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $event->start_date->format('Y-m-d')) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Selesai</label>
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $event->end_date->format('Y-m-d')) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Prioritas</label>
                        <input type="number" name="priority" id="priority" value="{{ old('priority', $event->priority) }}" min="0"
                               class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                    </div>

                    <div class="pt-4 border-t border-gray-200 flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>

            {{-- Actions --}}
            <div class="bg-white border border-gray-200 rounded-lg p-6 space-y-3">
                <h3 class="text-sm font-medium text-gray-900 mb-4">Aksi</h3>
                
                @if($event->status === 'draft')
                    <form action="{{ route('rate-events.activate', $event) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                            Aktifkan Event
                        </button>
                    </form>
                @endif

                @if($event->status === 'active')
                    <form action="{{ route('rate-events.cancel', $event) }}" method="POST" 
                          onsubmit="return confirm('Yakin ingin membatalkan event ini?')">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition">
                            Batalkan Event
                        </button>
                    </form>
                @endif

                @if($event->status !== 'active')
                    <form action="{{ route('rate-events.destroy', $event) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus event ini? Semua rules juga akan terhapus.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
                            Hapus Event
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Right: Rules --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Add Rule Form --}}
            <div class="bg-white border border-gray-200 rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Tambah Rule</h2>
                    <p class="text-sm text-gray-500 mt-1">Kosongkan field filter untuk berlaku pada semua</p>
                </div>

                <form action="{{ route('rate-events.add-rule', $event) }}" method="POST" class="p-6 space-y-4">
                    @csrf

                    {{-- Filters Row 1 --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Kendaraan</label>
                            <select name="vehicle_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                                <option value="">Semua</option>
                                @foreach($vehicleTypes as $vt)
                                    <option value="{{ $vt->id }}">{{ $vt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Coverage</label>
                            <select name="coverage_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                                <option value="">Semua</option>
                                @foreach($coverageTypes as $ct)
                                    <option value="{{ $ct->id }}">{{ $ct->code }} - {{ $ct->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Zone</label>
                            <select name="zone_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                                <option value="">Semua</option>
                                @foreach($zones as $z)
                                    <option value="{{ $z->id }}">{{ $z->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Filters Row 2 --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Kategori Harga</label>
                            <select name="vehicle_price_category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                                <option value="">Semua</option>
                                @foreach($priceCategories as $pc)
                                    <option value="{{ $pc->id }}">{{ $pc->name }} ({{ $pc->range }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Tipe Transaksi</label>
                            <select name="transaction_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                                <option value="">Semua</option>
                                @foreach($transactionTypes as $tt)
                                    <option value="{{ $tt->id }}">{{ $tt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">TSI Option (TPL/PA)</label>
                            <select name="tsi_option_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                                <option value="">Semua / N/A</option>
                                @foreach($tsiOptions as $tsi)
                                    <option value="{{ $tsi->id }}">{{ $tsi->coverageType->code }} - {{ $tsi->label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Override Type --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Override Type <span class="text-red-500">*</span></label>
                            <select name="override_type" id="override_type" required onchange="toggleOverrideFields()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent">
                                @foreach($overrideTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="override_value_field">
                            {{-- Dynamic field based on override_type --}}
                        </div>
                    </div>

                    {{-- Additional Fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Diskon Tambahan (%)</label>
                            <input type="number" name="discount_percent" step="0.01" min="0" max="100"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                                   placeholder="0">
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center">
                                <input type="checkbox" name="only_if_previous_exists" value="1" 
                                       class="rounded border-gray-300 text-gray-900 focus:ring-gray-900">
                                <span class="ml-2 text-sm text-gray-700">Hanya jika tahun lalu ada coverage ini</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                        <input type="text" name="notes" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                               placeholder="Catatan tambahan (opsional)">
                    </div>

                    <div class="pt-4 border-t border-gray-200">
                        <button type="submit" class="px-6 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition">
                            Tambah Rule
                        </button>
                    </div>
                </form>
            </div>

            {{-- Rules List --}}
            <div class="bg-white border border-gray-200 rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Rules ({{ $event->rules->count() }})</h2>
                </div>

                @if($event->rules->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($event->rules as $rule)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                {{-- Filters --}}
                                <div class="flex flex-wrap gap-2 mb-2">
                                    @if($rule->vehicleType)
                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">{{ $rule->vehicleType->name }}</span>
                                    @endif
                                    @if($rule->coverageType)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">{{ $rule->coverageType->code }}</span>
                                    @endif
                                    @if($rule->zone)
                                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">{{ $rule->zone->name }}</span>
                                    @endif
                                    @if($rule->vehiclePriceCategory)
                                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-700 rounded">{{ $rule->vehiclePriceCategory->name }}</span>
                                    @endif
                                    @if($rule->transactionType)
                                        <span class="px-2 py-1 text-xs bg-orange-100 text-orange-700 rounded">{{ $rule->transactionType->name }}</span>
                                    @endif
                                    @if($rule->tsiOption)
                                        <span class="px-2 py-1 text-xs bg-pink-100 text-pink-700 rounded">TSI {{ $rule->tsiOption->label }}</span>
                                    @endif
                                    @if(!$rule->vehicleType && !$rule->coverageType && !$rule->zone && !$rule->vehiclePriceCategory && !$rule->transactionType && !$rule->tsiOption)
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">Berlaku untuk semua</span>
                                    @endif
                                </div>

                                {{-- Override --}}
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900">{{ $rule->override_description }}</span>
                                    @if($rule->discount_percent > 0)
                                        <span class="text-gray-500"> + Diskon {{ $rule->discount_percent }}%</span>
                                    @endif
                                    @if($rule->only_if_previous_exists)
                                        <span class="text-xs text-orange-600 ml-2">(jika tahun lalu ada)</span>
                                    @endif
                                </div>

                                @if($rule->notes)
                                    <p class="text-xs text-gray-500 mt-1">{{ $rule->notes }}</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 ml-4">
                                <form action="{{ route('rate-events.delete-rule', [$event, $rule]) }}" method="POST"
                                      onsubmit="return confirm('Hapus rule ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-12 text-center text-gray-500">
                    <p>Belum ada rule. Tambahkan rule di atas.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function toggleOverrideFields() {
    const overrideType = document.getElementById('override_type').value;
    const valueField = document.getElementById('override_value_field');
    
    let html = '';
    
    switch(overrideType) {
        case 'use_custom_rate':
            html = `
                <label class="block text-xs font-medium text-gray-500 mb-1">Rate Custom (%)</label>
                <input type="number" name="custom_rate" step="0.0001" min="0" max="100" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                       placeholder="Contoh: 2.5">
            `;
            break;
        case 'use_flat_amount':
            html = `
                <label class="block text-xs font-medium text-gray-500 mb-1">Flat Amount (Rp)</label>
                <input type="number" name="flat_amount" min="0" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                       placeholder="Contoh: 50000">
            `;
            break;
        case 'use_percentage':
            html = `
                <label class="block text-xs font-medium text-gray-500 mb-1">Percentage (%)</label>
                <input type="number" name="percentage_value" step="0.0001" min="0" max="100" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                       placeholder="Contoh: 0.5">
            `;
            break;
        case 'tsi_add_amount':
            html = `
                <label class="block text-xs font-medium text-gray-500 mb-1">Tambah TSI (Rp)</label>
                <input type="number" name="tsi_add_amount" min="0" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-gray-900 focus:border-transparent"
                       placeholder="Contoh: 5000000">
            `;
            break;
        default:
            html = `<p class="text-xs text-gray-500 pt-6">Tidak perlu nilai tambahan</p>`;
    }
    
    valueField.innerHTML = html;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', toggleOverrideFields);
</script>
@endsection
