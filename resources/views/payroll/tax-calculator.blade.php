@extends('layouts.app')
@section('title', 'Tax Calculator')
@section('page-title', 'Pakistan Income Tax Calculator')
@section('breadcrumb', 'Payroll · FBR Tax Calculator 2024-25')

@section('content')

<div style="max-width:900px;display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    {{-- Calculator --}}
    <div class="card">
        <div class="form-section">
            <i class="fa-solid fa-calculator"></i> Salary Tax Estimator
        </div>

        <div style="margin-bottom:18px;">
            <label class="form-label">Monthly Gross Salary (PKR)</label>
            <input type="number" id="grossInput" placeholder="e.g. 150000"
                   min="0" step="1000" class="form-input"
                   style="font-size:16px;"
                   oninput="calculateLive(this.value)">
        </div>

        <div style="margin-bottom:24px;">
            <input type="range" id="grossSlider" min="0" max="1000000"
                   step="5000" value="0"
                   style="width:100%;accent-color:var(--accent);"
                   oninput="document.getElementById('grossInput').value=this.value; calculateLive(this.value)">
            <div style="display:flex;justify-content:space-between;font-size:10px;
                        color:var(--text-muted);margin-top:3px;">
                <span>PKR 0</span><span>PKR 500K</span><span>PKR 1M</span>
            </div>
        </div>

        <div id="results" style="display:none;">

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px;">
                @foreach([
                    ['resGross',    'Monthly Gross',     'text-primary'],
                    ['resTax',      'Monthly Tax (FBR)', 'text-yellow'],
                    ['resNet',      'Estimated Net',     'text-green'],
                    ['resEobiEmp',  'EOBI (Employee)',   'text-blue'],
                    ['resEobiEmpl', 'EOBI (Employer)',   'text-muted'],
                    ['resPessi',    'PESSI (Employee)',  'text-blue'],
                ] as [$id, $label, $color])
                <div class="detail-block" style="text-align:center;">
                    <div class="detail-block-label">{{ $label }}</div>
                    <div id="{{ $id }}" class="{{ $color }}"
                         style="font-size:16px;font-weight:700;">—</div>
                </div>
                @endforeach
            </div>

            <div class="note-block">
                <div class="note-block-label" style="margin-bottom:12px;">ANNUAL SUMMARY</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12px;">
                    <div>
                        Annual Gross:<br>
                        <span id="annualGross" style="font-size:14px;font-weight:700;color:var(--accent);">—</span>
                    </div>
                    <div>
                        Annual Taxable:<br>
                        <span id="annualTaxable" style="font-size:14px;font-weight:700;color:var(--accent);">—</span>
                    </div>
                    <div>
                        Annual Tax:<br>
                        <span id="annualTax" style="font-size:14px;font-weight:700;color:var(--yellow);">—</span>
                    </div>
                    <div>
                        Effective Rate:<br>
                        <span id="effectiveRate" style="font-size:14px;font-weight:700;color:var(--yellow);">—</span>
                    </div>
                </div>
                <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--border);
                            font-size:11px;color:var(--text-muted);">
                    Tax Slab: <span id="taxSlab" style="color:var(--accent);font-weight:700;">—</span>
                </div>
            </div>

        </div>
    </div>

    {{-- FBR Slab Table --}}
    <div class="card" style="position:sticky;top:0;">
        <div class="form-section">
            <i class="fa-solid fa-table"></i> FBR Slab Rates 2024-25
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Annual Income</th>
                    <th style="text-align:right;">Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($slabs as $slab)
                <tr id="slab-row-{{ $loop->index }}">
                    <td class="muted" style="font-size:11px;">{{ $slab['label'] }}</td>
                    <td style="text-align:right;font-size:12px;font-weight:700;color:var(--accent);">
                        {{ $slab['rate'] * 100 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);
                    font-size:10px;line-height:1.9;color:var(--text-muted);">
            <div class="section-label">Other Contributions</div>
            <div>EOBI Employee: 1% of basic</div>
            <div>EOBI Employer: PKR 1,850/mo</div>
            <div>PESSI Employee: 0.9% (max PKR 400)</div>
            <div>PESSI Employer: 5% of gross</div>
            <div style="color:var(--accent);margin-top:4px;font-weight:600;">Min Wage 2024: PKR 37,000</div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function calculateLive(gross) {
    gross = parseFloat(gross) || 0;
    document.getElementById('grossSlider').value = gross;

    if (gross <= 0) {
        document.getElementById('results').style.display = 'none';
        return;
    }
    document.getElementById('results').style.display = 'block';

    var annualGross = gross * 12;
    var basic       = gross * 0.6;
    var eobi        = basic * 0.01;
    var taxable     = Math.max(0, annualGross - eobi * 12);
    var pessi       = Math.min(gross * 0.009, 400);

    var slabs = [
        [0, 600000, 0, 0],
        [600001, 1200000, .05, 0],
        [1200001, 2200000, .15, 30000],
        [2200001, 3200000, .25, 180000],
        [3200001, 4100000, .30, 430000],
        [4100001, Infinity, .35, 700000]
    ];
    var slabLabels = [
        'Up to 600,000 @ 0%',
        '600,001–1,200,000 @ 5%',
        '1,200,001–2,200,000 @ 15%',
        '2,200,001–3,200,000 @ 25%',
        '3,200,001–4,100,000 @ 30%',
        'Above 4,100,000 @ 35%'
    ];

    var annualTax = 0, slabLabel = '';
    slabs.forEach(function(s, i) {
        if (taxable >= s[0] && taxable <= s[1]) {
            annualTax  = s[3] + (taxable - (s[0] - 1)) * s[2];
            slabLabel  = slabLabels[i];
            slabs.forEach(function(_, j) {
                var row = document.getElementById('slab-row-' + j);
                if (row) row.style.background = j === i ? 'var(--accent-bg)' : '';
            });
        }
    });

    var monthlyTax   = annualTax / 12;
    var effectiveRate = taxable > 0 ? ((annualTax / taxable) * 100).toFixed(2) : 0;
    var net           = gross - monthlyTax - eobi - pessi;

    function fmt(n) { return 'PKR ' + Math.round(n).toLocaleString(); }

    var ids    = ['resGross','resTax','resNet','resEobiEmp','resEobiEmpl','resPessi'];
    var values = [gross, monthlyTax, net, eobi, 1850, pessi];
    ids.forEach(function(id, i) {
        document.getElementById(id).textContent = fmt(values[i]);
    });

    document.getElementById('annualGross').textContent    = fmt(annualGross);
    document.getElementById('annualTaxable').textContent  = fmt(taxable);
    document.getElementById('annualTax').textContent      = fmt(annualTax);
    document.getElementById('effectiveRate').textContent  = effectiveRate + '%';
    document.getElementById('taxSlab').textContent        = slabLabel;
}
</script>
@endpush

@endsection