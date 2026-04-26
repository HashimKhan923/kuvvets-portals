@extends('layouts.app')
@section('title', $asset->name)
@section('page-title', $asset->name)
@section('breadcrumb', 'Assets · ' . $asset->asset_code)

@section('content')

<div class="grid-sidebar-main">

    {{-- LEFT: Asset Info Cards --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Main Card --}}
        <div class="card" style="text-align:center;">
            @if($asset->image)
            <img src="{{ asset('storage/' . $asset->image) }}"
                 style="width:100%;height:160px;object-fit:cover;border-radius:10px;
                        margin-bottom:14px;border:2px solid var(--accent-border);">
            @else
            <div style="width:100%;height:120px;background:var(--accent-bg);
                        border:2px solid var(--accent-border);border-radius:10px;
                        display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                <i class="fa-solid {{ $asset->type_icon }}" style="font-size:42px;color:var(--accent);"></i>
            </div>
            @endif

            <div style="font-size:16px;font-weight:700;color:var(--text-primary);">{{ $asset->name }}</div>
            <div style="font-size:12px;color:var(--accent);margin-top:2px;font-weight:600;">{{ $asset->asset_code }}</div>
            @if($asset->brand || $asset->model)
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ $asset->brand }} {{ $asset->model }}</div>
            @endif

            <div style="display:flex;gap:6px;justify-content:center;margin-top:10px;flex-wrap:wrap;">
                @php $sBadge = $asset->status_badge; $cBadge = $asset->condition_badge; @endphp
                <span class="badge" style="background:{{ $sBadge['bg'] }};color:{{ $sBadge['color'] }};border:1px solid {{ $sBadge['border'] }};">{{ ucfirst(str_replace('_', ' ', $asset->status)) }}</span>
                <span class="badge" style="background:{{ $cBadge['bg'] }};color:{{ $cBadge['color'] }};border:1px solid {{ $cBadge['border'] }};">{{ ucfirst($asset->condition) }}</span>
            </div>

            <a href="{{ route('assets.edit', $asset) }}" class="btn btn-secondary btn-sm" style="margin-top:14px;width:100%;justify-content:center;">
                <i class="fa-solid fa-pen"></i> Edit Asset
            </a>
        </div>

        {{-- Details --}}
        <div class="card">
            <div class="section-label">Asset Details</div>
            @foreach([
                ['Type',       ucfirst(str_replace('_', ' ', $asset->type))],
                ['Ownership',  ucfirst($asset->ownership)],
                ['Department', $asset->department?->name ?? '—'],
                ['Location',   $asset->location ?? '—'],
                ['Serial No.', $asset->serial_number ?? '—'],
                ['Reg. No.',   $asset->registration_number ?? '—'],
                ['Odometer',   $asset->odometer_reading ? number_format($asset->odometer_reading) . ' km' : '—'],
                ['Op. Hours',  $asset->operating_hours ? number_format($asset->operating_hours) . ' hrs' : '—'],
            ] as [$l, $v])
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
                <span style="color:var(--text-muted);">{{ $l }}</span>
                <span style="color:var(--text-primary);font-weight:500;text-align:right;max-width:140px;">{{ $v }}</span>
            </div>
            @endforeach
        </div>

        {{-- Financial --}}
        <div class="card">
            <div class="section-label">Financial</div>
            @foreach([
                ['Purchase Cost', $asset->purchase_cost ? 'PKR ' . number_format($asset->purchase_cost) : '—'],
                ['Current Value', $asset->current_value ? 'PKR ' . number_format($asset->current_value) : '—'],
                ['Dep. Value',    'PKR ' . number_format($asset->depreciated_value)],
                ['Depreciation',  $asset->depreciation_rate . '% /yr'],
                ['Purchase Date', $asset->purchase_date?->format('d M Y') ?? '—'],
                ['Vendor',        $asset->vendor ?? '—'],
            ] as [$l, $v])
            <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
                <span style="color:var(--text-muted);">{{ $l }}</span>
                <span style="color:var(--text-primary);font-weight:500;">{{ $v }}</span>
            </div>
            @endforeach
        </div>

        {{-- Compliance --}}
        <div class="card">
            <div class="section-label">Compliance</div>
            @foreach([
                ['Warranty',  $asset->warranty_expiry, $asset->isWarrantyExpired()],
                ['Insurance', $asset->insurance_expiry, $asset->isInsuranceExpired()],
                ['License',   $asset->license_expiry,  $asset->license_expiry?->isPast()],
            ] as [$l, $date, $expired])
            <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:12px;">
                <span style="color:var(--text-muted);">{{ $l }}</span>
                <span style="font-weight:500;color:{{ $expired ? 'var(--red)' : 'var(--text-primary)' }};">
                    {{ $date?->format('d M Y') ?? '—' }}
                    @if($expired) ⚠️ @endif
                </span>
            </div>
            @endforeach
        </div>

    </div>

    {{-- RIGHT: Tabs --}}
    <div>

        <div class="tab-nav">
            @foreach([
                ['assign',      'fa-user-check',      'Assignment'],
                ['maintenance', 'fa-wrench',          'Maintenance (' . $asset->maintenance->count() . ')'],
                ['history',     'fa-clock-rotate-left','History (' . $asset->assignments->count() . ')'],
                ['rental',      'fa-handshake',       'Rentals (' . $asset->rentals->count() . ')'],
            ] as [$id, $icon, $label])
            <button type="button" class="tab-btn" id="adtab-{{ $id }}"
                    onclick="switchAssetDetailTab('{{ $id }}')">
                <i class="fa-solid {{ $icon }}"></i> {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- ASSIGNMENT TAB --}}
        <div id="adpane-assign">
            @if($asset->currentAssignment)
            <div class="card" style="margin-bottom:14px;border-left:4px solid var(--blue);">
                <div style="font-size:12px;color:var(--blue);font-weight:700;margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">
                    <i class="fa-solid fa-user-check" style="margin-right:6px;"></i>Currently Assigned
                </div>
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
                    <img src="{{ $asset->currentAssignment->employee->avatar_url }}" class="avatar" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--accent-border);">
                    <div>
                        <div style="font-size:15px;font-weight:700;color:var(--text-primary);">{{ $asset->currentAssignment->employee->full_name }}</div>
                        <div style="font-size:12px;color:var(--text-muted);">
                            {{ $asset->currentAssignment->employee->designation?->title ?? '—' }} · {{ $asset->currentAssignment->employee->department?->name ?? '—' }}
                        </div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                            Assigned: {{ $asset->currentAssignment->assigned_date->format('d M Y') }}
                            @if($asset->currentAssignment->expected_return_date)
                            · Expected return: {{ $asset->currentAssignment->expected_return_date->format('d M Y') }}
                            @if($asset->currentAssignment->isOverdue())
                            <span style="color:var(--red);font-weight:700;"> (OVERDUE)</span>
                            @endif
                            @endif
                        </div>
                        @if($asset->currentAssignment->purpose)
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">Purpose: {{ $asset->currentAssignment->purpose }}</div>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('assets.return', $asset->currentAssignment) }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                        <div>
                            <label class="form-label">Condition on Return</label>
                            <select name="condition_on_return" class="form-select">
                                @foreach(['new'=>'New','good'=>'Good','fair'=>'Fair','poor'=>'Poor','under_repair'=>'Needs Repair'] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Return Notes</label>
                            <input type="text" name="notes" placeholder="Any remarks…" class="form-input">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-rotate-left"></i> Mark as Returned
                    </button>
                </form>
            </div>

            @elseif($asset->status === 'available')
            <div class="card">
                <div class="form-section"><i class="fa-solid fa-user-plus"></i> Assign to Employee</div>
                <form method="POST" action="{{ route('assets.assign', $asset) }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                        <div style="grid-column:span 2;">
                            <label class="form-label">Employee <span style="color:var(--red);">*</span></label>
                            <select name="employee_id" required class="form-select">
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->department?->name ?? 'No dept' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Assigned Date <span style="color:var(--red);">*</span></label>
                            <input type="date" name="assigned_date" required value="{{ today()->format('Y-m-d') }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Expected Return Date</label>
                            <input type="date" name="expected_return_date" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Purpose</label>
                            <input type="text" name="purpose" placeholder="e.g. Warehouse operations" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Condition on Issue</label>
                            <select name="condition_on_issue" class="form-select">
                                @foreach(['new'=>'New','good'=>'Good','fair'=>'Fair','poor'=>'Poor'] as $v => $l)
                                <option value="{{ $v }}" {{ $asset->condition === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-user-check"></i> Assign Asset
                    </button>
                </form>
            </div>

            @else
            <div class="flash flash-warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Asset is {{ str_replace('_', ' ', $asset->status) }}. Change status to Available first.
            </div>
            @endif
        </div>

        {{-- MAINTENANCE TAB --}}
        <div id="adpane-maintenance" style="display:none;">
            <div class="card" style="margin-bottom:14px;">
                <div class="form-section"><i class="fa-solid fa-calendar-plus"></i> Schedule Maintenance</div>
                <form method="POST" action="{{ route('assets.maintenance.store', $asset) }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px;">
                        <div>
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                @foreach(['routine'=>'Routine','preventive'=>'Preventive','corrective'=>'Corrective','emergency'=>'Emergency','inspection'=>'Inspection','calibration'=>'Calibration'] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Scheduled Date</label>
                            <input type="date" name="scheduled_date" required class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Cost (PKR)</label>
                            <input type="number" name="cost" min="0" placeholder="0" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Performed By</label>
                            <input type="text" name="performed_by" placeholder="Technician name" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Vendor / Service Centre</label>
                            <input type="text" name="vendor" placeholder="Workshop name" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Next Service Date</label>
                            <input type="date" name="next_service_date" class="form-input">
                        </div>
                        <div style="grid-column:span 3;">
                            <label class="form-label">Description <span style="color:var(--red);">*</span></label>
                            <input type="text" name="description" required placeholder="Brief description of work required" class="form-input">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-calendar-plus"></i> Schedule
                    </button>
                </form>
            </div>

            @forelse($asset->maintenance->sortByDesc('scheduled_date') as $record)
            @php $mBadge = $record->status_badge; $tBadge = $record->type_badge; @endphp
            <div class="card" style="margin-bottom:8px;">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:7px;margin-bottom:4px;flex-wrap:wrap;">
                            <span class="badge" style="background:{{ $tBadge['bg'] }};color:{{ $tBadge['color'] }};border:1px solid {{ $tBadge['border'] }};font-size:10px;">{{ ucfirst($record->type) }}</span>
                            <span class="badge" style="background:{{ $mBadge['bg'] }};color:{{ $mBadge['color'] }};border:1px solid {{ $mBadge['border'] }};font-size:10px;">{{ ucfirst($record->status) }}</span>
                            <span style="font-size:10px;color:var(--text-muted);">{{ $record->reference_number }}</span>
                        </div>
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $record->description }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">
                            Scheduled: {{ $record->scheduled_date->format('d M Y') }}
                            @if($record->completed_date) · Completed: {{ $record->completed_date->format('d M Y') }} @endif
                            @if($record->vendor) · {{ $record->vendor }} @endif
                            · PKR {{ number_format($record->cost) }}
                        </div>
                        @if($record->work_done)
                        <div style="font-size:11px;color:var(--text-secondary);margin-top:3px;">Work done: {{ $record->work_done }}</div>
                        @endif
                    </div>
                    @if(in_array($record->status, ['scheduled','in_progress']))
                    <button onclick="openCompleteModal({{ $record->id }})" class="btn btn-success btn-sm" style="white-space:nowrap;">
                        <i class="fa-solid fa-check"></i> Complete
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state"><i class="fa-solid fa-wrench"></i>No maintenance records yet.</div>
            @endforelse
        </div>

        {{-- HISTORY TAB --}}
        <div id="adpane-history" style="display:none;">
            @forelse($asset->assignments->sortByDesc('assigned_date') as $assignment)
            <div class="card" style="margin-bottom:8px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <img src="{{ $assignment->employee->avatar_url }}" class="avatar avatar-sm" style="flex-shrink:0;">
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $assignment->employee->full_name }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">
                            {{ $assignment->assigned_date->format('d M Y') }} →
                            {{ $assignment->actual_return_date?->format('d M Y') ?? 'Still assigned' }}
                            @if($assignment->purpose) · {{ $assignment->purpose }} @endif
                        </div>
                    </div>
                    @php
                    $hColor = match($assignment->status) {
                        'active'   => 'var(--green)',
                        'returned' => 'var(--text-muted)',
                        'overdue'  => 'var(--red)',
                        default    => 'var(--text-muted)',
                    };
                    @endphp
                    <span style="font-size:11px;font-weight:700;color:{{ $hColor }};">{{ ucfirst($assignment->status) }}</span>
                </div>
            </div>
            @empty
            <div class="empty-state">No assignment history yet.</div>
            @endforelse
        </div>

        {{-- RENTAL TAB --}}
        <div id="adpane-rental" style="display:none;">

            <div class="card" style="margin-bottom:14px;">
                <div class="form-section"><i class="fa-solid fa-file-contract"></i> New Rental Contract</div>
                <form method="POST" action="{{ route('assets.rental.store', $asset) }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px;">
                        <div>
                            <label class="form-label">Rental Type</label>
                            <select name="rental_type" class="form-select">
                                <option value="inbound">Inbound (We rent from others)</option>
                                <option value="outbound">Outbound (We rent to clients)</option>
                            </select>
                        </div>
                        <div style="grid-column:span 2;">
                            <label class="form-label">Party Name <span style="color:var(--red);">*</span></label>
                            <input type="text" name="party_name" required placeholder="Vendor or Client name" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Start Date <span style="color:var(--red);">*</span></label>
                            <input type="date" name="start_date" required id="rentalStart" class="form-input" onchange="calcRental()">
                        </div>
                        <div>
                            <label class="form-label">End Date <span style="color:var(--red);">*</span></label>
                            <input type="date" name="end_date" required id="rentalEnd" class="form-input" onchange="calcRental()">
                        </div>
                        <div>
                            <label class="form-label">Rate / Day (PKR) <span style="color:var(--red);">*</span></label>
                            <input type="number" name="rate_per_day" required min="0" id="rentalRate" class="form-input" oninput="calcRental()">
                        </div>
                        <div>
                            <label class="form-label">Deposit (PKR)</label>
                            <input type="number" name="deposit_amount" min="0" placeholder="0" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Contact</label>
                            <input type="text" name="party_contact" placeholder="+92-XXX-XXXXXXX" class="form-input">
                        </div>
                        <div id="rentalCalcBox" style="display:none;background:var(--accent-bg);border:1px solid var(--accent-border);border-radius:8px;padding:12px;text-align:center;">
                            <div style="font-size:10px;color:var(--text-muted);margin-bottom:2px;">Total Amount</div>
                            <div id="rentalTotal" style="font-size:18px;font-weight:700;color:var(--accent);">PKR 0</div>
                        </div>
                        <div style="grid-column:span 3;">
                            <label class="form-label">Terms / Notes</label>
                            <textarea name="terms" rows="2" class="form-textarea"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-file-contract"></i> Create Contract
                    </button>
                </form>
            </div>

            @forelse($asset->rentals->sortByDesc('start_date') as $rental)
            @php $rBadge = $rental->status_badge; @endphp
            <div class="card" style="margin-bottom:8px;">
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                            <span class="badge {{ $rental->rental_type === 'outbound' ? 'badge-green' : 'badge-blue' }}" style="font-size:10px;">{{ ucfirst($rental->rental_type) }}</span>
                            <span class="badge" style="background:{{ $rBadge['bg'] }};color:{{ $rBadge['color'] }};border:1px solid {{ $rBadge['border'] }};">{{ ucfirst($rental->status) }}</span>
                            <span style="font-size:10px;color:var(--text-muted);">{{ $rental->contract_number }}</span>
                        </div>
                        <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $rental->party_name }}</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                            {{ $rental->start_date->format('d M Y') }} – {{ $rental->end_date->format('d M Y') }}
                            · {{ $rental->duration_days }} days
                            @if($rental->party_contact) · {{ $rental->party_contact }} @endif
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:16px;font-weight:700;color:var(--accent);">PKR {{ number_format($rental->total_amount) }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">PKR {{ number_format($rental->rate_per_day) }}/day</div>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">No rental contracts yet.</div>
            @endforelse

        </div>

    </div>
</div>

{{-- Complete Maintenance Modal --}}
<div id="completeMaintModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">
            <i class="fa-solid fa-check-circle" style="color:var(--green);"></i>
            Complete Maintenance Record
        </div>
        <form id="completeMaintForm" method="POST">
            @csrf
            <div style="display:flex;flex-direction:column;gap:11px;margin-bottom:4px;">
                <div>
                    <label class="form-label">Work Done</label>
                    <textarea name="work_done" rows="2" class="form-textarea" placeholder="Describe work performed…"></textarea>
                </div>
                <div>
                    <label class="form-label">Parts Replaced</label>
                    <input type="text" name="parts_replaced" placeholder="List any replaced parts" class="form-input">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Actual Cost (PKR)</label>
                        <input type="number" name="cost" min="0" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Next Service Date</label>
                        <input type="date" name="next_service_date" class="form-input">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCompleteModal()">Cancel</button>
                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-check"></i> Mark Completed
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function switchAssetDetailTab(active) {
    ['assign', 'maintenance', 'history', 'rental'].forEach(function(t) {
        document.getElementById('adpane-' + t).style.display = t === active ? 'block' : 'none';
        document.getElementById('adtab-' + t).classList.toggle('active', t === active);
    });
}
switchAssetDetailTab('assign');

function openCompleteModal(id) {
    document.getElementById('completeMaintForm').action = '/assets/maintenance/' + id + '/complete';
    document.getElementById('completeMaintModal').classList.add('open');
}
function closeCompleteModal() {
    document.getElementById('completeMaintModal').classList.remove('open');
}
document.getElementById('completeMaintModal').addEventListener('click', function(e) {
    if (e.target === this) closeCompleteModal();
});

function calcRental() {
    var start = document.getElementById('rentalStart') ? document.getElementById('rentalStart').value : '';
    var end   = document.getElementById('rentalEnd')   ? document.getElementById('rentalEnd').value   : '';
    var rate  = parseFloat(document.getElementById('rentalRate') ? document.getElementById('rentalRate').value : 0) || 0;
    if (!start || !end || !rate) return;
    var days  = Math.max(1, Math.round((new Date(end) - new Date(start)) / 86400000) + 1);
    var total = days * rate;
    document.getElementById('rentalTotal').textContent = 'PKR ' + Math.round(total).toLocaleString();
    document.getElementById('rentalCalcBox').style.display = 'block';
}
</script>
@endpush

@endsection