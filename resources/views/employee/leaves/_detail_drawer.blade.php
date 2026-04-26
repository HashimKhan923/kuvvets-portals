@php
    $statusMap = [
        'pending'   => ['yellow', 'fa-hourglass-half', 'Pending review', 'Waiting for HR approval'],
        'approved'  => ['green',  'fa-circle-check',  'Approved',        'Your leave has been approved'],
        'rejected'  => ['red',    'fa-circle-xmark',  'Rejected',        'Your leave was not approved'],
        'cancelled' => ['gray',   'fa-ban',           'Cancelled',       'This request was cancelled'],
        'withdrawn' => ['gray',   'fa-rotate-left',   'Withdrawn',       'This request was withdrawn'],
    ];
    $sm = $statusMap[$lr->status] ?? ['gray','fa-question','Unknown',''];
    $color = $lr->leaveType->color ?? '#C2531B';
    $canCancel = in_array($lr->status, ['pending'])
                 || ($lr->status === 'approved' && $lr->from_date->isFuture());
@endphp

{{-- Status banner --}}
<div style="padding:20px;border-radius:14px;margin-bottom:18px;text-align:center;
            background: var(--{{ $sm[0] }}-bg);
            border: 1px solid var(--{{ $sm[0] }}-border);">
    <div style="font-size:32px;color:var(--{{ $sm[0] }});margin-bottom:6px;"><i class="fa-solid {{ $sm[1] }}"></i></div>
    <div style="font-size:14px;font-weight:700;color:var(--{{ $sm[0] }});letter-spacing:.3px;">{{ $sm[2] }}</div>
    <div style="font-size:11.5px;color:var(--text-muted);margin-top:3px;">{{ $sm[3] }}</div>
</div>

{{-- Header info --}}
<div style="padding:14px;background:var(--bg-muted);border-radius:12px;margin-bottom:18px;">
    <div style="font-size:11px;color:var(--text-muted);font-weight:600;letter-spacing:.5px;text-transform:uppercase;">
        Request Number
    </div>
    <div style="font-family:'Space Grotesk',sans-serif;font-size:18px;font-weight:700;color:var(--accent);margin-top:3px;">
        {{ $lr->request_number }}
    </div>
    @if($lr->is_emergency)
        <div style="display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:999px;background:var(--red-bg);color:var(--red);font-size:10.5px;font-weight:700;margin-top:8px;">
            <i class="fa-solid fa-triangle-exclamation"></i> Emergency
        </div>
    @endif
</div>

{{-- Detail grid --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px;">
    <div style="padding:12px;background:var(--bg-muted);border-radius:12px;">
        <div style="font-size:10px;color:var(--text-muted);letter-spacing:.6px;text-transform:uppercase;font-weight:600;">Leave Type</div>
        <div style="margin-top:6px;display:flex;align-items:center;gap:6px;">
            <span style="width:10px;height:10px;border-radius:50%;background:{{ $color }};"></span>
            <span style="font-size:14px;font-weight:700;">{{ $lr->leaveType->name }}</span>
        </div>
    </div>
    <div style="padding:12px;background:var(--bg-muted);border-radius:12px;">
        <div style="font-size:10px;color:var(--text-muted);letter-spacing:.6px;text-transform:uppercase;font-weight:600;">Duration</div>
        <div style="margin-top:6px;font-size:14px;font-weight:700;">
            {{ $lr->duration_text ?? rtrim(rtrim(number_format((float)$lr->total_days,1),'0'),'.') . ' Day' . ($lr->total_days == 1 ? '' : 's') }}
        </div>
    </div>
    <div style="padding:12px;background:var(--bg-muted);border-radius:12px;grid-column:1/-1;">
        <div style="font-size:10px;color:var(--text-muted);letter-spacing:.6px;text-transform:uppercase;font-weight:600;">Period</div>
        <div style="margin-top:6px;font-size:14px;font-weight:700;">
            {{ $lr->from_date->format('l, F j') }}
            @if(!$lr->from_date->isSameDay($lr->to_date))
                → {{ $lr->to_date->format('l, F j') }}
            @endif
            <span style="font-weight:500;color:var(--text-muted);">{{ $lr->from_date->format('Y') }}</span>
        </div>
    </div>
</div>

{{-- Reason --}}
<div style="font-size:11px;color:var(--text-secondary);letter-spacing:.6px;text-transform:uppercase;font-weight:700;margin-bottom:8px;">
    Reason
</div>
<div style="padding:12px 14px;background:var(--bg-muted);border-radius:12px;font-size:13px;line-height:1.6;color:var(--text-primary);margin-bottom:18px;">
    {{ $lr->reason }}
</div>

{{-- Contact during leave --}}
@if($lr->contact_during_leave)
    <div style="font-size:11px;color:var(--text-secondary);letter-spacing:.6px;text-transform:uppercase;font-weight:700;margin-bottom:8px;">
        Contact During Leave
    </div>
    <div style="padding:10px 14px;background:var(--bg-muted);border-radius:12px;font-size:13px;color:var(--text-primary);margin-bottom:18px;">
        <i class="fa-solid fa-phone" style="color:var(--accent);margin-right:6px;"></i>{{ $lr->contact_during_leave }}
    </div>
@endif

{{-- Document --}}
@if($lr->document_path)
    <div style="font-size:11px;color:var(--text-secondary);letter-spacing:.6px;text-transform:uppercase;font-weight:700;margin-bottom:8px;">
        Supporting Document
    </div>
    <a href="{{ route('employee.leaves.document', $lr) }}" target="_blank"
       style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:var(--accent-bg);border:1px solid var(--accent-border);border-radius:12px;color:var(--accent);text-decoration:none;font-weight:600;font-size:13px;margin-bottom:18px;">
        <i class="fa-solid fa-file-lines" style="font-size:18px;"></i>
        <span style="flex:1;">Download attached document</span>
        <i class="fa-solid fa-arrow-down"></i>
    </a>
@endif

{{-- HR rejection reason --}}
@if($lr->status === 'rejected' && $lr->rejection_reason)
    <div style="font-size:11px;color:var(--red);letter-spacing:.6px;text-transform:uppercase;font-weight:700;margin-bottom:8px;">
        Rejection Reason
    </div>
    <div style="padding:12px 14px;background:var(--red-bg);border:1px solid var(--red-border);border-radius:12px;font-size:13px;color:var(--red);line-height:1.6;margin-bottom:18px;">
        {{ $lr->rejection_reason }}
    </div>
@endif

{{-- HR notes --}}
@if($lr->hr_notes)
    <div style="font-size:11px;color:var(--text-secondary);letter-spacing:.6px;text-transform:uppercase;font-weight:700;margin-bottom:8px;">
        HR Notes
    </div>
    <div style="padding:12px 14px;background:var(--bg-muted);border-radius:12px;font-size:13px;line-height:1.6;margin-bottom:18px;">
        {{ $lr->hr_notes }}
    </div>
@endif

{{-- Timeline --}}
<div style="font-size:11px;color:var(--text-secondary);letter-spacing:.6px;text-transform:uppercase;font-weight:700;margin-bottom:12px;">
    Timeline
</div>
<div style="position:relative;padding-left:24px;">
    {{-- Applied --}}
    <div style="position:relative;padding-bottom:14px;">
        <div style="position:absolute;left:-24px;top:2px;width:14px;height:14px;border-radius:50%;background:var(--green);border:3px solid var(--bg-card);box-shadow:0 0 0 1px var(--green);"></div>
        <div style="position:absolute;left:-18px;top:14px;bottom:-4px;width:2px;background:var(--border);"></div>
        <div style="font-size:12.5px;font-weight:700;color:var(--text-primary);">Request submitted</div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $lr->created_at->format('M j, Y • h:i A') }}</div>
    </div>

    {{-- Reviewed --}}
    @if($lr->reviewed_at)
        <div style="position:relative;padding-bottom:14px;">
            @php
                $revColor = $lr->status === 'approved' ? 'var(--green)' : ($lr->status === 'rejected' ? 'var(--red)' : 'var(--yellow)');
            @endphp
            <div style="position:absolute;left:-24px;top:2px;width:14px;height:14px;border-radius:50%;background:{{ $revColor }};border:3px solid var(--bg-card);box-shadow:0 0 0 1px {{ $revColor }};"></div>
            <div style="font-size:12.5px;font-weight:700;color:var(--text-primary);">
                {{ ucfirst($lr->status) }} by {{ $lr->reviewer?->name ?? 'HR' }}
            </div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $lr->reviewed_at->format('M j, Y • h:i A') }}</div>
        </div>
    @elseif($lr->status === 'pending')
        <div style="position:relative;">
            <div style="position:absolute;left:-24px;top:2px;width:14px;height:14px;border-radius:50%;background:var(--bg-muted);border:3px solid var(--bg-card);box-shadow:0 0 0 1px var(--border-strong);"></div>
            <div style="font-size:12.5px;font-weight:600;color:var(--text-muted);">Awaiting review</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">HR team will review shortly</div>
        </div>
    @endif
</div>

{{-- Action buttons --}}
@if($canCancel)
    <div style="margin-top:22px;padding-top:18px;border-top:1px solid var(--border);">
        <form method="POST" action="{{ route('employee.leaves.cancel', $lr) }}"
              onsubmit="return confirm('Are you sure you want to cancel this leave request?')">
            @csrf
            <button type="submit" class="btn btn-danger btn-block">
                <i class="fa-solid fa-ban"></i> Cancel This Request
            </button>
        </form>
    </div>
@endif