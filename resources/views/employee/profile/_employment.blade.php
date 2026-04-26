<div style="padding:14px 16px;background:var(--blue-bg);border:1px solid var(--blue-border);border-radius:12px;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <i class="fa-solid fa-circle-info" style="color:var(--blue);font-size:16px;"></i>
    <span style="font-size:12.5px;color:var(--blue);">These details are managed by HR. To make changes, please contact your HR department.</span>
</div>

{{-- ═══════ Position ═══════ --}}
<div class="info-card">
    <div class="info-card-hd">
        <div class="info-card-title"><i class="fa-solid fa-briefcase"></i>Position & Organization</div>
    </div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-lbl">Employee ID</div>
            <div class="info-val" style="color:var(--accent);font-family:'Space Grotesk',sans-serif;">{{ $employee->employee_id }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Company</div>
            <div class="info-val">{{ $employee->company?->name ?? '—' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Department</div>
            <div class="info-val">{{ $employee->department?->name ?? '—' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Designation</div>
            <div class="info-val">{{ $employee->designation?->name ?? '—' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Manager</div>
            <div class="info-val {{ !$employee->manager ? 'empty' : '' }}">
                @if($employee->manager)
                    {{ $employee->manager->full_name }}
                    <span style="font-size:11px;color:var(--text-muted);font-weight:500;">({{ $employee->manager->employee_id }})</span>
                @else
                    Not assigned
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Employment type</div>
            <div class="info-val">{{ ucfirst(str_replace('_',' ',$employee->employment_type)) }}</div>
        </div>
    </div>
</div>

{{-- ═══════ Dates ═══════ --}}
<div class="info-card">
    <div class="info-card-hd">
        <div class="info-card-title"><i class="fa-solid fa-calendar-days"></i>Important Dates</div>
    </div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-lbl">Joining date</div>
            <div class="info-val">
                {{ $employee->joining_date?->format('F j, Y') ?? '—' }}
                @if($employee->joining_date)
                    <span style="font-size:11px;color:var(--text-muted);font-weight:500;display:block;margin-top:2px;">
                        {{ $employee->joining_date->diffForHumans(['parts' => 2]) }}
                    </span>
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Confirmation date</div>
            <div class="info-val {{ !$employee->confirmation_date ? 'empty' : '' }}">{{ $employee->confirmation_date?->format('F j, Y') ?? 'Pending' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Probation status</div>
            <div class="info-val">
                @php
                    $probationBadge = match($employee->probation_status) {
                        'confirmed'    => 'badge-green',
                        'on_probation' => 'badge-yellow',
                        'extended'     => 'badge-blue',
                        default        => 'badge-gray',
                    };
                @endphp
                <span class="badge {{ $probationBadge }}">{{ ucfirst(str_replace('_',' ',$employee->probation_status)) }}</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-lbl">Date of birth</div>
            <div class="info-val {{ !$employee->date_of_birth ? 'empty' : '' }}">{{ $employee->date_of_birth?->format('F j, Y') ?? 'Not set' }}</div>
        </div>
    </div>
</div>

{{-- ═══════ Statutory ═══════ --}}
@if($employee->eobi_number || $employee->pessi_number || $employee->nssf_number)
<div class="info-card">
    <div class="info-card-hd">
        <div class="info-card-title"><i class="fa-solid fa-shield-halved"></i>Statutory Registrations</div>
    </div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-lbl">EOBI Number</div>
            <div class="info-val {{ !$employee->eobi_number ? 'empty' : '' }}" style="font-family:'Space Grotesk',monospace;">{{ $employee->eobi_number ?: 'Not registered' }}</div>
        </div>
        <div class="info-row">
            <div class="info-lbl">PESSI/SESSI Number</div>
            <div class="info-val {{ !$employee->pessi_number ? 'empty' : '' }}" style="font-family:'Space Grotesk',monospace;">{{ $employee->pessi_number ?: 'Not registered' }}</div>
        </div>
    </div>
</div>
@endif