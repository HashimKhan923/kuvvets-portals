@extends('layouts.app')
@section('title','Audit Log')
@section('page-title','Audit Log')
@section('breadcrumb','Settings · Audit Log')

@section('content')

{{-- Filter --}}
<div class="card card-gold" style="padding:14px 18px;margin-bottom:18px;
     display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <form method="GET" action="{{ route('settings.audit-log') }}"
          style="display:flex;gap:10px;align-items:center;flex:1;flex-wrap:wrap;">
        <select name="user"
                style="background:var(--white);border:1px solid var(--border);border-radius:7px;
                       padding:8px 12px;color:var(--text-secondary);font-size:13px;outline:none;
                       min-width:160px;">
            <option value="">All Users</option>
            @foreach($users as $u)
            <option value="{{ $u->id }}" {{ request('user')==$u->id?'selected':'' }}>
                {{ $u->name }}
            </option>
            @endforeach
        </select>
        <input type="text" name="action" value="{{ request('action') }}"
               placeholder="Filter by action…"
               style="background:var(--white);border:1px solid var(--border);border-radius:7px;
                      padding:8px 12px;color:var(--text-primary);font-size:13px;outline:none;
                      min-width:180px;">
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               style="background:var(--white);border:1px solid var(--border);border-radius:7px;
                      padding:8px 12px;color:var(--text-primary);font-size:13px;outline:none;">
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               style="background:var(--white);border:1px solid var(--border);border-radius:7px;
                      padding:8px 12px;color:var(--text-primary);font-size:13px;outline:none;">
        <button type="submit" class="btn-gold" style="padding:8px 14px;font-size:13px;">
            <i class="fa-solid fa-filter"></i>
        </button>
        @if(request()->hasAny(['user','action','date_from','date_to']))
        <a href="{{ route('settings.audit-log') }}"
           style="font-size:12px;color:var(--text-muted);text-decoration:none;">
            <i class="fa-solid fa-xmark"></i> Clear
        </a>
        @endif
    </form>
</div>

{{-- Log Table --}}
<div class="card card-gold" style="overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#F7F9FC;border-bottom:1px solid var(--border-light);">
                @foreach(['Date & Time','User','Action','Model','IP Address'] as $h)
                <th style="padding:10px 14px;text-align:left;font-size:10px;color:var(--text-muted);
                           letter-spacing:.7px;font-weight:600;text-transform:uppercase;">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            @php
                $actionColors = [
                    'login'    => ['bg'=>'#F0FBF4','color'=>'#2D7A4F'],
                    'logout'   => ['bg'=>'#F7FAFC','color'=>'#718096'],
                    'created'  => ['bg'=>'#EBF8FF','color'=>'#2B6CB0'],
                    'updated'  => ['bg'=>'#FFFBEB','color'=>'#B7791F'],
                    'deleted'  => ['bg'=>'#FFF5F5','color'=>'#C53030'],
                    'exported' => ['bg'=>'#FAF5FF','color'=>'#6B46C1'],
                    'default'  => ['bg'=>'#F7FAFC','color'=>'#718096'],
                ];
                $ac = collect($actionColors)
                    ->first(fn($v,$k) => str_contains(strtolower($log->action), $k))
                    ?? $actionColors['default'];
            @endphp
            <tr class="table-row">
                <td style="padding:10px 14px;">
                    <div style="font-size:12px;color:var(--text-primary);font-weight:500;">
                        {{ $log->created_at->setTimezone('Asia/Karachi')->format('d M Y') }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        {{ $log->created_at->setTimezone('Asia/Karachi')->format('h:i:s A') }} PKT
                    </div>
                </td>
                <td style="padding:10px 14px;">
                    @if($log->user)
                    <div style="display:flex;align-items:center;gap:8px;">
                        <img src="{{ $log->user->avatar_url }}"
                             style="width:26px;height:26px;border-radius:50%;
                                    object-fit:cover;border:1px solid var(--gold-mid);">
                        <div>
                            <div style="font-size:12px;color:var(--text-primary);font-weight:500;">
                                {{ $log->user->name }}
                            </div>
                            <div style="font-size:10px;color:var(--text-light);">
                                {{ $log->user->display_role }}
                            </div>
                        </div>
                    </div>
                    @else
                    <span style="font-size:12px;color:var(--text-light);">System</span>
                    @endif
                </td>
                <td style="padding:10px 14px;">
                    <span style="font-size:11px;font-weight:500;
                                 background:{{ $ac['bg'] }};color:{{ $ac['color'] }};
                                 border:1px solid {{ $ac['color'] }}30;
                                 border-radius:20px;padding:3px 10px;">
                        {{ str_replace('_',' ',$log->action) }}
                    </span>
                </td>
                <td style="padding:10px 14px;font-size:12px;color:var(--text-secondary);">
                    @if($log->auditable_type)
                    <span style="font-size:10px;background:var(--cream-warm);
                                 color:var(--text-muted);border:1px solid var(--border-light);
                                 border-radius:4px;padding:2px 7px;">
                        {{ class_basename($log->auditable_type) }}
                        @if($log->auditable_id) #{{ $log->auditable_id }} @endif
                    </span>
                    @else
                    <span style="color:var(--text-light);">—</span>
                    @endif
                </td>
                <td style="padding:10px 14px;font-size:11px;color:var(--text-muted);
                           font-family:'Space Grotesk',sans-serif;">
                    {{ $log->ip_address ?? '—' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5"
                    style="padding:40px;text-align:center;color:var(--text-light);font-size:13px;">
                    <i class="fa-solid fa-shield-check"
                       style="font-size:32px;display:block;margin-bottom:12px;color:var(--border);"></i>
                    No audit logs found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div style="padding:12px 20px;border-top:1px solid var(--border-light);
                display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:12px;color:var(--text-muted);">
            Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }}
            of {{ $logs->total() }}
        </span>
        <div style="display:flex;gap:4px;">
            @if($logs->onFirstPage())
                <span style="padding:5px 10px;background:var(--white);
                             border:1px solid var(--border);border-radius:5px;
                             font-size:12px;color:var(--text-light);">← Prev</span>
            @else
                <a href="{{ $logs->previousPageUrl() }}"
                   style="padding:5px 10px;background:var(--white);border:1px solid var(--border);
                          border-radius:5px;font-size:12px;color:var(--text-secondary);
                          text-decoration:none;">← Prev</a>
            @endif
            @if($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}"
                   class="btn-gold" style="padding:5px 12px;font-size:12px;">Next →</a>
            @else
                <span style="padding:5px 10px;background:var(--white);
                             border:1px solid var(--border);border-radius:5px;
                             font-size:12px;color:var(--text-light);">Next →</span>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection