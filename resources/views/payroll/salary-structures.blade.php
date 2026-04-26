@extends('layouts.app')
@section('title', 'Salary Structures')
@section('page-title', 'Salary Structures')
@section('breadcrumb', 'Payroll · Salary Structures')

@section('content')

<div class="card card-sm" style="margin-bottom:18px;">
    <div class="toolbar">
        <form method="GET" action="{{ route('payroll.salary-structures') }}" class="toolbar" style="flex:1;">
            <select name="department" class="form-select" style="min-width:160px;">
                <option value="">All Departments</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>
                        {{ $d->name }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </form>
        <div style="font-size:12px;color:var(--text-muted);">
            {{ $employees->count() }} employees ·
            {{ $employees->filter(fn($e) => $e->salaryStructure)->count() }} have structures
        </div>
    </div>
</div>

<div style="display:flex;flex-direction:column;gap:10px;">
    @foreach($employees as $emp)
    @php $str = $emp->salaryStructure; @endphp
    <div class="card card-flush" style="transition:border-color .2s;"
         onmouseover="this.style.borderColor='var(--accent-border)'"
         onmouseout="this.style.borderColor='var(--border)'">

        {{-- Employee Row Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:14px 20px;background:var(--bg-muted);cursor:pointer;"
             onclick="toggleStructure({{ $emp->id }})">
            <div class="td-employee">
                <img src="{{ $emp->avatar_url }}" class="avatar avatar-md">
                <div>
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);">
                        {{ $emp->full_name }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        {{ $emp->employee_id }} ·
                        {{ $emp->department?->name ?? 'No dept' }} ·
                        {{ $emp->designation?->title ?? '—' }}
                    </div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                @if($str)
                <div style="text-align:right;">
                    <div style="font-size:10px;color:var(--text-muted);">Gross</div>
                    <div style="font-size:15px;font-weight:700;color:var(--accent);">
                        PKR {{ number_format($str->gross_salary) }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:10px;color:var(--text-muted);">Basic</div>
                    <div style="font-size:13px;font-weight:500;color:var(--text-secondary);">
                        {{ number_format($str->basic_salary) }}
                    </div>
                </div>
                @else
                <span class="badge badge-red">No Structure</span>
                @endif
                <i class="fa-solid fa-chevron-down" id="arrow-{{ $emp->id }}"
                   style="font-size:11px;color:var(--text-muted);transition:transform .25s;"></i>
            </div>
        </div>

        {{-- Collapsible Form --}}
        <div id="struct-{{ $emp->id }}" style="display:none;padding:24px;border-top:1px solid var(--border);">
            <form method="POST" action="{{ route('payroll.salary-structures.save', $emp) }}">
                @csrf

                {{-- Basic Info --}}
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;">
                    <div>
                        <label class="form-label">Basic Salary (PKR) <span style="color:var(--red);">*</span></label>
                        <input type="number" name="basic_salary" required
                               value="{{ old('basic_salary', $str?->basic_salary ?? 0) }}"
                               class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Effective From <span style="color:var(--red);">*</span></label>
                        <input type="date" name="effective_from" required
                               value="{{ old('effective_from', $str?->effective_from?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                               class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Structure Name</label>
                        <input type="text" name="structure_name"
                               value="{{ old('structure_name', $str?->structure_name ?? 'Standard') }}"
                               class="form-input">
                    </div>
                </div>

                {{-- Allowances --}}
                <div class="form-section" style="color:var(--green);">
                    <i class="fa-solid fa-plus-circle"></i> Allowances
                </div>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
                    @foreach([
                        'house_rent'       => 'House Rent (HRA)',
                        'medical'          => 'Medical',
                        'conveyance'       => 'Conveyance',
                        'fuel'             => 'Fuel',
                        'utility'          => 'Utility',
                        'meal'             => 'Meal',
                        'special_allowance'=> 'Special Allowance',
                        'other_allowance'  => 'Other Allowance',
                    ] as $n => $l)
                    <div>
                        <label class="form-label">{{ $l }}</label>
                        <input type="number" name="{{ $n }}"
                               value="{{ old($n, $str?->{$n} ?? 0) }}"
                               min="0" class="form-input">
                    </div>
                    @endforeach
                </div>

                {{-- Deductions --}}
                <div class="form-section" style="color:var(--red);">
                    <i class="fa-solid fa-minus-circle"></i> Fixed Deductions
                </div>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
                    @foreach([
                        'loan_deduction'  => 'Monthly Loan',
                        'other_deduction' => 'Other Deductions',
                        'tax_rebate'      => 'Tax Rebate/Year',
                    ] as $n => $l)
                    <div>
                        <label class="form-label">{{ $l }}</label>
                        <input type="number" name="{{ $n }}"
                               value="{{ old($n, $str?->{$n} ?? 0) }}"
                               min="0" class="form-input">
                    </div>
                    @endforeach
                    <div style="display:flex;align-items:center;padding-top:22px;">
                        <label style="display:flex;align-items:center;gap:7px;font-size:12px;
                                       color:var(--text-secondary);cursor:pointer;">
                            <input type="checkbox" name="tax_exempt" value="1"
                                   {{ old('tax_exempt', $str?->tax_exempt) ? 'checked' : '' }}
                                   style="accent-color:var(--accent);">
                            Tax Exempt
                        </label>
                    </div>
                </div>

                {{-- Live Calculator --}}
                <div style="display:flex;gap:24px;align-items:center;padding:16px;margin-bottom:16px;
                            background:var(--bg-muted);border:1px solid var(--border);border-radius:8px;">
                    <div>
                        <div style="font-size:10px;color:var(--text-muted);margin-bottom:3px;">Estimated Gross</div>
                        <div id="liveGross-{{ $emp->id }}"
                             style="font-size:20px;font-weight:700;color:var(--accent);">PKR 0</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:var(--text-muted);margin-bottom:3px;">Est. Tax/mo</div>
                        <div id="liveTax-{{ $emp->id }}"
                             style="font-size:17px;font-weight:700;color:var(--yellow);">PKR 0</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:var(--text-muted);margin-bottom:3px;">Est. Net/mo</div>
                        <div id="liveNet-{{ $emp->id }}"
                             style="font-size:17px;font-weight:700;color:var(--green);">PKR 0</div>
                    </div>
                </div>

                <div style="display:flex;gap:10px;align-items:center;">
                    <textarea name="notes" rows="1" placeholder="Notes (optional)"
                              class="form-textarea" style="flex:1;">{{ old('notes', $str?->notes) }}</textarea>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Structure
                    </button>
                </div>
            </form>
        </div>

    </div>
    @endforeach
</div>

@push('scripts')
<script>
function toggleStructure(id) {
    var pane  = document.getElementById('struct-' + id);
    var arrow = document.getElementById('arrow-' + id);
    var open  = pane.style.display !== 'none';
    pane.style.display    = open ? 'none' : 'block';
    arrow.style.transform = open ? 'rotate(0deg)' : 'rotate(180deg)';
}

document.querySelectorAll('form').forEach(function(form) {
    form.querySelectorAll('input[type="number"]').forEach(function(f) {
        f.addEventListener('input', function() { updateLive(form); });
    });
});

function updateLive(form) {
    var fields = ['basic_salary','house_rent','medical','conveyance','fuel','utility','meal','special_allowance','other_allowance'];
    var gross  = 0;
    fields.forEach(function(n) {
        var el = form.querySelector('[name="' + n + '"]');
        gross += el ? (parseFloat(el.value) || 0) : 0;
    });

    var rebateEl  = form.querySelector('[name="tax_rebate"]');
    var taxRebate = rebateEl ? (parseFloat(rebateEl.value) || 0) : 0;

    var annualGross = gross * 12;
    var eobi        = gross * 0.6 * 0.01;
    var taxable     = Math.max(0, annualGross - eobi * 12 - taxRebate);

    var slabs = [[0,600000,0,0],[600001,1200000,.05,0],[1200001,2200000,.15,30000],
                 [2200001,3200000,.25,180000],[3200001,4100000,.30,430000],[4100001,Infinity,.35,700000]];
    var tax = 0;
    for (var i = 0; i < slabs.length; i++) {
        if (taxable >= slabs[i][0] && taxable <= slabs[i][1]) {
            tax = slabs[i][3] + (taxable - (slabs[i][0] - 1)) * slabs[i][2];
            break;
        }
    }

    var net   = gross - tax / 12 - eobi - Math.min(gross * 0.009, 400);
    var match = form.action.match(/salary-structures\/(\d+)/);
    if (!match) return;
    var id = match[1];

    var grossEl = document.getElementById('liveGross-' + id);
    var taxEl   = document.getElementById('liveTax-'   + id);
    var netEl   = document.getElementById('liveNet-'   + id);
    if (grossEl) grossEl.textContent = 'PKR ' + Math.round(gross).toLocaleString();
    if (taxEl)   taxEl.textContent   = 'PKR ' + Math.round(tax / 12).toLocaleString();
    if (netEl)   netEl.textContent   = 'PKR ' + Math.round(net).toLocaleString();
}
</script>
@endpush

@endsection