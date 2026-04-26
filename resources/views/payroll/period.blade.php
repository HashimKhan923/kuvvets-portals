@extends('layouts.app')
@section('title', $period->title)
@section('page-title', $period->title)
@section('breadcrumb', 'Payroll · ' . $period->month_name)

@section('content')

{{-- Period Header --}}
@php $badge = $period->status_badge; @endphp
<div class="card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px;">

        <div>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
                <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};font-size:12px;padding:5px 14px;">
                    {{ ucfirst($period->status) }}
                </span>
                <span style="font-size:12px;color:var(--text-muted);">
                    Payment: {{ $period->payment_date?->format('d M Y') ?? 'Not set' }}
                </span>
                @if($period->approved_by)
                <span style="font-size:11px;color:var(--text-muted);">
                    Approved by {{ $period->approver->name }} · {{ $period->approved_at->format('d M Y') }}
                </span>
                @endif
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                @foreach([
                    ['Employees',   $period->employee_count,                         'accent'],
                    ['Gross',       'PKR ' . number_format($period->total_gross),    'text-primary'],
                    ['Deductions',  'PKR ' . number_format($period->total_deductions),'red'],
                    ['Net Payable', 'PKR ' . number_format($period->total_net),      'green'],
                    ['Income Tax',  'PKR ' . number_format($period->total_tax),      'yellow'],
                    ['EOBI Total',  'PKR ' . number_format($period->total_eobi),     'blue'],
                ] as [$l, $v, $c])
                <div class="detail-block" style="min-width:120px;">
                    <div class="detail-block-label">{{ $l }}</div>
                    <div class="detail-block-value" style="color:var(--{{ $c }});">{{ $v }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end;">
            @if(in_array($period->status, ['draft', 'processing']))
            <form method="POST" action="{{ route('payroll.generate', $period) }}">
                @csrf
                <button type="submit" class="btn btn-blue"
                        onclick="return confirm('Generate payslips for all active employees?')">
                    <i class="fa-solid fa-gears"></i>
                    {{ $period->employee_count > 0 ? 'Re-Generate' : 'Generate Payroll' }}
                </button>
            </form>
            @endif

            @if($period->status === 'processing')
            <form method="POST" action="{{ route('payroll.approve', $period) }}">
                @csrf
                <button type="submit" class="btn btn-warning"
                        onclick="return confirm('Approve this payroll? This will lock all payslips.')">
                    <i class="fa-solid fa-circle-check"></i> Approve Payroll
                </button>
            </form>
            @endif

            @if($period->status === 'approved')
            <div style="display:flex;gap:8px;">
                <form method="POST" action="{{ route('payroll.mark-paid', $period) }}">
                    @csrf
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('Mark as PAID? This cannot be undone.')">
                        <i class="fa-solid fa-money-bill-wave"></i> Mark as Paid
                    </button>
                </form>
                <a href="{{ route('payroll.export', $period) }}" class="btn btn-secondary">
                    <i class="fa-solid fa-file-csv"></i> Bank Export
                </a>
            </div>
            @endif
        </div>

    </div>
</div>

{{-- Tabs --}}
<div class="tab-nav">
    @foreach([
        ['payslips',    'fa-file-invoice-dollar', 'Payslips (' . $payslips->count() . ')'],
        ['adjustments', 'fa-sliders',             'Adjustments (' . $adjustments->count() . ')'],
        ['breakdown',   'fa-chart-pie',           'Tax Breakdown'],
    ] as [$id, $icon, $label])
    <button type="button" class="tab-btn" id="ptab-{{ $id }}"
            onclick="switchPTab('{{ $id }}')">
        <i class="fa-solid {{ $icon }}"></i> {{ $label }}
    </button>
    @endforeach
</div>

{{-- PAYSLIPS TAB --}}
<div id="ppane-payslips">
    <div class="card card-flush">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th class="center">Working Days</th>
                    <th>Gross</th>
                    <th>Deductions</th>
                    <th>Net</th>
                    <th>Tax</th>
                    <th>EOBI</th>
                    <th>Status</th>
                    <th class="center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payslips as $ps)
                @php $psBadge = $ps->status_badge; @endphp
                <tr>
                    <td>
                        <div class="td-employee">
                            <img src="{{ $ps->employee->avatar_url }}" class="avatar avatar-sm">
                            <div>
                                <div class="td-employee name">{{ $ps->employee->full_name }}</div>
                                <div class="td-employee id">{{ $ps->employee->employee_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="muted">{{ $ps->employee->department?->name ?? '—' }}</td>
                    <td class="center">
                        <div style="font-size:12px;font-weight:600;color:var(--text-primary);">
                            {{ $ps->present_days }}/{{ $ps->working_days }}
                        </div>
                        @if($ps->absent_days > 0)
                        <div style="font-size:10px;color:var(--red);">{{ $ps->absent_days }} absent</div>
                        @endif
                        @if($ps->overtime_hours > 0)
                        <div style="font-size:10px;color:var(--yellow);">+{{ $ps->overtime_hours }}h OT</div>
                        @endif
                    </td>
                    <td style="font-size:12px;font-weight:600;color:var(--text-primary);">
                        {{ number_format($ps->gross_salary) }}
                    </td>
                    <td style="font-size:12px;color:var(--red);">
                        {{ number_format($ps->total_deductions) }}
                    </td>
                    <td style="font-size:13px;font-weight:700;color:var(--green);">
                        {{ number_format($ps->net_salary) }}
                    </td>
                    <td style="font-size:12px;color:var(--yellow);">
                        {{ number_format($ps->income_tax) }}
                    </td>
                    <td style="font-size:12px;color:var(--blue);">
                        {{ number_format($ps->eobi_employee) }}
                    </td>
                    <td>
                        <span class="badge" style="background:{{ $psBadge['bg'] }};color:{{ $psBadge['color'] }};border:1px solid {{ $psBadge['border'] }};">
                            {{ ucfirst($ps->status) }}
                        </span>
                    </td>
                    <td class="center">
                        <div style="display:flex;align-items:center;justify-content:center;gap:5px;">
                            <a href="{{ route('payroll.payslip.show', $ps) }}" class="action-btn" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('payroll.payslip.pdf', $ps) }}" class="action-btn" title="PDF">
                                <i class="fa-solid fa-file-pdf"></i>
                            </a>
                            @if(in_array($period->status, ['draft', 'processing']))
                            <form method="POST" action="{{ route('payroll.payslip.recalculate', $ps) }}">
                                @csrf
                                <button type="submit" class="action-btn" title="Recalculate">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10">
                        <div class="empty-state">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            No payslips yet. Click "Generate Payroll" to create them.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($payslips->count())
            <tfoot>
                <tr style="background:var(--bg-muted);border-top:2px solid var(--border-strong);">
                    <td colspan="3" style="padding:10px 16px;font-size:11px;font-weight:700;
                                           color:var(--accent);letter-spacing:.5px;">TOTALS</td>
                    <td style="padding:10px 16px;font-weight:700;color:var(--text-primary);">
                        {{ number_format($payslips->sum('gross_salary')) }}
                    </td>
                    <td style="padding:10px 16px;font-weight:700;color:var(--red);">
                        {{ number_format($payslips->sum('total_deductions')) }}
                    </td>
                    <td style="padding:10px 16px;font-size:14px;font-weight:700;color:var(--green);">
                        {{ number_format($payslips->sum('net_salary')) }}
                    </td>
                    <td style="padding:10px 16px;font-weight:700;color:var(--yellow);">
                        {{ number_format($payslips->sum('income_tax')) }}
                    </td>
                    <td style="padding:10px 16px;font-weight:700;color:var(--blue);">
                        {{ number_format($payslips->sum('eobi_employee')) }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- ADJUSTMENTS TAB --}}
<div id="ppane-adjustments" style="display:none;">
    <div style="display:grid;grid-template-columns:1fr 380px;gap:16px;align-items:start;">

        <div class="card card-flush">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Effect</th>
                        <th>Added By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                    <tr>
                        <td style="font-size:12px;font-weight:600;color:var(--text-primary);">
                            {{ $adj->employee->full_name }}
                        </td>
                        <td><span class="badge badge-accent">{{ ucfirst($adj->type) }}</span></td>
                        <td class="muted">{{ $adj->description }}</td>
                        <td style="font-weight:700;color:{{ $adj->effect === 'add' ? 'var(--green)' : 'var(--red)' }};">
                            {{ $adj->effect === 'add' ? '+' : '-' }}PKR {{ number_format($adj->amount) }}
                        </td>
                        <td>
                            <span class="badge {{ $adj->effect === 'add' ? 'badge-green' : 'badge-red' }}">
                                {{ ucfirst($adj->effect) }}
                            </span>
                        </td>
                        <td class="muted" style="font-size:11px;">{{ $adj->creator->name }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">No adjustments yet.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(in_array($period->status, ['draft', 'processing']))
        <div class="card">
            <div class="form-section">
                <i class="fa-solid fa-plus-circle"></i> Add Adjustment
            </div>
            <form method="POST" action="{{ route('payroll.adjustment', $period) }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:11px;">
                    <div>
                        <label class="form-label">Employee</label>
                        <select name="employee_id" required class="form-select">
                            <option value="">Select Employee</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div>
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                @foreach(['bonus'=>'Bonus','arrears'=>'Arrears','deduction'=>'Deduction','advance'=>'Advance','loan'=>'Loan','other'=>'Other'] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Effect</label>
                            <select name="effect" class="form-select">
                                <option value="add">Add (+)</option>
                                <option value="deduct">Deduct (-)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <input type="text" name="description" required
                               placeholder="e.g. Performance bonus Q3" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Amount (PKR)</label>
                        <input type="number" name="amount" required min="0" step="0.01" class="form-input">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-plus"></i> Add Adjustment
                    </button>
                </div>
            </form>
        </div>
        @endif

    </div>
</div>

{{-- TAX BREAKDOWN TAB --}}
<div id="ppane-breakdown" style="display:none;">
    @if($payslips->count())
    <div class="card card-flush">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Annual Taxable</th>
                    <th>Tax Slab</th>
                    <th>Monthly Tax</th>
                    <th>EOBI (Emp)</th>
                    <th>EOBI (Empl)</th>
                    <th>PESSI</th>
                    <th>Effective Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payslips as $ps)
                @php $effectiveRate = $ps->gross_salary > 0 ? round(($ps->income_tax / $ps->gross_salary) * 100, 2) : 0; @endphp
                <tr>
                    <td>
                        <div style="font-size:12px;font-weight:600;color:var(--text-primary);">
                            {{ $ps->employee->full_name }}
                        </div>
                        <div style="font-size:10px;color:var(--text-muted);">
                            {{ $ps->employee->employee_id }}
                        </div>
                    </td>
                    <td class="muted">PKR {{ number_format($ps->annual_taxable_income) }}</td>
                    <td style="font-size:11px;color:var(--accent);max-width:140px;">{{ $ps->tax_slab }}</td>
                    <td style="font-weight:700;color:var(--yellow);">
                        PKR {{ number_format($ps->monthly_tax) }}
                    </td>
                    <td style="color:var(--blue);">PKR {{ number_format($ps->eobi_employee) }}</td>
                    <td class="muted">PKR {{ number_format($ps->eobi_employer) }}</td>
                    <td class="muted">PKR {{ number_format($ps->pessi_employee) }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="progress-track" style="width:60px;">
                                <div class="progress-fill"
                                     style="width:{{ min(100, $effectiveRate * 3) }}%;
                                            background:var(--yellow);">
                                </div>
                            </div>
                            <span style="font-size:12px;font-weight:700;color:var(--yellow);">
                                {{ $effectiveRate }}%
                            </span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="card">
        <div class="empty-state">Generate payroll first to see tax breakdown.</div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function switchPTab(active) {
    ['payslips', 'adjustments', 'breakdown'].forEach(function(t) {
        document.getElementById('ppane-' + t).style.display = t === active ? 'block' : 'none';
        document.getElementById('ptab-' + t).classList.toggle('active', t === active);
    });
}
switchPTab('payslips');
</script>
@endpush

@endsection