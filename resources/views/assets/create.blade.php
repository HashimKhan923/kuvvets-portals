@extends('layouts.app')
@section('title', 'Register Asset')
@section('page-title', 'Register New Asset')
@section('breadcrumb', 'Assets · Register')

@section('content')
<div style="max-width:900px;">
<form method="POST" action="{{ route('assets.store') }}" enctype="multipart/form-data">
@csrf

@if($errors->any())
<div class="error-box" style="margin-bottom:18px;">
    <div class="error-box-title"><i class="fa-solid fa-triangle-exclamation"></i> Please fix these errors:</div>
    <ul style="padding-left:16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- Tabs --}}
<div class="tab-nav">
    @foreach([['basic','fa-box','Basic Info'],['financial','fa-money-bill','Financial'],['dates','fa-calendar','Dates & Compliance']] as [$id,$icon,$label])
    <button type="button" class="tab-btn" id="atab-{{ $id }}" onclick="switchAssetTab('{{ $id }}')">
        <i class="fa-solid {{ $icon }}"></i> {{ $label }}
    </button>
    @endforeach
</div>

{{-- BASIC INFO --}}
<div id="apane-basic">
<div class="card" style="margin-bottom:16px;">
    <div class="form-section"><i class="fa-solid fa-box"></i> Asset Information</div>

    {{-- Image Upload --}}
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border);">
        <div id="assetImgPreview"
             style="width:80px;height:80px;background:var(--accent-bg);border:2px solid var(--accent-border);
                    border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fa-solid fa-box" style="font-size:28px;color:var(--accent);"></i>
        </div>
        <div>
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:6px;">Asset Photo (optional)</div>
            <label class="btn btn-secondary btn-sm" style="cursor:pointer;">
                <i class="fa-solid fa-upload"></i> Upload Image
                <input type="file" name="image" accept="image/*" style="display:none;"
                       onchange="previewAssetImg(this)">
            </label>
            <div style="font-size:10px;color:var(--text-muted);margin-top:4px;">Max 2MB · JPG, PNG</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <div style="grid-column:span 2;">
            <label class="form-label">Asset Name <span style="color:var(--red);">*</span></label>
            <input type="text" name="name" required value="{{ old('name') }}"
                   placeholder="e.g. Komatsu Forklift FD25T-17" class="form-input">
        </div>
        <div>
            <label class="form-label">Category</label>
            <select name="asset_category_id" class="form-select">
                <option value="">Select Category</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('asset_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Asset Type <span style="color:var(--red);">*</span></label>
            <select name="type" required class="form-select">
                @foreach(['heavy_equipment'=>'Heavy Equipment','vehicle'=>'Vehicle','forklift'=>'Forklift','crane'=>'Crane','warehouse_equipment'=>'Warehouse Equipment','it_equipment'=>'IT Equipment','furniture'=>'Furniture','tools'=>'Tools','safety_equipment'=>'Safety Equipment','other'=>'Other'] as $v => $l)
                <option value="{{ $v }}" {{ old('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select">
                <option value="">None</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ old('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Brand / Make</label>
            <input type="text" name="brand" value="{{ old('brand') }}" placeholder="e.g. Komatsu / Toyota" class="form-input">
        </div>
        <div>
            <label class="form-label">Model</label>
            <input type="text" name="model" value="{{ old('model') }}" placeholder="e.g. FD25T-17" class="form-input">
        </div>
        <div>
            <label class="form-label">Serial Number</label>
            <input type="text" name="serial_number" value="{{ old('serial_number') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Registration Number</label>
            <input type="text" name="registration_number" value="{{ old('registration_number') }}" placeholder="For vehicles / equipment" class="form-input">
        </div>
        <div>
            <label class="form-label">Condition <span style="color:var(--red);">*</span></label>
            <select name="condition" required class="form-select">
                @foreach(['new'=>'New','good'=>'Good','fair'=>'Fair','poor'=>'Poor','under_repair'=>'Under Repair'] as $v => $l)
                <option value="{{ $v }}" {{ old('condition', 'good') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Ownership <span style="color:var(--red);">*</span></label>
            <select name="ownership" required class="form-select">
                @foreach(['owned'=>'Owned','leased'=>'Leased','rented'=>'Rented'] as $v => $l)
                <option value="{{ $v }}" {{ old('ownership', 'owned') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Location / Bay</label>
            <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Warehouse A Bay 3" class="form-input">
        </div>
        <div>
            <label class="form-label">Odometer (km)</label>
            <input type="number" name="odometer_reading" value="{{ old('odometer_reading') }}" placeholder="For vehicles" class="form-input">
        </div>
        <div>
            <label class="form-label">Operating Hours</label>
            <input type="number" name="operating_hours" value="{{ old('operating_hours') }}" placeholder="For heavy equipment" class="form-input">
        </div>
        <div style="grid-column:span 3;">
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="2" class="form-textarea">{{ old('notes') }}</textarea>
        </div>
    </div>
</div>
</div>

{{-- FINANCIAL --}}
<div id="apane-financial" style="display:none;">
<div class="card" style="margin-bottom:16px;">
    <div class="form-section"><i class="fa-solid fa-money-bill-wave"></i> Financial Details</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <div>
            <label class="form-label">Purchase Date</label>
            <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Purchase Cost (PKR)</label>
            <input type="number" name="purchase_cost" value="{{ old('purchase_cost') }}" placeholder="0.00" step="0.01" class="form-input">
        </div>
        <div>
            <label class="form-label">Current Value (PKR)</label>
            <input type="number" name="current_value" value="{{ old('current_value') }}" placeholder="0.00" step="0.01" class="form-input">
        </div>
        <div>
            <label class="form-label">Depreciation Rate (% /year)</label>
            <input type="number" name="depreciation_rate" value="{{ old('depreciation_rate', 0) }}" placeholder="e.g. 20" step="0.5" min="0" max="100" class="form-input">
        </div>
        <div>
            <label class="form-label">Vendor / Supplier</label>
            <input type="text" name="vendor" value="{{ old('vendor') }}" placeholder="Vendor company name" class="form-input">
        </div>
        <div>
            <label class="form-label">Vendor Contact</label>
            <input type="text" name="vendor_contact" value="{{ old('vendor_contact') }}" placeholder="+92-XXX-XXXXXXX" class="form-input">
        </div>
    </div>

    {{-- Live Depreciation Calculator --}}
    <div style="margin-top:18px;padding:16px;background:var(--accent-bg);border:1px solid var(--accent-border);border-radius:10px;">
        <div class="section-label" style="margin-bottom:10px;">Live Depreciation Calculator</div>
        <div style="display:flex;gap:24px;flex-wrap:wrap;">
            @foreach(['Year 1' => 'depYear1', 'Year 3' => 'depYear3', 'Year 5' => 'depYear5'] as $label => $id)
            <div>
                <div style="font-size:10px;color:var(--text-muted);margin-bottom:2px;">{{ $label }}</div>
                <div id="{{ $id }}" style="font-size:16px;font-weight:700;color:var(--accent);">—</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
</div>

{{-- DATES & COMPLIANCE --}}
<div id="apane-dates" style="display:none;">
<div class="card" style="margin-bottom:16px;">
    <div class="form-section"><i class="fa-solid fa-calendar-check"></i> Dates & Compliance</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <div>
            <label class="form-label">Warranty Expiry</label>
            <input type="date" name="warranty_expiry" value="{{ old('warranty_expiry') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Insurance Expiry</label>
            <input type="date" name="insurance_expiry" value="{{ old('insurance_expiry') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Insurance Policy No.</label>
            <input type="text" name="insurance_policy" value="{{ old('insurance_policy') }}" placeholder="Policy number" class="form-input">
        </div>
        <div>
            <label class="form-label">License / Permit Expiry</label>
            <input type="date" name="license_expiry" value="{{ old('license_expiry') }}" class="form-input">
        </div>
    </div>
    <div class="note-block" style="margin-top:16px;">
        <div class="note-block-text">
            <i class="fa-solid fa-circle-info" style="color:var(--blue);margin-right:6px;"></i>
            <strong>Compliance Note:</strong> For vehicles and heavy equipment in Pakistan, ensure all documents comply with NTRC, FBR, and relevant provincial transport authority requirements. Insurance and fitness certificates must be renewed annually.
        </div>
    </div>
</div>
</div>

{{-- Submit --}}
<div style="display:flex;align-items:center;justify-content:space-between;">
    <a href="{{ route('assets.list') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Cancel
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-floppy-disk"></i> Register Asset
    </button>
</div>
</form>
</div>

@push('scripts')
<script>
function switchAssetTab(active) {
    ['basic', 'financial', 'dates'].forEach(function(t) {
        document.getElementById('apane-' + t).style.display = t === active ? 'block' : 'none';
        document.getElementById('atab-' + t).classList.toggle('active', t === active);
    });
}
switchAssetTab('basic');

function previewAssetImg(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('assetImgPreview');
            preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updateDepreciation() {
    var costEl = document.querySelector('[name=purchase_cost]');
    var rateEl = document.querySelector('[name=depreciation_rate]');
    var cost = parseFloat(costEl ? costEl.value : 0) || 0;
    var rate = parseFloat(rateEl ? rateEl.value : 0) || 0;
    if (!cost || !rate) return;
    var r = rate / 100;
    function fmt(v) { return 'PKR ' + Math.round(v).toLocaleString(); }
    document.getElementById('depYear1').textContent = fmt(cost * Math.pow(1 - r, 1));
    document.getElementById('depYear3').textContent = fmt(cost * Math.pow(1 - r, 3));
    document.getElementById('depYear5').textContent = fmt(cost * Math.pow(1 - r, 5));
}

var costInput = document.querySelector('[name=purchase_cost]');
var rateInput = document.querySelector('[name=depreciation_rate]');
if (costInput) costInput.addEventListener('input', updateDepreciation);
if (rateInput) rateInput.addEventListener('input', updateDepreciation);
</script>
@endpush

@endsection