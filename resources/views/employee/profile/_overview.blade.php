{{-- ═══════ Completion breakdown card ═══════ --}}
@if($completion['overall_pct'] < 100)
<div class="info-card">
    <div class="info-card-hd">
        <div>
            <div class="info-card-title"><i class="fa-solid fa-chart-simple"></i>Profile Completion</div>
            <div class="info-card-sub">Fill out each section to get your profile to 100%</div>
        </div>
        <a href="{{ route('employee.profile.index', ['tab'=>'edit']) }}" class="btn btn-primary" style="padding:8px 14px;font-size:12px;">
            <i class="fa-solid fa-pen"></i> Complete
        </a>
    </div>
    <div style="padding:14px;">
        <div class="completion-grid">
            @foreach($completion['sections'] as $name => $sec)
                <div class="completion-item {{ $sec['pct'] === 100 ? 'done' : '' }}">
                    <div class="completion-item-hd">
                        <div class="completion-item-name">{{ $name }}</div>
                        <div class="completion-item-pct">{{ $sec['filled'] }}/{{ $sec['total'] }}</div>
                    </div>
                    <div class="completion-item-bar">
                        <div class="completion-item-fill" style="width:{{ $sec['pct'] }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ═══════ Personal Information ═══════ --}}
<div class="info-card">
    <div class="info-card-hd">
        <div class="info-card-title"><i class="fa-solid fa-user"></i>Personal Information</div>
    </div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-lbl">Full name</div>
            <div class="info-val">{{ $employee->full_name }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Father's name</div>
            <div class="info-val {{ !$employee->father_name ? 'empty' : '' }}">{{ $employee->father_name ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Date of birth</div>
            <div class="info-val locked">{{ $employee->date_of_birth?->format('F j, Y') ?? 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Gender</div>
            <div class="info-val locked">{{ $employee->gender ? ucfirst($employee->gender) : 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Marital status</div>
            <div class="info-val {{ !$employee->marital_status ? 'empty' : '' }}">{{ $employee->marital_status ? ucfirst($employee->marital_status) : 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Religion</div>
            <div class="info-val {{ !$employee->religion ? 'empty' : '' }}">{{ $employee->religion ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Nationality</div>
            <div class="info-val locked">{{ $employee->nationality ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">CNIC</div>
            <div class="info-val locked">{{ $employee->cnic ?: 'Not set' }}</div>
        </div>
    </div>
</div>

{{-- ═══════ Contact Information ═══════ --}}
<div class="info-card">
    <div class="info-card-hd">
        <div class="info-card-title"><i class="fa-solid fa-address-book"></i>Contact Information</div>
    </div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-lbl">Work email</div>
            <div class="info-val locked">{{ $employee->work_email ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Personal email</div>
            <div class="info-val {{ !$employee->personal_email ? 'empty' : '' }}">{{ $employee->personal_email ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Work phone</div>
            <div class="info-val locked">{{ $employee->work_phone ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Personal phone</div>
            <div class="info-val {{ !$employee->personal_phone ? 'empty' : '' }}">{{ $employee->personal_phone ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">WhatsApp</div>
            <div class="info-val {{ !$employee->whatsapp ? 'empty' : '' }}">{{ $employee->whatsapp ?: 'Not set' }}</div>
        </div>
    </div>
</div>

{{-- ═══════ Address ═══════ --}}
<div class="info-card">
    <div class="info-card-hd">
        <div class="info-card-title"><i class="fa-solid fa-location-dot"></i>Address</div>
    </div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-lbl">Current address</div>
            <div class="info-val {{ !$employee->current_address ? 'empty' : '' }}">{{ $employee->current_address ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Current city</div>
            <div class="info-val {{ !$employee->current_city ? 'empty' : '' }}">{{ $employee->current_city ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Permanent address</div>
            <div class="info-val {{ !$employee->permanent_address ? 'empty' : '' }}">{{ $employee->permanent_address ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Permanent city / Province</div>
            <div class="info-val {{ !$employee->permanent_city && !$employee->province ? 'empty' : '' }}">
                {{ $employee->permanent_city }}@if($employee->permanent_city && $employee->province),@endif {{ $employee->province ?: ($employee->permanent_city ? '' : 'Not set') }}
            </div>
        </div>
    </div>
</div>

{{-- ═══════ Emergency Contact ═══════ --}}
<div class="info-card">
    <div class="info-card-hd">
        <div class="info-card-title"><i class="fa-solid fa-phone" style="color:var(--red) !important;"></i>Emergency Contact</div>
    </div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-lbl">Name</div>
            <div class="info-val {{ !$employee->emergency_contact_name ? 'empty' : '' }}">{{ $employee->emergency_contact_name ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Relation</div>
            <div class="info-val {{ !$employee->emergency_contact_relation ? 'empty' : '' }}">{{ $employee->emergency_contact_relation ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Phone</div>
            <div class="info-val {{ !$employee->emergency_contact_phone ? 'empty' : '' }}">{{ $employee->emergency_contact_phone ?: 'Not set' }}</div>
        </div>
    </div>
</div>

{{-- ═══════ Bank Information ═══════ --}}
<div class="info-card">
    <div class="info-card-hd">
        <div class="info-card-title"><i class="fa-solid fa-building-columns"></i>Bank Information</div>
    </div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-lbl">Bank name</div>
            <div class="info-val {{ !$employee->bank_name ? 'empty' : '' }}">{{ $employee->bank_name ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Branch</div>
            <div class="info-val {{ !$employee->bank_branch ? 'empty' : '' }}">{{ $employee->bank_branch ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Account number</div>
            <div class="info-val {{ !$employee->bank_account_no ? 'empty' : '' }}">{{ $employee->bank_account_no ?: 'Not set' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">IBAN</div>
            <div class="info-val {{ !$employee->bank_iban ? 'empty' : '' }}" style="font-family:'Space Grotesk',monospace;">{{ $employee->bank_iban ?: 'Not set' }}</div>
        </div>
    </div>
</div>