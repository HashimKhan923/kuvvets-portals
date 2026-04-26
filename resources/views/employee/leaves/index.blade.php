@extends('employee.layouts.app')
@section('title', 'Leaves')
@section('page-title', 'My Leaves')
@section('page-sub', 'Balance, apply, and track your leave requests')

@push('styles')
<style>
    /* ═══════════════════════════════════════════════════
       LEAVES PAGE
    ═══════════════════════════════════════════════════ */
    .hd-row {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; flex-wrap: wrap; margin-bottom: 18px;
    }
    .year-nav {
        display: flex; align-items: center; gap: 4px;
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 10px; padding: 3px;
    }
    .year-nav a {
        padding: 8px 12px; border-radius: 7px;
        font-size: 12.5px; font-weight: 600;
        color: var(--text-secondary); text-decoration: none;
    }
    .year-nav a.active { background: var(--accent-bg); color: var(--accent); }
    .year-nav a:hover:not(.active) { background: var(--bg-hover); color: var(--accent); }

    /* Stats */
    .lv-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px; }
    @media (max-width:780px) { .lv-stats { grid-template-columns: repeat(2, 1fr); } }

    .lv-stat {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 14px; padding: 14px 16px;
        display: flex; align-items: center; gap: 12px;
    }
    .lv-stat-ico {
        width: 42px; height: 42px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; flex-shrink: 0;
    }
    .lv-stat-ico.yellow { background: var(--yellow-bg); color: var(--yellow); }
    .lv-stat-ico.green  { background: var(--green-bg);  color: var(--green);  }
    .lv-stat-ico.red    { background: var(--red-bg);    color: var(--red);    }
    .lv-stat-ico.accent { background: var(--accent-bg); color: var(--accent); }
    .lv-stat-val { font-family: 'Space Grotesk', sans-serif; font-size: 20px; font-weight: 700; line-height: 1; }
    .lv-stat-lbl { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; font-weight: 600; margin-top: 3px; }

    /* Balance cards */
    .section-title {
        font-size: 12px; font-weight: 700; color: var(--text-primary);
        letter-spacing: .5px; text-transform: uppercase;
        font-family: 'Space Grotesk', sans-serif;
        margin-bottom: 12px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .bal-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; margin-bottom: 24px; }

    .bal-card {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 16px; padding: 18px;
        position: relative; overflow: hidden;
    }
    .bal-card::before {
        content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
        background: var(--type-color, var(--accent));
    }
    .bal-hd { display: flex; align-items: start; justify-content: space-between; margin-bottom: 14px; }
    .bal-type-name {
        font-size: 14px; font-weight: 700; color: var(--text-primary);
        font-family: 'Space Grotesk', sans-serif;
    }
    .bal-type-code {
        font-size: 10.5px; color: var(--text-muted);
        font-weight: 600; letter-spacing: .5px; margin-top: 2px;
    }
    .bal-chip {
        padding: 4px 10px; border-radius: 999px;
        font-size: 10.5px; font-weight: 700; letter-spacing: .3px;
    }
    .bal-chip.paid   { background: var(--green-bg);  color: var(--green);  }
    .bal-chip.unpaid { background: var(--bg-muted); color: var(--text-muted); }

    .bal-big {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 28px; font-weight: 700; line-height: 1;
        color: var(--text-primary);
        font-variant-numeric: tabular-nums;
    }
    .bal-big .unit { font-size: 14px; color: var(--text-muted); font-weight: 500; margin-left: 3px; }
    .bal-sub { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }

    .bal-progress {
        height: 6px; border-radius: 999px;
        background: var(--bg-muted); overflow: hidden;
        margin-top: 14px;
    }
    .bal-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--type-color, var(--accent)) 0%, var(--type-color-light, var(--accent-light)) 100%);
        border-radius: 999px;
        transition: width .4s ease;
    }
    .bal-breakdown {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
        margin-top: 12px; font-size: 11px;
    }
    .bal-breakdown-item {
        background: var(--bg-muted); border-radius: 8px;
        padding: 6px 8px; text-align: center;
    }
    .bal-breakdown-item .n {
        display: block; font-weight: 700; font-size: 13px;
        color: var(--text-primary); font-variant-numeric: tabular-nums;
    }
    .bal-breakdown-item .l {
        display: block; color: var(--text-muted); font-size: 10px;
        text-transform: uppercase; letter-spacing: .4px; font-weight: 600;
    }

    /* Empty state */
    .empty-state {
        text-align: center; padding: 60px 20px;
        color: var(--text-muted);
    }
    .empty-state i { font-size: 40px; opacity: .3; margin-bottom: 14px; display: block; }

    /* History table */
    .history-card {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 16px; overflow: hidden;
    }
    .history-filter {
        display: flex; gap: 6px; padding: 12px 14px;
        border-bottom: 1px solid var(--border);
        overflow-x: auto;
    }
    .f-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 12px; border-radius: 10px;
        font-size: 12px; font-weight: 600;
        color: var(--text-secondary); text-decoration: none;
        background: var(--bg-muted);
        white-space: nowrap;
    }
    .f-chip.active { background: var(--accent-bg); color: var(--accent); }
    .f-chip:hover:not(.active) { background: var(--bg-hover); color: var(--accent); }

    .lv-table { width: 100%; border-collapse: collapse; }
    .lv-table th {
        background: var(--bg-muted);
        padding: 12px 14px; text-align: left;
        font-size: 11px; font-weight: 700;
        color: var(--text-secondary);
        letter-spacing: .6px; text-transform: uppercase;
    }
    .lv-table td {
        padding: 14px; font-size: 13px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .lv-table tr:last-child td { border-bottom: none; }
    .lv-table tbody tr { cursor: pointer; transition: background .15s; }
    .lv-table tbody tr:hover { background: var(--bg-hover); }
    .lv-req-num {
        font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 12px;
        color: var(--accent);
    }
    .lv-emergency {
        display: inline-flex; align-items: center; gap: 3px;
        font-size: 10px; color: var(--red); font-weight: 700;
        margin-left: 4px;
    }
    @media (max-width: 780px) {
        .lv-table .col-hide-mobile { display: none; }
        .lv-table td, .lv-table th { padding: 10px 8px; font-size: 12px; }
    }

    /* Pagination */
    .pagination-wrap {
        padding: 14px; display: flex; justify-content: center;
        border-top: 1px solid var(--border);
    }
    .pagination-wrap nav { display: flex; gap: 4px; }
    .pagination-wrap a, .pagination-wrap span {
        min-width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        padding: 0 10px; border-radius: 8px;
        font-size: 12.5px; font-weight: 600;
        text-decoration: none; color: var(--text-secondary);
        background: var(--bg-muted);
    }
    .pagination-wrap .active span, .pagination-wrap a:hover {
        background: var(--accent); color: #fff;
    }

    /* Drawer */
    .drawer-overlay {
        position: fixed; inset: 0; z-index: 90;
        background: rgba(15,10,5,.5); backdrop-filter: blur(4px);
        animation: fadeIn .2s ease;
    }
    @keyframes fadeIn { from{opacity:0} to{opacity:1} }
    .drawer {
        position: fixed; top: 0; right: 0; bottom: 0;
        width: 100%; max-width: 480px;
        background: var(--bg-card); z-index: 91;
        display: flex; flex-direction: column;
        box-shadow: -10px 0 40px rgba(0,0,0,.15);
        animation: slideInRight .25s cubic-bezier(.22,.61,.36,1);
    }
    @keyframes slideInRight { from{transform:translateX(100%)} to{transform:translateX(0)} }
    .drawer-hd {
        padding: 18px 22px; border-bottom: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
    }
    .drawer-title { font-family:'Space Grotesk',sans-serif; font-size: 16px; font-weight: 700; }
    .drawer-sub { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
    .drawer-body { flex: 1; overflow-y: auto; padding: 20px 22px; }
    .drawer-close {
        width:34px; height:34px; border-radius:10px;
        background:var(--bg-muted); border:none; cursor:pointer;
        display:flex; align-items:center; justify-content:center;
        color:var(--text-secondary);
    }
    .drawer-close:hover { background: var(--red-bg); color: var(--red); }
    .drawer-spinner {
        display:inline-block; width:28px; height:28px;
        border:3px solid var(--border-strong); border-top-color: var(--accent);
        border-radius:50%; animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')

<div x-data="leavesApp()">
    {{-- ═══════════ Header row ═══════════ --}}
    <div class="hd-row">
        <div class="year-nav">
            @for($y = now()->year + 1; $y >= now()->year - 2; $y--)
                <a href="{{ route('employee.leaves.index', ['year'=>$y, 'status'=>$statusFilter]) }}" class="{{ $year == $y ? 'active' : '' }}">{{ $y }}</a>
            @endfor
        </div>

        <a href="{{ route('employee.leaves.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Apply for Leave
        </a>
    </div>

    {{-- ═══════════ Stats ═══════════ --}}
    <div class="lv-stats">
        <div class="lv-stat">
            <div class="lv-stat-ico yellow"><i class="fa-solid fa-hourglass-half"></i></div>
            <div>
                <div class="lv-stat-val">{{ $stats['pending'] }}</div>
                <div class="lv-stat-lbl">Pending</div>
            </div>
        </div>
        <div class="lv-stat">
            <div class="lv-stat-ico green"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <div class="lv-stat-val">{{ $stats['approved'] }}</div>
                <div class="lv-stat-lbl">Approved ({{ $year }})</div>
            </div>
        </div>
        <div class="lv-stat">
            <div class="lv-stat-ico red"><i class="fa-solid fa-circle-xmark"></i></div>
            <div>
                <div class="lv-stat-val">{{ $stats['rejected'] }}</div>
                <div class="lv-stat-lbl">Rejected ({{ $year }})</div>
            </div>
        </div>
        <div class="lv-stat">
            <div class="lv-stat-ico accent"><i class="fa-solid fa-umbrella-beach"></i></div>
            <div>
                <div class="lv-stat-val">{{ rtrim(rtrim(number_format((float)$stats['total_days_used'],1),'0'),'.') ?: '0' }}</div>
                <div class="lv-stat-lbl">Days used ({{ $year }})</div>
            </div>
        </div>
    </div>

    {{-- ═══════════ Balance cards ═══════════ --}}
    <div class="section-title">
        <span>Leave Balance — {{ $year }}</span>
    </div>

    @if($balances->count())
        <div class="bal-grid">
            @foreach($balances as $b)
                @php
                    $type = $b->leaveType;
                    $color = $type->color ?? '#C2531B';
                    $available = $b->available;
                    $used = $b->used_days;
                    $pending = $b->pending_days;
                    $total = $b->total_allocated;
                    $pct = $total > 0 ? min(100, round((($used + $pending) / $total) * 100)) : 0;
                @endphp
                <div class="bal-card" style="--type-color: {{ $color }}; --type-color-light: {{ $color }}80;">
                    <div class="bal-hd">
                        <div>
                            <div class="bal-type-name">{{ $type->name }}</div>
                            <div class="bal-type-code">{{ $type->code }}</div>
                        </div>
                        <span class="bal-chip {{ $type->is_paid ? 'paid' : 'unpaid' }}">
                            {{ $type->is_paid ? 'Paid' : 'Unpaid' }}
                        </span>
                    </div>

                    <div class="bal-big">
                        {{ rtrim(rtrim(number_format((float)$available,1),'0'),'.') }}<span class="unit">of {{ rtrim(rtrim(number_format((float)$total,1),'0'),'.') }} days</span>
                    </div>
                    <div class="bal-sub">available to apply</div>

                    <div class="bal-progress"><div class="bal-progress-fill" style="width: {{ $pct }}%"></div></div>

                    <div class="bal-breakdown">
                        <div class="bal-breakdown-item">
                            <span class="n">{{ rtrim(rtrim(number_format((float)$used,1),'0'),'.') }}</span>
                            <span class="l">Used</span>
                        </div>
                        <div class="bal-breakdown-item">
                            <span class="n">{{ rtrim(rtrim(number_format((float)$pending,1),'0'),'.') }}</span>
                            <span class="l">Pending</span>
                        </div>
                        <div class="bal-breakdown-item">
                            <span class="n">{{ rtrim(rtrim(number_format((float)$b->carried_forward,1),'0'),'.') }}</span>
                            <span class="l">Carried</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="empty-state">
                <i class="fa-solid fa-umbrella-beach"></i>
                <div style="font-weight:600;color:var(--text-secondary);margin-bottom:4px;">No leave types configured</div>
                <div style="font-size:12px;">Please contact HR to set up your leave allocations.</div>
            </div>
        </div>
    @endif

    {{-- ═══════════ History ═══════════ --}}
    <div class="section-title" style="margin-top:8px;">
        <span>Request History</span>
        <span style="font-size:11px;color:var(--text-muted);font-weight:500;text-transform:none;letter-spacing:0;">
            {{ $history->total() }} total
        </span>
    </div>

    <div class="history-card">
        {{-- Status filters --}}
        <div class="history-filter">
            <a href="{{ route('employee.leaves.index', ['year'=>$year]) }}" class="f-chip {{ !$statusFilter ? 'active' : '' }}">
                <i class="fa-solid fa-list" style="font-size:10px;"></i> All
            </a>
            @foreach(['pending'=>'fa-hourglass-half','approved'=>'fa-circle-check','rejected'=>'fa-circle-xmark','cancelled'=>'fa-ban'] as $s => $ic)
                <a href="{{ route('employee.leaves.index', ['year'=>$year,'status'=>$s]) }}" class="f-chip {{ $statusFilter===$s ? 'active' : '' }}">
                    <i class="fa-solid {{ $ic }}" style="font-size:10px;"></i> {{ ucfirst($s) }}
                </a>
            @endforeach
        </div>

        @if($history->count())
            <div style="overflow-x:auto;">
                <table class="lv-table">
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>Type</th>
                            <th>Period</th>
                            <th class="col-hide-mobile">Days</th>
                            <th>Status</th>
                            <th class="col-hide-mobile">Applied</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $lr)
                            @php
                                $bc = match($lr->status) {
                                    'approved'  => 'badge-green',
                                    'rejected'  => 'badge-red',
                                    'pending'   => 'badge-yellow',
                                    'cancelled' => 'badge-gray',
                                    default     => 'badge-gray',
                                };
                                $color = $lr->leaveType->color ?? '#C2531B';
                            @endphp
                            <tr @click="openDetail({{ $lr->id }})">
                                <td>
                                    <div class="lv-req-num">{{ $lr->request_number }}</div>
                                    @if($lr->is_emergency)
                                        <span class="lv-emergency"><i class="fa-solid fa-triangle-exclamation"></i>Emergency</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:inline-flex;align-items:center;gap:6px;">
                                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $color }};display:inline-block;"></span>
                                        <span style="font-weight:600;">{{ $lr->leaveType->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight:600;">{{ $lr->from_date->format('M j') }} – {{ $lr->to_date->format('M j') }}</div>
                                    <div style="font-size:11px;color:var(--text-muted);">{{ $lr->from_date->format('Y') }}</div>
                                </td>
                                <td class="col-hide-mobile">
                                    <span style="font-weight:700;font-variant-numeric:tabular-nums;">{{ rtrim(rtrim(number_format((float)$lr->total_days,1),'0'),'.') }}</span>
                                    <span style="font-size:11px;color:var(--text-muted);">day{{ $lr->total_days == 1 ? '' : 's' }}</span>
                                </td>
                                <td><span class="badge {{ $bc }}">{{ ucfirst($lr->status) }}</span></td>
                                <td class="col-hide-mobile" style="font-size:11.5px;color:var(--text-muted);">
                                    {{ $lr->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($history->hasPages())
                <div class="pagination-wrap">{{ $history->links() }}</div>
            @endif
        @else
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <div style="font-weight:600;color:var(--text-secondary);margin-bottom:4px;">No leave requests found</div>
                <div style="font-size:12px;">
                    @if($statusFilter)
                        No {{ $statusFilter }} requests.
                        <a href="{{ route('employee.leaves.index', ['year'=>$year]) }}" style="color:var(--accent);font-weight:600;">Clear filter</a>
                    @else
                        <a href="{{ route('employee.leaves.create') }}" style="color:var(--accent);font-weight:600;">Apply for your first leave →</a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- ═══════════ Detail drawer ═══════════ --}}
    <template x-teleport="body">
        <div x-show="drawerOpen" style="display:none;">
            <div class="drawer-overlay" @click="closeDetail()"></div>
            <div class="drawer">
                <div class="drawer-hd">
                    <div>
                        <div class="drawer-title">Leave Request</div>
                        <div class="drawer-sub">Full details &amp; timeline</div>
                    </div>
                    <button class="drawer-close" @click="closeDetail()"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="drawer-body">
                    <template x-if="loading">
                        <div style="text-align:center;padding:60px 0;">
                            <div class="drawer-spinner"></div>
                            <div style="margin-top:14px;font-size:12.5px;color:var(--text-muted);">Loading…</div>
                        </div>
                    </template>
                    <div x-show="!loading" x-html="drawerHtml"></div>
                </div>
            </div>
        </div>
    </template>
</div>

@endsection

@push('scripts')
<script>
function leavesApp() {
    return {
        drawerOpen: false,
        drawerHtml: '',
        loading: false,

        async openDetail(id) {
            this.drawerOpen = true;
            this.loading = true;
            this.drawerHtml = '';
            try {
                const res = await fetch("{{ url('employee/leaves') }}/" + id, {
                    headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' }
                });
                const data = await res.json();
                this.drawerHtml = data.html;
            } catch (e) {
                this.drawerHtml = '<div style="text-align:center;padding:40px;color:var(--red);">Failed to load.</div>';
            } finally {
                this.loading = false;
            }
        },
        closeDetail() { this.drawerOpen = false; }
    };
}
</script>
@endpush