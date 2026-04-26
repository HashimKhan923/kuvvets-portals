<form method="POST" action="{{ route('employee.profile.update') }}">
    @csrf
    @method('PUT')

    {{-- ═══════ Contact ═══════ --}}
    <div class="form-card">
        <div class="form-section-title"><i class="fa-solid fa-address-book"></i>Contact Information</div>

        <div class="form-row">
            <div class="field">
                <label>Work Email <span class="hint">(managed by HR)</span></label>
                <input type="email" value="{{ $employee->work_email }}" disabled>
            </div>
            <div class="field">
                <label>Personal Email</label>
                <input type="email" name="personal_email" value="{{ old('personal_email', $employee->personal_email) }}" placeholder="you@example.com">
                @error('personal_email') <div class="field-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-row triple">
            <div class="field">
                <label>Work Phone <span class="hint">(HR)</span></label>
                <input type="tel" value="{{ $employee->work_phone }}" disabled>
            </div>
            <div class="field">
                <label>Personal Phone</label>
                <input type="tel" name="personal_phone" value="{{ old('personal_phone', $employee->personal_phone) }}" placeholder="+92 3xx xxxxxxx">
            </div>
            <div class="field">
                <label>WhatsApp</label>
                <input type="tel" name="whatsapp" value="{{ old('whatsapp', $employee->whatsapp) }}" placeholder="+92 3xx xxxxxxx">
            </div>
        </div>
    </div>

    {{-- ═══════ Personal ═══════ --}}
    <div class="form-card">
        <div class="form-section-title"><i class="fa-solid fa-user"></i>Personal Details</div>

        <div class="form-row">
            <div class="field">
                <label>Marital Status</label>
                <select name="marital_status">
                    <option value="">— Select —</option>
                    @foreach(['single'=>'Single','married'=>'Married','divorced'=>'Divorced','widowed'=>'Widowed'] as $v=>$l)
                        <option value="{{ $v }}" {{ old('marital_status', $employee->marital_status) === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Religion</label>
                <input type="text" name="religion" value="{{ old('religion', $employee->religion) }}" placeholder="Optional">
            </div>
        </div>
    </div>

    {{-- ═══════ Address ═══════ --}}
    <div class="form-card">
        <div class="form-section-title"><i class="fa-solid fa-location-dot"></i>Address</div>

        <div class="form-row full">
            <div class="field">
                <label>Current Address</label>
                <textarea name="current_address" placeholder="House, street, area">{{ old('current_address', $employee->current_address) }}</textarea>
            </div>
        </div>
        <div class="form-row">
            <div class="field">
                <label>Current City</label>
                <input type="text" name="current_city" value="{{ old('current_city', $employee->current_city) }}" placeholder="Karachi">
            </div>
            <div class="field">
                <label>Province</label>
                <select name="province">
                    <option value="">— Select —</option>
                    @foreach(['Sindh','Punjab','KPK','Balochistan','Islamabad','Gilgit-Baltistan','Azad Kashmir'] as $p)
                        <option value="{{ $p }}" {{ old('province', $employee->province) === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row full">
            <div class="field">
                <label>Permanent Address <span class="hint">(if different from current)</span></label>
                <textarea name="permanent_address" placeholder="Permanent address">{{ old('permanent_address', $employee->permanent_address) }}</textarea>
            </div>
        </div>
        <div class="form-row full">
            <div class="field">
                <label>Permanent City</label>
                <input type="text" name="permanent_city" value="{{ old('permanent_city', $employee->permanent_city) }}">
            </div>
        </div>
    </div>

    {{-- ═══════ Emergency Contact ═══════ --}}
    <div class="form-card">
        <div class="form-section-title"><i class="fa-solid fa-phone" style="color:var(--red) !important;"></i>Emergency Contact</div>

        <div class="form-row triple">
            <div class="field">
                <label>Full Name</label>
                <input type="text" name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}" placeholder="Contact person's name">
            </div>
            <div class="field">
                <label>Relation</label>
                <input type="text" name="emergency_contact_relation" value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation) }}" placeholder="e.g. Father, Spouse">
            </div>
            <div class="field">
                <label>Phone</label>
                <input type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}" placeholder="+92 3xx xxxxxxx">
            </div>
        </div>
    </div>

    {{-- ═══════ Bank ═══════ --}}
    <div class="form-card">
        <div class="form-section-title"><i class="fa-solid fa-building-columns"></i>Bank Information</div>
        <div style="font-size:11.5px;color:var(--text-muted);margin-bottom:16px;padding:10px 12px;background:var(--yellow-bg);border:1px solid var(--yellow-border);border-radius:8px;">
            <i class="fa-solid fa-circle-info" style="color:var(--yellow);margin-right:6px;"></i>
            This is where your salary will be credited. Double-check all details.
        </div>

        <div class="form-row">
            <div class="field">
                <label>Bank Name</label>
                <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}" placeholder="e.g. Meezan Bank">
            </div>
            <div class="field">
                <label>Branch</label>
                <input type="text" name="bank_branch" value="{{ old('bank_branch', $employee->bank_branch) }}" placeholder="Branch name / code">
            </div>
        </div>
        <div class="form-row">
            <div class="field">
                <label>Account Number</label>
                <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $employee->bank_account_no) }}" placeholder="Account number">
            </div>
            <div class="field">
                <label>IBAN</label>
                <input type="text" name="bank_iban" value="{{ old('bank_iban', $employee->bank_iban) }}" placeholder="PK36SCBL0000001123456702" style="font-family:'Space Grotesk',monospace;text-transform:uppercase;" maxlength="34">
            </div>
        </div>
    </div>

    {{-- ═══════ Actions ═══════ --}}
    <div class="form-card" style="margin-bottom:0;">
        <div class="form-actions" style="border:0;margin:0;padding:0;">
            <a href="{{ route('employee.profile.index', ['tab'=>'overview']) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-check"></i> Save Changes
            </button>
        </div>
    </div>
</form>