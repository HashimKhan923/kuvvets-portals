@php $isEdit = isset($employee) && $employee !== null; @endphp

{{-- Validation Errors --}}
@if($errors->any())
<div class="error-box" style="margin-bottom:20px;">
    <div class="error-box-title">
        <i class="fa-solid fa-triangle-exclamation"></i> Please fix these errors:
    </div>
    <ul style="padding-left:16px;">
        @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Tab Navigation --}}
<div class="tab-nav">
    <button type="button" class="tab-btn" id="tab-personal"   onclick="switchTab('personal')">
        <i class="fa-solid fa-user"></i> Personal Info
    </button>
    <button type="button" class="tab-btn" id="tab-employment" onclick="switchTab('employment')">
        <i class="fa-solid fa-briefcase"></i> Employment
    </button>
    <button type="button" class="tab-btn" id="tab-contact"    onclick="switchTab('contact')">
        <i class="fa-solid fa-location-dot"></i> Contact & Address
    </button>
    <button type="button" class="tab-btn" id="tab-financial"  onclick="switchTab('financial')">
        <i class="fa-solid fa-money-bill-wave"></i> Financial
    </button>
</div>

{{-- ══ TAB: PERSONAL ══════════════════════════════════ --}}
<div id="pane-personal">
<div class="card" style="margin-bottom:20px;">
    <div class="form-section">
        <i class="fa-solid fa-user-circle"></i> Personal Information
    </div>

    {{-- Avatar --}}
    <div style="display:flex;align-items:center;gap:20px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--border);">
        <img id="avatarPreview"
             src="{{ $isEdit && $employee->avatar ? asset('storage/'.$employee->avatar) : 'https://ui-avatars.com/api/?name=Employee&background=C2531B&color=fff&bold=true' }}"
             class="avatar avatar-xl">
        <div>
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:8px;">Profile Photo</div>
            <label class="btn btn-secondary" style="cursor:pointer;">
                <i class="fa-solid fa-upload"></i> Upload Photo
                <input type="file" name="avatar" id="avatarInput" accept="image/*"
                       style="display:none;" onchange="previewAvatar(this)">
            </label>
            <div style="font-size:10px;color:var(--text-muted);margin-top:6px;">Max 2MB · JPG, PNG</div>
        </div>
    </div>

    <div class="grid-3" style="gap:16px;">
        @include('employees._field', ['name'=>'employee_id', 'label'=>'Employee ID', 'type'=>'text', 'required'=>true,  'value'=>old('employee_id', $nextId),                                     'placeholder'=>'KVT-0001'])
        @include('employees._field', ['name'=>'first_name',  'label'=>'First Name',  'type'=>'text', 'required'=>true,  'value'=>old('first_name',  $employee?->first_name),                       'placeholder'=>'Muhammad'])
        @include('employees._field', ['name'=>'last_name',   'label'=>'Last Name',   'type'=>'text', 'required'=>true,  'value'=>old('last_name',   $employee?->last_name),                        'placeholder'=>'Ali'])
        @include('employees._field', ['name'=>'father_name', 'label'=>'Father Name', 'type'=>'text', 'required'=>false, 'value'=>old('father_name', $employee?->father_name),                      'placeholder'=>'Ahmad Ali'])
        @include('employees._field', ['name'=>'cnic',        'label'=>'CNIC',        'type'=>'text', 'required'=>false, 'value'=>old('cnic',        $employee?->cnic),                             'placeholder'=>'42201-1234567-1'])
        @include('employees._field', ['name'=>'cnic_expiry', 'label'=>'CNIC Expiry', 'type'=>'date', 'required'=>false, 'value'=>old('cnic_expiry', $employee?->cnic_expiry)])
        @include('employees._field', ['name'=>'date_of_birth','label'=>'Date of Birth','type'=>'date','required'=>false,'value'=>old('date_of_birth',$employee?->date_of_birth?->format('Y-m-d'))])

        <div>
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
                <option value="">Select</option>
                @foreach(['male'=>'Male','female'=>'Female','other'=>'Other'] as $v => $l)
                    <option value="{{ $v }}" {{ old('gender', $employee?->gender) == $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Marital Status</label>
            <select name="marital_status" class="form-select">
                <option value="">Select</option>
                @foreach(['single'=>'Single','married'=>'Married','divorced'=>'Divorced','widowed'=>'Widowed'] as $v => $l)
                    <option value="{{ $v }}" {{ old('marital_status', $employee?->marital_status) == $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        @include('employees._field', ['name'=>'nationality','label'=>'Nationality','type'=>'text','required'=>false,'value'=>old('nationality',$employee?->nationality ?? 'Pakistani'),'placeholder'=>'Pakistani'])
        @include('employees._field', ['name'=>'religion',   'label'=>'Religion',   'type'=>'text','required'=>false,'value'=>old('religion',   $employee?->religion),                'placeholder'=>'Islam'])
    </div>
</div>
</div>

{{-- ══ TAB: EMPLOYMENT ════════════════════════════════ --}}
<div id="pane-employment" style="display:none;">
<div class="card" style="margin-bottom:20px;">
    <div class="form-section">
        <i class="fa-solid fa-briefcase"></i> Employment Details
    </div>
    <div class="grid-3" style="gap:16px;">

        <div>
            <label class="form-label">Department</label>
            <select name="department_id" class="form-select">
                <option value="">Select Department</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ old('department_id', $employee?->department_id) == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Designation</label>
            <select name="designation_id" class="form-select">
                <option value="">Select Designation</option>
                <!-- @foreach($designations as $desig)
                    <option value="{{ $desig->id }}" {{ old('designation_id', $employee?->designation_id) == $desig->id ? 'selected' : '' }}>
                        {{ $desig->title }}
                    </option>
                @endforeach -->
                /// i need complete 
                <option value=""></option>
            </select>
        </div>

        <div>
            <label class="form-label">Reporting Manager</label>
            <select name="manager_id" class="form-select">
                <option value="">None</option>
                @foreach($managers as $mgr)
                    <option value="{{ $mgr->id }}" {{ old('manager_id', $employee?->manager_id) == $mgr->id ? 'selected' : '' }}>
                        {{ $mgr->full_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Employment Type <span style="color:var(--red);">*</span></label>
            <select name="employment_type" required class="form-select">
                @foreach(['permanent'=>'Permanent','contract'=>'Contract','probationary'=>'Probationary','part_time'=>'Part Time','internship'=>'Internship','daily_wages'=>'Daily Wages'] as $v => $l)
                    <option value="{{ $v }}" {{ old('employment_type', $employee?->employment_type ?? 'permanent') == $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Employment Status</label>
            <select name="employment_status" class="form-select">
                @foreach(['active'=>'Active','resigned'=>'Resigned','terminated'=>'Terminated','retired'=>'Retired','on_leave'=>'On Leave','absconded'=>'Absconded'] as $v => $l)
                    <option value="{{ $v }}" {{ old('employment_status', $employee?->employment_status ?? 'active') == $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="form-label">Probation Status</label>
            <select name="probation_status" class="form-select">
                @foreach(['on_probation'=>'On Probation','confirmed'=>'Confirmed','extended'=>'Extended','terminated'=>'Terminated'] as $v => $l)
                    <option value="{{ $v }}" {{ old('probation_status', $employee?->probation_status ?? 'on_probation') == $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        @include('employees._field', ['name'=>'joining_date',       'label'=>'Joining Date',       'type'=>'date',  'required'=>false, 'value'=>old('joining_date',       $employee?->joining_date?->format('Y-m-d'))])
        @include('employees._field', ['name'=>'confirmation_date',  'label'=>'Confirmation Date',  'type'=>'date',  'required'=>false, 'value'=>old('confirmation_date',  $employee?->confirmation_date?->format('Y-m-d'))])
        @include('employees._field', ['name'=>'probation_end_date', 'label'=>'Probation End Date', 'type'=>'date',  'required'=>false, 'value'=>old('probation_end_date', $employee?->probation_end_date?->format('Y-m-d'))])
        @include('employees._field', ['name'=>'work_email',         'label'=>'Work Email',         'type'=>'email', 'required'=>false, 'value'=>old('work_email',         $employee?->work_email),         'placeholder'=>'emp@kuvvet.com'])
        @include('employees._field', ['name'=>'work_phone',         'label'=>'Work Phone',         'type'=>'text',  'required'=>false, 'value'=>old('work_phone',         $employee?->work_phone),         'placeholder'=>'+92-21-0000000'])
    </div>

    @if(!$isEdit)
    <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);display:flex;align-items:center;gap:10px;">
        <input type="checkbox" name="create_user_account" id="createAccount" value="1"
               style="accent-color:var(--accent);cursor:pointer;">
        <label for="createAccount" style="font-size:13px;color:var(--text-secondary);cursor:pointer;">
            Create portal login account for this employee
            <span style="font-size:11px;color:var(--text-muted);">(Uses work email · Default: Kuvvet@{{ date('Y') }}!)</span>
        </label>
    </div>
    @endif
</div>
</div>

{{-- ══ TAB: CONTACT ════════════════════════════════════ --}}
<div id="pane-contact" style="display:none;">
<div class="card" style="margin-bottom:20px;">
    <div class="form-section">
        <i class="fa-solid fa-location-dot"></i> Contact & Address
    </div>
    <div class="grid-3" style="gap:16px;margin-bottom:16px;">
        @include('employees._field', ['name'=>'personal_email','label'=>'Personal Email','type'=>'email','required'=>false,'value'=>old('personal_email',$employee?->personal_email),'placeholder'=>'personal@gmail.com'])
        @include('employees._field', ['name'=>'personal_phone','label'=>'Personal Phone','type'=>'text', 'required'=>false,'value'=>old('personal_phone',$employee?->personal_phone),'placeholder'=>'+92-300-0000000'])
        @include('employees._field', ['name'=>'whatsapp',      'label'=>'WhatsApp',      'type'=>'text', 'required'=>false,'value'=>old('whatsapp',      $employee?->whatsapp),      'placeholder'=>'+92-300-0000000'])
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:16px;margin-bottom:16px;">
        <div>
            <label class="form-label">Current Address</label>
            <textarea name="current_address" rows="2" class="form-textarea">{{ old('current_address', $employee?->current_address) }}</textarea>
        </div>
        @include('employees._field', ['name'=>'current_city','label'=>'City','type'=>'text','required'=>false,'value'=>old('current_city',$employee?->current_city),'placeholder'=>'Karachi'])
        <div>
            <label class="form-label">Province</label>
            <select name="province" class="form-select">
                <option value="">Select Province</option>
                @foreach(['Sindh','Punjab','KPK','Balochistan','AJK','Gilgit-Baltistan','ICT'] as $prov)
                    <option value="{{ $prov }}" {{ old('province', $employee?->province) == $prov ? 'selected' : '' }}>{{ $prov }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div style="border-top:1px solid var(--border);padding-top:16px;">
        <div class="section-label">Emergency Contact</div>
        <div class="grid-3" style="gap:16px;">
            @include('employees._field', ['name'=>'emergency_contact_name',    'label'=>'Contact Name','type'=>'text','required'=>false,'value'=>old('emergency_contact_name',    $employee?->emergency_contact_name),    'placeholder'=>'Ahmad Khan'])
            @include('employees._field', ['name'=>'emergency_contact_relation','label'=>'Relation',    'type'=>'text','required'=>false,'value'=>old('emergency_contact_relation',$employee?->emergency_contact_relation),'placeholder'=>'Father / Spouse'])
            @include('employees._field', ['name'=>'emergency_contact_phone',   'label'=>'Phone',       'type'=>'text','required'=>false,'value'=>old('emergency_contact_phone',   $employee?->emergency_contact_phone),   'placeholder'=>'+92-300-0000000'])
        </div>
    </div>
</div>
</div>

{{-- ══ TAB: FINANCIAL ══════════════════════════════════ --}}
<div id="pane-financial" style="display:none;">
<div class="card" style="margin-bottom:20px;">
    <div class="form-section">
        <i class="fa-solid fa-money-bill-wave"></i> Financial & Payroll
    </div>
    <div class="grid-3" style="gap:16px;">
        @include('employees._field', ['name'=>'basic_salary',    'label'=>'Basic Salary (PKR)','type'=>'number','required'=>false,'value'=>old('basic_salary',   $employee?->basic_salary),   'placeholder'=>'50000'])
        @include('employees._field', ['name'=>'bank_name',       'label'=>'Bank Name',         'type'=>'text',  'required'=>false,'value'=>old('bank_name',       $employee?->bank_name),       'placeholder'=>'HBL / UBL / MCB…'])
        @include('employees._field', ['name'=>'bank_account_no', 'label'=>'Account Number',    'type'=>'text',  'required'=>false,'value'=>old('bank_account_no', $employee?->bank_account_no), 'placeholder'=>'0123-4567890-001'])
        @include('employees._field', ['name'=>'bank_iban',       'label'=>'IBAN',              'type'=>'text',  'required'=>false,'value'=>old('bank_iban',       $employee?->bank_iban),       'placeholder'=>'PK36SCBL0000001123456702'])
        @include('employees._field', ['name'=>'bank_branch',     'label'=>'Bank Branch',       'type'=>'text',  'required'=>false,'value'=>old('bank_branch',     $employee?->bank_branch),     'placeholder'=>'Clifton, Karachi'])
        @include('employees._field', ['name'=>'eobi_number',     'label'=>'EOBI Number',       'type'=>'text',  'required'=>false,'value'=>old('eobi_number',     $employee?->eobi_number),     'placeholder'=>'EOBI-XXXXX'])
        @include('employees._field', ['name'=>'pessi_number',    'label'=>'PESSI / SESSI No.', 'type'=>'text',  'required'=>false,'value'=>old('pessi_number',    $employee?->pessi_number),    'placeholder'=>'SESSI-XXXXX'])
    </div>
</div>
</div>

{{-- Footer Buttons --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-top:4px;">

    {{-- Left: Cancel (always) --}}
    <a href="{{ route('employees.index') }}" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Cancel
    </a>

    <div style="display:flex;gap:10px;align-items:center;">

        {{-- Back button (hidden on first tab) --}}
        <button type="button" id="btn-back" class="btn btn-secondary"
                onclick="goTab(-1)" style="display:none;">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>

        {{-- Next button (hidden on last tab) --}}
        <button type="button" id="btn-next" class="btn btn-primary"
                onclick="goTab(1)">
            Next <i class="fa-solid fa-arrow-right"></i>
        </button>

        {{-- Submit button (shown only on last tab) --}}
        <button type="submit" id="btn-submit" class="btn btn-primary"
                style="display:none;">
            <i class="fa-solid fa-{{ $isEdit ? 'floppy-disk' : 'user-plus' }}"></i>
            {{ $isEdit ? 'Save Changes' : 'Create Employee' }}
        </button>

    </div>
</div>

@push('scripts')
<script>
var tabs       = ['personal', 'employment', 'contact', 'financial'];
var currentTab = 0;

function switchTab(active) {
    tabs.forEach(function(t) {
        document.getElementById('pane-' + t).style.display = t === active ? 'block' : 'none';
        document.getElementById('tab-' + t).classList.toggle('active', t === active);
    });
    currentTab = tabs.indexOf(active);
    updateFooterButtons();
}

function goTab(direction) {
    var next = currentTab + direction;
    if (next >= 0 && next < tabs.length) {
        switchTab(tabs[next]);
    }
}

function updateFooterButtons() {
    var isFirst = currentTab === 0;
    var isLast  = currentTab === tabs.length - 1;

    document.getElementById('btn-back').style.display   = isFirst ? 'none'  : 'inline-flex';
    document.getElementById('btn-next').style.display   = isLast  ? 'none'  : 'inline-flex';
    document.getElementById('btn-submit').style.display = isLast  ? 'inline-flex' : 'none';
}

switchTab('personal');

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

var cnicInput = document.querySelector('[name="cnic"]');
if (cnicInput) {
    cnicInput.addEventListener('input', function() {
        var v = this.value.replace(/\D/g, '');
        if (v.length > 5)  v = v.slice(0, 5)  + '-' + v.slice(5);
        if (v.length > 13) v = v.slice(0, 13) + '-' + v.slice(13);
        this.value = v.slice(0, 15);
    });
}
</script>
@endpush