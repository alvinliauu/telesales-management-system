@extends('layouts.app')
@section('title', 'Add Package - ' . $event->name)
@section('breadcrumb')<ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('events.packages.index', $event) }}">{{ $event->name }}</a></li><li class="breadcrumb-item active">Add Package</li></ol>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add Package to {{ $event->name }}</h4>
</div>

<form action="{{ route('events.packages.store', $event) }}" method="POST" id="packageForm">
    @csrf
    
    @if($errors->any())
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}</div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card stat-card mb-4">
                <div class="card-header bg-transparent"><h6 class="mb-0">Package Info</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Package Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g., TLO Basic, Comprehensive Plus" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card stat-card bg-light">
                <div class="card-body">
                    <h6><i class="bi bi-info-circle me-2"></i>Rules</h6>
                    <ul class="mb-0 small">
                        <li><span class="badge bg-danger">Main</span> coverages (TLO/COMPREHENSIVE) cannot be combined</li>
                        <li><span class="text-warning"><i class="bi bi-exclamation-triangle"></i></span> = Vehicle age limit applies</li>
                        <li><strong>Percentage</strong> = Auto-calculated from sum insured</li>
                        <li><strong>Flat</strong> = Fixed amount (Rp)</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card stat-card">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Extensions & Rates</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addExtensionModal">
                        <i class="bi bi-plus-lg me-1"></i>Add Extension
                    </button>
                </div>
                <div class="card-body">
                    <div id="extensionsList">
                        <div class="text-center text-muted py-4" id="noExtensions">
                            <i class="bi bi-inbox" style="font-size:2rem"></i>
                            <p class="mb-0 mt-2">No extensions added. Click "Add Extension" to start.</p>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-2"></i>Save Package</button>
                        <a href="{{ route('events.packages.index', $event) }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Add Extension Modal -->
<div class="modal fade" id="addExtensionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Extension</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Extension</label>
                    <select id="extensionSelect" class="form-select">
                        <option value="">-- Select --</option>
                        @foreach($extensions as $ext)
                        <option value="{{ $ext->id }}" data-name="{{ $ext->name }}" data-main="{{ $ext->is_main_coverage ? '1' : '0' }}" data-max-age="{{ $ext->max_vehicle_age ?? '' }}">{{ $ext->name }} {!! $ext->is_main_coverage ? '(Main Coverage)' : '' !!} {!! $ext->max_vehicle_age ? '- ≤'.$ext->max_vehicle_age.'yr' : '' !!}</option>
                        @endforeach
                    </select>
                </div>
                <div id="rateInputs">
                    <label class="form-label">Rates per Car Type</label>
                    @foreach($carTypes as $ct)
                    <div class="card bg-light mb-2">
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-4"><strong>{{ $ct->name }}</strong></div>
                                <div class="col-4">
                                    <select class="form-select form-select-sm rate-type" data-car-type="{{ $ct->id }}" onchange="toggleRateField(this)">
                                        <option value="percentage">% (Auto)</option>
                                        <option value="flat">Flat (Rp)</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <input type="number" class="form-control form-control-sm rate-value" data-car-type="{{ $ct->id }}" step="0.0001" min="0" placeholder="Auto" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddExtension">Add</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let addedExtensions = [];
let hasMainCoverage = false;
const carTypes = @json($carTypes);

// Toggle rate field based on rate type
function toggleRateField(selectElement) {
    const carTypeId = selectElement.dataset.carType;
    const rateInput = document.querySelector('.rate-value[data-car-type="' + carTypeId + '"]');
    
    if (selectElement.value === 'percentage') {
        rateInput.disabled = true;
        rateInput.value = '';
        rateInput.placeholder = 'Auto';
    } else {
        rateInput.disabled = false;
        rateInput.placeholder = '0';
        rateInput.focus();
    }
}

// Reset modal when opened
document.getElementById('addExtensionModal').addEventListener('show.bs.modal', function() {
    document.getElementById('extensionSelect').value = '';
    document.querySelectorAll('.rate-type').forEach(sel => {
        sel.value = 'percentage';
        toggleRateField(sel);
    });
});

document.getElementById('confirmAddExtension').addEventListener('click', function() {
    const select = document.getElementById('extensionSelect');
    const extId = select.value;
    const option = select.options[select.selectedIndex];
    
    if (!extId) { alert('Please select an extension'); return; }
    if (addedExtensions.includes(extId)) { alert('This extension is already added'); return; }
    
    const isMain = option.dataset.main === '1';
    const maxAge = option.dataset.maxAge;
    const extName = option.dataset.name;
    
    if (isMain && hasMainCoverage) { alert('Cannot add another main coverage. TLO and COMPREHENSIVE cannot be combined.'); return; }
    
    const rates = [];
    let hasValidRate = false;
    
    document.querySelectorAll('.rate-type').forEach(sel => {
        const carTypeId = sel.dataset.carType;
        const rateType = sel.value;
        const rateInput = document.querySelector('.rate-value[data-car-type="' + carTypeId + '"]');
        let rateValue = 0;
        
        if (rateType === 'flat') {
            rateValue = parseFloat(rateInput.value) || 0;
            if (rateValue > 0) hasValidRate = true;
        } else {
            // Percentage is auto-calculated, so we mark it as valid
            hasValidRate = true;
            rateValue = 0; // Will be calculated later based on sum insured
        }
        
        rates.push({ car_type_id: carTypeId, rate_type: rateType, rate_value: rateValue });
    });
    
    if (!hasValidRate) { alert('Please set at least one rate'); return; }
    
    addedExtensions.push(extId);
    if (isMain) hasMainCoverage = true;
    addExtensionToList(extId, extName, isMain, maxAge, rates);
    
    bootstrap.Modal.getInstance(document.getElementById('addExtensionModal')).hide();
});

function addExtensionToList(extId, extName, isMain, maxAge, rates) {
    document.getElementById('noExtensions').style.display = 'none';
    const idx = addedExtensions.length - 1;
    
    let ratesHtml = '<table class="table table-sm table-bordered mb-0"><thead class="table-light"><tr><th>Car Type</th><th>Rate Type</th><th>Value</th></tr></thead><tbody>';
    rates.forEach((rate, rateIdx) => {
        const carType = carTypes.find(c => c.id == rate.car_type_id);
        let rateDisplay, badgeClass;
        
        if (rate.rate_type === 'percentage') {
            rateDisplay = 'Auto %';
            badgeClass = 'primary';
        } else {
            rateDisplay = 'Rp ' + rate.rate_value.toLocaleString();
            badgeClass = 'success';
        }
        
        ratesHtml += '<tr><td>' + carType.name + '</td><td>' + (rate.rate_type === 'percentage' ? 'Percentage' : 'Flat') + '</td><td><span class="badge bg-' + badgeClass + '">' + rateDisplay + '</span></td></tr>';
        ratesHtml += '<input type="hidden" name="extensions[' + idx + '][rates][' + rateIdx + '][car_type_id]" value="' + rate.car_type_id + '">';
        ratesHtml += '<input type="hidden" name="extensions[' + idx + '][rates][' + rateIdx + '][rate_type]" value="' + rate.rate_type + '">';
        ratesHtml += '<input type="hidden" name="extensions[' + idx + '][rates][' + rateIdx + '][rate_value]" value="' + rate.rate_value + '">';
    });
    ratesHtml += '</tbody></table>';
    
    const html = '<div class="card mb-3" id="ext-' + extId + '"><div class="card-body">' +
        '<div class="d-flex justify-content-between align-items-start mb-2"><div>' +
        (isMain ? '<span class="badge bg-danger me-1">Main</span>' : '') +
        '<strong>' + extName + '</strong>' +
        (maxAge ? '<span class="badge bg-warning ms-1">≤' + maxAge + 'yr</span>' : '') +
        '</div><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeExtension(\'' + extId + '\', ' + isMain + ')"><i class="bi bi-trash"></i></button></div>' +
        '<input type="hidden" name="extensions[' + idx + '][id]" value="' + extId + '">' + ratesHtml + '</div></div>';
    
    document.getElementById('extensionsList').insertAdjacentHTML('beforeend', html);
}

function removeExtension(extId, isMain) {
    document.getElementById('ext-' + extId).remove();
    addedExtensions = addedExtensions.filter(id => id !== extId);
    if (isMain) hasMainCoverage = false;
    if (addedExtensions.length === 0) document.getElementById('noExtensions').style.display = 'block';
}
</script>
@endpush