@php $loc = $location ?? null; @endphp

<form method="POST" action="{{ $loc ? route('locations.update', $loc) : route('locations.store') }}" x-data="locationForm()">
    @csrf
    @if($loc) @method('PUT') @endif

    <div class="form-card">
        <div class="form-section-title"><i class="fa-solid fa-circle-info"></i>Basic Information</div>

        <div class="form-row">
            <div class="field">
                <label>Location Code <span class="req">*</span></label>
                <input type="text" name="code" value="{{ old('code', $loc?->code) }}" required placeholder="e.g. KVT-WH-KHI-01" style="font-family:'Space Grotesk',monospace;text-transform:uppercase;">
                <div class="field-help"><i class="fa-solid fa-circle-info"></i>Unique identifier — letters, digits, dashes only</div>
                @error('code') <div class="field-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Location Name <span class="req">*</span></label>
                <input type="text" name="name" value="{{ old('name', $loc?->name) }}" required placeholder="e.g. Karachi Head Office">
                @error('name') <div class="field-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label>Type <span class="req">*</span></label>
                <select name="type" required>
                    @foreach(['warehouse'=>'Warehouse','office'=>'Office','site'=>'Site','branch'=>'Branch','other'=>'Other'] as $v=>$l)
                        <option value="{{ $v }}" {{ old('type', $loc?->type ?? 'office') === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Status</label>
                <label class="checkbox-row">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $loc?->is_active ?? true) ? 'checked' : '' }}>
                    <span>Active (employees can check in/out here)</span>
                </label>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-section-title"><i class="fa-solid fa-location-dot"></i>Address</div>

        <div class="form-row full">
            <div class="field">
                <label>Street Address</label>
                <textarea name="address" placeholder="Plot, building, street name">{{ old('address', $loc?->address) }}</textarea>
            </div>
        </div>
        <div class="form-row">
            <div class="field">
                <label>City</label>
                <input type="text" name="city" value="{{ old('city', $loc?->city) }}" placeholder="Karachi">
            </div>
            <div class="field">
                <label>Province</label>
                <select name="province">
                    <option value="">— Select —</option>
                    @foreach(['Sindh','Punjab','KPK','Balochistan','Islamabad','Gilgit-Baltistan','Azad Kashmir'] as $p)
                        <option value="{{ $p }}" {{ old('province', $loc?->province) === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ══ GPS + Map picker ══ --}}
    <div class="form-card">
        <div class="form-section-title"><i class="fa-solid fa-map-location-dot"></i>GPS Coordinates &amp; Geofence</div>

        <div style="font-size:12px;color:var(--muted);background:#EFF6FF;border:1px solid #BFDBFE;color:#1E40AF;border-radius:10px;padding:12px 14px;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px;">
            <i class="fa-solid fa-lightbulb" style="margin-top:2px;"></i>
            <div>
                <strong>Tip:</strong> Open Google Maps → right-click the exact spot → click the coordinates that appear → paste them below. Or click <strong>"Use my current location"</strong> to auto-fill from your device's GPS.
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label>Latitude <span class="req">*</span></label>
                <input type="number" step="0.0000001" min="-90" max="90" name="latitude" x-model="lat" required placeholder="24.8607" style="font-family:'Space Grotesk',monospace;">
                @error('latitude') <div class="field-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror
            </div>
            <div class="field">
                <label>Longitude <span class="req">*</span></label>
                <input type="number" step="0.0000001" min="-180" max="180" name="longitude" x-model="lng" required placeholder="67.0011" style="font-family:'Space Grotesk',monospace;">
                @error('longitude') <div class="field-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
            <button type="button" class="btn btn-secondary" @click="useMyLocation()" :disabled="locating">
                <i class="fa-solid" :class="locating ? 'fa-spinner fa-spin' : 'fa-location-crosshairs'"></i>
                <span x-text="locating ? 'Getting GPS...' : 'Use My Current Location'"></span>
            </button>
            <a :href="mapLink" target="_blank" rel="noopener" x-show="lat && lng" class="btn btn-secondary">
                <i class="fa-solid fa-map"></i> Preview on Google Maps
            </a>
        </div>

        <div class="form-row full">
            <div class="field">
                <label>Geofence Radius (meters) <span class="req">*</span></label>
                <input type="range" min="20" max="2000" step="10" x-model="radius" name="radius_meters" value="{{ old('radius_meters', $loc?->radius_meters ?? 100) }}" style="width:100%;height:6px;accent-color:var(--accent, #C2531B);">
                <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--muted);margin-top:6px;">
                    <span>20m (strict)</span>
                    <span style="font-family:'Space Grotesk',monospace;font-weight:700;color:var(--accent, #C2531B);font-size:14px;"><span x-text="radius"></span>m</span>
                    <span>2000m (lenient)</span>
                </div>
                <div class="field-help" style="margin-top:10px;">
                    <i class="fa-solid fa-circle-info"></i>
                    Employees must be within this distance from the GPS coordinates to check in.
                </div>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="form-section-title"><i class="fa-solid fa-note-sticky"></i>Internal Notes</div>
        <div class="form-row full">
            <div class="field">
                <label>Notes <span class="hint">(optional, admin-only)</span></label>
                <textarea name="notes" placeholder="Any internal reference notes...">{{ old('notes', $loc?->notes) }}</textarea>
            </div>
        </div>
    </div>

    <div class="form-card" style="margin-bottom:0;">
        <div class="form-actions" style="border:0;margin:0;padding:0;">
            <a href="{{ $loc ? route('locations.show', $loc) : route('locations.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-check"></i> {{ $loc ? 'Update Location' : 'Create Location' }}
            </button>
        </div>
    </div>
</form>

<style>
    .form-card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 20px; margin-bottom: 14px; }
    .form-section-title { font-family: 'Space Grotesk', sans-serif; font-size: 13px; font-weight: 700; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
    .form-section-title i { color: var(--accent, #C2531B); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 12px; }
    .form-row.full { grid-template-columns: 1fr; }
    @media (max-width:640px) { .form-row { grid-template-columns: 1fr; } }
    .field label { display: block; font-size: 11px; font-weight: 600; color: var(--text); letter-spacing: .5px; text-transform: uppercase; margin-bottom: 8px; }
    .field label .req { color: #DC2626; }
    .field label .hint { color: var(--muted); font-weight: 500; text-transform: none; letter-spacing: 0; font-size: 10px; margin-left: 6px; }
    .field input[type="text"], .field input[type="email"], .field input[type="number"], .field input[type="tel"], .field select, .field textarea { width: 100%; height: 42px; padding: 0 14px; background: #F7F3EF; border: 1.5px solid transparent; border-radius: 10px; font: inherit; font-size: 13.5px; color: var(--text); transition: all .15s; }
    .field textarea { height: auto; padding: 10px 14px; min-height: 70px; resize: vertical; }
    .field input:focus, .field select:focus, .field textarea:focus { outline: none; border-color: var(--accent, #C2531B); background: #fff; box-shadow: 0 0 0 3px rgba(194,83,27,.08); }
    .field-help { font-size: 11px; color: var(--muted); margin-top: 6px; display: flex; align-items: center; gap: 5px; }
    .field-err { font-size: 11.5px; color: #DC2626; margin-top: 6px; font-weight: 600; display: flex; align-items: center; gap: 5px; }
    .checkbox-row { display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: #F5F0EB; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 500; height: 42px; }
    .checkbox-row input { width: 16px; height: 16px; accent-color: var(--accent, #C2531B); cursor: pointer; }
    .form-actions { display: flex; justify-content: flex-end; gap: 10px; padding-top: 18px; border-top: 1px solid var(--border); margin-top: 8px; }
</style>

<script>
function locationForm() {
    return {
        lat: "{{ old('latitude', $loc?->latitude ?? '') }}",
        lng: "{{ old('longitude', $loc?->longitude ?? '') }}",
        radius: {{ old('radius_meters', $loc?->radius_meters ?? 100) }},
        locating: false,
        get mapLink() {
            return this.lat && this.lng
                ? `https://www.google.com/maps?q=${this.lat},${this.lng}`
                : '#';
        },
        useMyLocation() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }
            this.locating = true;
            navigator.geolocation.getCurrentPosition(
                pos => {
                    this.lat = pos.coords.latitude.toFixed(7);
                    this.lng = pos.coords.longitude.toFixed(7);
                    this.locating = false;
                },
                err => {
                    alert('Could not get GPS: ' + (err.message || 'unknown error'));
                    this.locating = false;
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }
    };
}
</script>