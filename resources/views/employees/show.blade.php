@extends('layouts.app')
@section('title', $employee->full_name)
@section('page-title', $employee->full_name)
@section('breadcrumb', 'Employees · ' . $employee->employee_id)

@section('content')
<div class="grid-sidebar-main">

    <div>
        <div class="card text-center mb-4">
            <img src="{{ $employee->avatar_url }}" class="avatar avatar-xl mx-auto mb-3">
            <div style="font-size:16px;font-weight:700;color:var(--text-primary);">{{ $employee->full_name }}</div>
            <div class="text-accent" style="font-size:12px;margin:3px 0;">{{ $employee->employee_id }}</div>
            <div class="text-muted" style="font-size:12px;margin-bottom:12px;">{{ $employee->designation?->title ?? 'No designation' }}</div>
            <span class="badge status-{{ $employee->employment_status }}">{{ ucfirst($employee->employment_status) }}</span>
            @can('employees.edit')
            <div class="mt-3">
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;">
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
            ] as [$icon,$label,$value])
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

    <div>
        <div class="tab-nav">
            @foreach([
                ['overview',  'fa-table-cells', 'Overview'],
                ['documents', 'fa-folder',      'Documents (' . $employee->documents->count() . ')'],
                ['notes',     'fa-note-sticky', 'Notes (' . $employee->notes->count() . ')'],
            ] as [$id,$icon,$label])
            <button type="button" class="tab-btn" id="ptab-{{ $id }}" onclick="switchProfileTab('{{ $id }}')">
                <i class="fa-solid {{ $icon }}"></i> {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- OVERVIEW --}}
        <div id="ppane-overview">
            <div class="card mb-3">
                <div class="form-section"><i class="fa-solid fa-briefcase"></i> Employment</div>
                <div class="grid-3">
                    @foreach([
                        ['Type',          ucfirst(str_replace('_',' ',$employee->employment_type))],
                        ['Probation',     ucfirst(str_replace('_',' ',$employee->probation_status))],
                        ['Manager',       $employee->manager?->full_name ?? 'None'],
                        ['Probation End', $employee->probation_end_date?->format('d M Y') ?? '—'],
                        ['Confirmation',  $employee->confirmation_date?->format('d M Y') ?? '—'],
                        ['Basic Salary',  'PKR ' . number_format($employee->basic_salary)],
                    ] as [$l,$v])
                    <div class="detail-block">
                        <div class="detail-block-label">{{ $l }}</div>
                        <div class="detail-block-value">{{ $v }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="card">
                <div class="form-section"><i class="fa-solid fa-building-columns"></i> Bank & Compliance</div>
                <div class="grid-2">
                    @foreach([
                        ['Bank',        $employee->bank_name ?? '—'],
                        ['Account No.', $employee->bank_account_no ?? '—'],
                        ['IBAN',        $employee->bank_iban ?? '—'],
                        ['EOBI No.',    $employee->eobi_number ?? '—'],
                        ['PESSI/SESSI', $employee->pessi_number ?? '—'],
                    ] as [$l,$v])
                    <div>
                        <div class="info-row-label">{{ $l }}</div>
                        <div class="info-row-value mt-1">{{ $v }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- DOCUMENTS --}}
        <div id="ppane-documents" style="display:none;">
            <div class="card mb-3">
                <form method="POST" action="{{ route('employees.documents.upload', $employee) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-section"><i class="fa-solid fa-upload"></i> Upload Document</div>
                    <div class="grid-4-auto mb-3" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:12px;align-items:end;">
                        <div>
                            <label class="form-label">Title</label>
                            <input type="text" name="title" required placeholder="e.g. Employment Contract" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                @foreach(['cnic'=>'CNIC','passport'=>'Passport','contract'=>'Contract','offer_letter'=>'Offer Letter','degree'=>'Degree','certificate'=>'Certificate','other'=>'Other'] as $v=>$l)
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
                            <input type="file" name="document" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="form-input">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-upload"></i> Upload</button>
                </form>
            </div>
            @forelse($employee->documents as $doc)
            <div class="card card-sm mb-2 flex items-center gap-3">
                <div class="stat-icon stat-icon-accent"><i class="fa-solid fa-file-lines"></i></div>
                <div style="flex:1;">
                    <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $doc->title }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        {{ ucfirst(str_replace('_',' ',$doc->type)) }} · {{ $doc->file_size }} ·
                        @if($doc->expiry_date)
                            Expires: <span class="{{ $doc->isExpired() ? 'text-red' : ($doc->isExpiringSoon() ? 'text-yellow' : 'text-muted') }}">{{ $doc->expiry_date->format('d M Y') }}</span>
                        @else No expiry @endif
                    </div>
                </div>
                <a href="{{ $doc->file_url }}" target="_blank" class="btn btn-secondary btn-xs"><i class="fa-solid fa-download"></i> View</a>
            </div>
            @empty
            <div class="empty-state"><i class="fa-solid fa-folder-open"></i>No documents uploaded yet</div>
            @endforelse
        </div>

        {{-- NOTES --}}
        <div id="ppane-notes" style="display:none;">
            <div class="card mb-3">
                <form method="POST" action="{{ route('employees.notes.store', $employee) }}">
                    @csrf
                    <div class="form-section"><i class="fa-solid fa-note-sticky"></i> Add Note</div>
                    <div class="grid-2 mb-3">
                        <input type="text" name="title" placeholder="Note title (optional)" class="form-input">
                        <select name="type" class="form-select">
                            @foreach(['general'=>'General','warning'=>'Warning','commendation'=>'Commendation','hr_note'=>'HR Note','performance'=>'Performance'] as $v=>$l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <textarea name="body" required rows="3" placeholder="Write your note here…" class="form-textarea mb-3"></textarea>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2" style="font-size:12px;color:var(--text-muted);cursor:pointer;">
                            <input type="checkbox" name="is_private" value="1" style="accent-color:var(--accent);"> Private (HR only)
                        </label>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add Note</button>
                    </div>
                </form>
            </div>
            @forelse(($employee->notes ?? collect())->sortByDesc('created_at') as $note)
            @php $badge = $note->type_badge; @endphp
            <div class="card card-sm mb-2" style="border-left:3px solid {{ $badge['color'] }};">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">{{ $badge['label'] }}</span>
                        @if($note->title)<span style="font-size:13px;font-weight:600;">{{ $note->title }}</span>@endif
                        @if($note->is_private)<span class="text-muted" style="font-size:10px;"><i class="fa-solid fa-lock"></i> Private</span>@endif
                    </div>
                    <div class="text-muted" style="font-size:11px;">{{ $note->author->name }} · {{ $note->created_at->diffForHumans() }}</div>
                </div>
                <div style="font-size:13px;color:var(--text-secondary);line-height:1.6;">{{ $note->body }}</div>
            </div>
            @empty
            <div class="empty-state"><i class="fa-solid fa-note-sticky"></i>No notes yet</div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function switchProfileTab(active) {
    ['overview','documents','notes'].forEach(t => {
        document.getElementById('ppane-' + t).style.display = t === active ? 'block' : 'none';
        document.getElementById('ptab-' + t).classList.toggle('active', t === active);
    });
}
switchProfileTab('overview');
</script>
@endpush
@endsection