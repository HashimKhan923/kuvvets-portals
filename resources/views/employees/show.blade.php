@extends('layouts.app')
@section('title', $employee->full_name)
@section('page-title', $employee->full_name)
@section('breadcrumb', 'Employees · ' . $employee->employee_id)

@section('content')

<div class="grid-sidebar-main">

    {{-- LEFT: Profile Card --}}
    <div>

        <div class="card" style="text-align:center;margin-bottom:16px;">
            <img src="{{ $employee->avatar_url }}"
                 class="avatar avatar-xl"
                 style="display:block;margin:0 auto 14px;"
                 alt="{{ $employee->full_name }}">

            <div style="font-size:16px;font-weight:700;color:var(--text-primary);">
                {{ $employee->full_name }}
            </div>
            <div style="font-size:12px;color:var(--accent);margin:4px 0;">
                {{ $employee->employee_id }}
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:14px;">
                {{ $employee->designation?->title ?? 'No designation' }}
            </div>

            <span class="badge status-{{ $employee->employment_status }}">
                {{ ucfirst($employee->employment_status) }}
            </span>

            @can('employees.edit')
            <div style="margin-top:16px;">
                <a href="{{ route('employees.edit', $employee) }}"
                   class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;">
                    <i class="fa-solid fa-pen"></i> Edit Profile
                </a>
            </div>
            @endcan
        </div>

        <div class="card">
            <div class="section-label">Quick Info</div>
            @foreach([
                ['fa-sitemap',           'Department', $employee->department?->name ?? '—'],
                ['fa-calendar-plus',     'Joined',     $employee->joining_date?->format('d M Y') ?? '—'],
                ['fa-clock-rotate-left', 'Service',    $employee->service_length],
                ['fa-id-card',           'CNIC',       $employee->formatted_cnic ?? '—'],
                ['fa-envelope',          'Work Email', $employee->work_email ?? '—'],
                ['fa-phone',             'Phone',      $employee->personal_phone ?? '—'],
            ] as [$icon, $label, $value])
            <div class="info-row">
                <i class="fa-solid {{ $icon }}"></i>
                <div>
                    <div class="info-row-label">{{ $label }}</div>
                    <div class="info-row-value">{{ $value }}</div>
                </div>
            </div>
            @endforeach
        </div>

    </div>

    {{-- RIGHT: Tabs --}}
    <div>

        <div class="tab-nav">
            <button type="button" class="tab-btn" id="ptab-overview" onclick="switchProfileTab('overview')">
                <i class="fa-solid fa-table-cells"></i> Overview
            </button>
            <button type="button" class="tab-btn" id="ptab-documents" onclick="switchProfileTab('documents')">
                <i class="fa-solid fa-folder"></i> Documents ({{ $employee->documents?->count() ?? 0 }})
            </button>
            <button type="button" class="tab-btn" id="ptab-notes" onclick="switchProfileTab('notes')">
                <i class="fa-solid fa-note-sticky"></i> Notes ({{ $employee->notes?->count() ?? 0 }})
            </button>
        </div>

        {{-- OVERVIEW --}}
        <div id="ppane-overview">

            <div class="card" style="margin-bottom:14px;">
                <div class="form-section">
                    <i class="fa-solid fa-briefcase"></i> Employment
                </div>
                <div class="grid-3" style="gap:12px;">
                    @foreach([
                        ['Type',          ucfirst(str_replace('_', ' ', $employee->employment_type))],
                        ['Probation',     ucfirst(str_replace('_', ' ', $employee->probation_status))],
                        ['Manager',       $employee->manager?->full_name ?? 'None'],
                        ['Probation End', $employee->probation_end_date?->format('d M Y') ?? '—'],
                        ['Confirmation',  $employee->confirmation_date?->format('d M Y') ?? '—'],
                        ['Basic Salary',  'PKR ' . number_format($employee->basic_salary)],
                    ] as [$l, $v])
                    <div class="detail-block">
                        <div class="detail-block-label">{{ $l }}</div>
                        <div class="detail-block-value">{{ $v }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card">
                <div class="form-section">
                    <i class="fa-solid fa-building-columns"></i> Bank & Compliance
                </div>
                <div class="grid-2" style="gap:12px;">
                    @foreach([
                        ['Bank',        $employee->bank_name ?? '—'],
                        ['Account No.', $employee->bank_account_no ?? '—'],
                        ['IBAN',        $employee->bank_iban ?? '—'],
                        ['EOBI No.',    $employee->eobi_number ?? '—'],
                        ['PESSI/SESSI', $employee->pessi_number ?? '—'],
                    ] as [$l, $v])
                    <div>
                        <div class="info-row-label">{{ $l }}</div>
                        <div class="info-row-value" style="margin-top:3px;">{{ $v }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- DOCUMENTS --}}
        <div id="ppane-documents" style="display:none;">

            <div class="card" style="margin-bottom:14px;">
                <form method="POST" action="{{ route('employees.documents.upload', $employee) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="form-section">
                        <i class="fa-solid fa-upload"></i> Upload Document
                    </div>
                    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:12px;align-items:end;margin-bottom:12px;">
                        <div>
                            <label class="form-label">Title</label>
                            <input type="text" name="title" required
                                   placeholder="e.g. Employment Contract" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                @foreach(['cnic'=>'CNIC','passport'=>'Passport','contract'=>'Contract','offer_letter'=>'Offer Letter','degree'=>'Degree','certificate'=>'Certificate','other'=>'Other'] as $v => $l)
                                    <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">File</label>
                            <input type="file" name="document" required
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="form-input">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-upload"></i> Upload
                    </button>
                </form>
            </div>

            @forelse(($employee->documents ?? collect()) as $doc)
            <div class="card card-sm" style="display:flex;align-items:center;gap:14px;margin-bottom:8px;">
                <div class="stat-icon stat-icon-accent" style="flex-shrink:0;">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $doc->title }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        {{ ucfirst(str_replace('_', ' ', $doc->type)) }} · {{ $doc->file_size }} ·
                        @if($doc->expiry_date)
                            Expires:
                            <span style="color:{{ $doc->isExpired() ? 'var(--red)' : ($doc->isExpiringSoon() ? 'var(--yellow)' : 'var(--text-muted)') }}">
                                {{ $doc->expiry_date->format('d M Y') }}
                            </span>
                        @else
                            No expiry
                        @endif
                    </div>
                </div>
                <a href="{{ $doc->file_url }}" target="_blank" class="btn btn-secondary btn-xs">
                    <i class="fa-solid fa-download"></i> View
                </a>
            </div>
            @empty
            <div class="empty-state">
                <i class="fa-solid fa-folder-open"></i>
                No documents uploaded yet
            </div>
            @endforelse

        </div>

        {{-- NOTES --}}
        <div id="ppane-notes" style="display:none;">

            <div class="card" style="margin-bottom:14px;">
                <form method="POST" action="{{ route('employees.notes.store', $employee) }}">
                    @csrf
                    <div class="form-section">
                        <i class="fa-solid fa-note-sticky"></i> Add Note
                    </div>
                    <div class="grid-2" style="gap:12px;margin-bottom:12px;">
                        <input type="text" name="title" placeholder="Note title (optional)" class="form-input">
                        <select name="type" class="form-select">
                            @foreach(['general'=>'General','warning'=>'Warning','commendation'=>'Commendation','hr_note'=>'HR Note','performance'=>'Performance'] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <textarea name="body" required rows="3"
                              placeholder="Write your note here…"
                              class="form-textarea" style="margin-bottom:12px;"></textarea>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--text-muted);cursor:pointer;">
                            <input type="checkbox" name="is_private" value="1"
                                   style="accent-color:var(--accent);"> Private (HR only)
                        </label>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-plus"></i> Add Note
                        </button>
                    </div>
                </form>
            </div>

            @forelse(($employee->notes ?? collect())->sortByDesc('created_at') as $note)
            @php $badge = $note->type_badge; @endphp
            <div class="card card-sm" style="margin-bottom:8px;border-left:3px solid {{ $badge['color'] }};">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                            {{ $badge['label'] }}
                        </span>
                        @if($note->title)
                            <span style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $note->title }}</span>
                        @endif
                        @if($note->is_private)
                            <span style="font-size:10px;color:var(--text-muted);">
                                <i class="fa-solid fa-lock"></i> Private
                            </span>
                        @endif
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        {{ $note->author->name }} · {{ $note->created_at->diffForHumans() }}
                    </div>
                </div>
                <div style="font-size:13px;color:var(--text-secondary);line-height:1.6;">
                    {{ $note->body }}
                </div>
            </div>
            @empty
            <div class="empty-state">
                <i class="fa-solid fa-note-sticky"></i>
                No notes yet
            </div>
            @endforelse

        </div>

    </div>

</div>

@push('scripts')
<script>
function switchProfileTab(active) {
    ['overview', 'documents', 'notes'].forEach(function(t) {
        document.getElementById('ppane-' + t).style.display = t === active ? 'block' : 'none';
        document.getElementById('ptab-' + t).classList.toggle('active', t === active);
    });
}
switchProfileTab('overview');
</script>
@endpush

@endsection