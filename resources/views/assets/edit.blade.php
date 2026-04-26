@extends('layouts.app')
@section('title', 'Edit Asset')
@section('page-title', 'Edit Asset — ' . $asset->name)
@section('breadcrumb', 'Assets · ' . $asset->asset_code . ' · Edit')

@section('content')
<div style="max-width:900px;">
<form method="POST" action="{{ route('assets.update', $asset) }}" enctype="multipart/form-data">
@csrf @method('PUT')

<div class="card" style="margin-bottom:16px;">
    <div class="form-section"><i class="fa-solid fa-pen"></i> Edit Asset Details</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <div style="grid-column:span 2;">
            <label class="form-label">Asset Name <span style="color:var(--red);">*</span></label>
            <input type="text" name="name" required value="{{ old('name', $asset->name) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Category</label>
            <select name="asset_category_id" class="form-select">
                <option value="">Select Category</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('asset_category_id', $asset->asset_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Type</label>
            <select name="type" required class="form-select">
                @foreach(['heavy_equipment'=>'Heavy Equipment','vehicle'=>'Vehicle','forklift'=>'Forklift','crane'=>'Crane','warehouse_equipment'=>'Warehouse Equipment','it_equipment'=>'IT Equipment','furniture'=>'Furniture','tools'=>'Tools','safety_equipment'=>'Safety Equipment','other'=>'Other'] as $v => $l)
                <option value="{{ $v }}" {{ old('type', $asset->type) === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                @foreach(['available'=>'Available','assigned'=>'Assigned','under_maintenance'=>'Under Maintenance','out_of_service'=>'Out of Service','disposed'=>'Disposed'] as $v => $l)
                <option value="{{ $v }}" {{ old('status', $asset->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Condition</label>
            <select name="condition" required class="form-select">
                @foreach(['new'=>'New','good'=>'Good','fair'=>'Fair','poor'=>'Poor','under_repair'=>'Under Repair'] as $v => $l)
                <option value="{{ $v }}" {{ old('condition', $asset->condition) === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Ownership</label>
            <select name="ownership" class="form-select">
                @foreach(['owned'=>'Owned','leased'=>'Leased','rented'=>'Rented'] as $v => $l)
                <option value="{{ $v }}" {{ old('ownership', $asset->ownership) === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Current Value (PKR)</label>
            <input type="number" name="current_value" value="{{ old('current_value', $asset->current_value) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Odometer (km)</label>
            <input type="number" name="odometer_reading" value="{{ old('odometer_reading', $asset->odometer_reading) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Operating Hours</label>
            <input type="number" name="operating_hours" value="{{ old('operating_hours', $asset->operating_hours) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Location</label>
            <input type="text" name="location" value="{{ old('location', $asset->location) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Insurance Expiry</label>
            <input type="date" name="insurance_expiry" value="{{ old('insurance_expiry', $asset->insurance_expiry?->format('Y-m-d')) }}" class="form-input">
        </div>
        <div>
            <label class="form-label">License Expiry</label>
            <input type="date" name="license_expiry" value="{{ old('license_expiry', $asset->license_expiry?->format('Y-m-d')) }}" class="form-input">
        </div>
        <div style="grid-column:span 3;">
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="2" class="form-textarea">{{ old('notes', $asset->notes) }}</textarea>
        </div>
    </div>
</div>

<div style="display:flex;align-items:center;justify-content:space-between;">
    <a href="{{ route('assets.show', $asset) }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Cancel
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-floppy-disk"></i> Save Changes
    </button>
</div>
</form>
</div>
@endsection