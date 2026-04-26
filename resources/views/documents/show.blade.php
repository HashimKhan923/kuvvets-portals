@extends('layouts.app')
@section('title', $document->title)
@section('page-title', $document->title)
@section('breadcrumb', 'Documents · ' . ($document->category?->name ?? 'Library'))

@section('content')

<div class="grid-sidebar-main">

    {{-- LEFT: Document Info --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- File Card --}}
        <div class="card" style="text-align:center;">
            <div style="width:80px;height:80px;margin:0 auto 14px;
                        background:{{ $document->file_icon_color }}15;
                        border:2px solid {{ $document->file_icon_color }}30;
                        border-radius:16px;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid {{ $document->file_icon }}"
                   style="font-size:36px;color:{{ $document->file_icon_color }};"></i>
            </div>

            <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">
                {{ $document->title }}
            </div>
            @if($document->document_number)
            <div style="font-size:11px;color:var(--accent);margin-bottom:6px;font-weight:600;">
                {{ $document->document_number }}
            </div>
            @endif

            <div style="display:flex;gap:5px;justify-content:center;flex-wrap:wrap;margin-bottom:12px;">
                @php $sBadge = $document->status_badge; $tBadge = $document->type_badge; @endphp
                <span class="badge" style="background:{{ $sBadge['bg'] }};color:{{ $sBadge['color'] }};border:1px solid {{ $sBadge['border'] }};">{{ ucfirst($document->status) }}</span>
                <span class="badge" style="background:{{ $tBadge['bg'] }};color:{{ $tBadge['color'] }};border:1px solid {{ $tBadge['border'] }};">{{ ucfirst(str_replace('_', ' ', $document->type)) }}</span>
                <span class="badge badge-accent" style="font-size:10px;">v{{ $document->version }}</span>
            </div>

            <a href="{{ route('documents.download', $document) }}"
               class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:8px;">
                <i class="fa-solid fa-download"></i> Download
            </a>
            <div style="font-size:11px;color:var(--text-muted);">
                {{ $document->file_size_formatted }} ·
                {{ strtoupper($document->file_type) }} ·
                {{ $document->download_count }} downloads
            </div>
        </div>

        {{-- Document Info --}}
        <div class="card">
            <div class="section-label">Document Info</div>
            @foreach([
                ['Category',     $document->category?->name ?? '—'],
                ['Access Level', ucfirst(str_replace('_', ' ', $document->access_level))],
                ['Issue Date',   $document->issue_date?->format('d M Y') ?? '—'],
                ['Expiry Date',  $document->expiry_date?->format('d M Y') ?? 'No Expiry'],
                ['Uploaded By',  $document->uploader->name],
                ['Uploaded',     $document->created_at->format('d M Y')],
                ['Views',        $document->view_count],
            ] as [$l, $v])
            <div style="display:flex;justify-content:space-between;padding:6px 0;
                        border-bottom:1px solid var(--border);font-size:12px;">
                <span style="color:var(--text-muted);">{{ $l }}</span>
                <span style="font-weight:500;
                             color:{{ $l === 'Expiry Date' && $document->isExpired() ? 'var(--red)' : 'var(--text-primary)' }};">
                    {{ $v }} @if($l === 'Expiry Date' && $document->isExpired()) ⚠️ @endif
                </span>
            </div>
            @endforeach
            @if($document->tags)
            <div style="padding-top:8px;">
                <div style="font-size:10px;color:var(--text-muted);margin-bottom:5px;">Tags</div>
                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                    @foreach(explode(',', $document->tags) as $tag)
                    <span class="badge badge-accent" style="font-size:10px;">{{ trim($tag) }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Linked Employee --}}
        @if($document->employee)
        <div class="card">
            <div class="section-label">Linked Employee</div>
            <div style="display:flex;align-items:center;gap:12px;margin-top:8px;">
                <img src="{{ $document->employee->avatar_url }}"
                     class="avatar" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--accent-border);">
                <div>
                    <div style="font-size:13px;font-weight:700;color:var(--text-primary);">
                        {{ $document->employee->full_name }}
                    </div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        {{ $document->employee->designation?->title ?? '—' }}
                    </div>
                    <a href="{{ route('documents.employee', $document->employee) }}"
                       style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:500;">
                        View all documents →
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Version History --}}
        @if($allVersions->count() > 1)
        <div class="card">
            <div class="section-label">Version History</div>
            @foreach($allVersions as $ver)
            <div style="display:flex;align-items:center;justify-content:space-between;
                        padding:7px 0;border-bottom:1px solid var(--border);">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="badge badge-accent" style="font-size:10px;">v{{ $ver->version }}</span>
                    <span style="font-size:11px;color:var(--text-muted);">{{ $ver->created_at->format('d M Y') }}</span>
                    @if($ver->is_latest_version)
                    <span class="badge badge-green" style="font-size:9px;">Latest</span>
                    @endif
                </div>
                <a href="{{ route('documents.download', $ver) }}"
                   style="font-size:11px;color:var(--accent);text-decoration:none;">
                    <i class="fa-solid fa-download"></i>
                </a>
            </div>
            @endforeach
        </div>
        @endif

    </div>

    {{-- RIGHT: Edit + Version Upload --}}
    <div>
        <div class="tab-nav">
            @foreach([
                ['details',  'fa-info-circle', 'Details & Edit'],
                ['versions', 'fa-code-branch', 'Upload Version'],
            ] as [$id, $icon, $label])
            <button type="button" class="tab-btn" id="dtab-{{ $id }}"
                    onclick="switchDocTab('{{ $id }}')">
                <i class="fa-solid {{ $icon }}"></i> {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- DETAILS TAB --}}
        <div id="dpane-details">
            <div class="card">
                <div class="form-section"><i class="fa-solid fa-pen"></i> Edit Document Details</div>
                <form method="POST" action="{{ route('documents.update', $document) }}">
                    @csrf @method('PUT')
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div style="grid-column:span 2;">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" required value="{{ old('title', $document->title) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Document Number</label>
                            <input type="text" name="document_number" value="{{ old('document_number', $document->document_number) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Type</label>
                            <select name="type" required class="form-select">
                                @foreach(['policy'=>'Policy','procedure'=>'Procedure','contract'=>'Contract','certificate'=>'Certificate','compliance'=>'Compliance','hr_document'=>'HR Document','legal'=>'Legal','financial'=>'Financial','training'=>'Training','other'=>'Other'] as $v => $l)
                                <option value="{{ $v }}" {{ old('type', $document->type) === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['active'=>'Active','expired'=>'Expired','archived'=>'Archived','draft'=>'Draft'] as $v => $l)
                                <option value="{{ $v }}" {{ old('status', $document->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Access Level</label>
                            <select name="access_level" class="form-select">
                                @foreach(['public'=>'All Staff','hr_only'=>'HR Only','management'=>'Management','private'=>'Private'] as $v => $l)
                                <option value="{{ $v }}" {{ old('access_level', $document->access_level) === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Issue Date</label>
                            <input type="date" name="issue_date" value="{{ old('issue_date', $document->issue_date?->format('Y-m-d')) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Expiry Date</label>
                            <input type="date" name="expiry_date" value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}" class="form-input">
                        </div>
                        <div style="grid-column:span 2;">
                            <label class="form-label">Tags</label>
                            <input type="text" name="tags" value="{{ old('tags', $document->tags) }}" placeholder="policy, HR, 2024" class="form-input">
                        </div>
                        <div style="grid-column:span 2;">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-textarea">{{ old('description', $document->description) }}</textarea>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:16px;">
                        <form method="POST" action="{{ route('documents.destroy', $document) }}"
                              onsubmit="return confirm('Delete this document? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </form>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- VERSION TAB --}}
        <div id="dpane-versions" style="display:none;">
            <div class="card">
                <div class="form-section">
                    <i class="fa-solid fa-code-branch"></i>
                    Upload New Version
                    <span style="font-size:11px;color:var(--text-muted);font-weight:400;text-transform:none;">
                        (Current: v{{ $document->version }})
                    </span>
                </div>
                <form method="POST" action="{{ route('documents.version', $document) }}" enctype="multipart/form-data">
                    @csrf
                    <div style="margin-bottom:14px;">
                        <label class="form-label">New File <span style="color:var(--red);">*</span></label>
                        <div style="border:2px dashed var(--accent-border);border-radius:10px;padding:24px;
                                    text-align:center;cursor:pointer;background:var(--accent-bg);transition:border-color .2s;"
                             onclick="document.getElementById('versionFile').click()"
                             onmouseover="this.style.borderColor='var(--accent)'"
                             onmouseout="this.style.borderColor='var(--accent-border)'">
                            <i class="fa-solid fa-code-branch" style="font-size:24px;color:var(--accent);display:block;margin-bottom:8px;"></i>
                            <div style="font-size:13px;color:var(--text-secondary);">Click to upload new version</div>
                            <div id="versionFileName" style="font-size:12px;color:var(--green);margin-top:6px;font-weight:600;"></div>
                        </div>
                        <input type="file" id="versionFile" name="file" required style="display:none;"
                               onchange="document.getElementById('versionFileName').textContent = this.files[0] ? this.files[0].name : ''">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label class="form-label">Change Notes</label>
                        <textarea name="description" rows="2" class="form-textarea"
                                  placeholder="What changed in this version?"></textarea>
                    </div>
                    <div class="flash flash-warning" style="margin-bottom:14px;">
                        <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px;"></i>
                        Uploading a new version will mark the current version (v{{ $document->version }}) as superseded.
                        Previous versions remain accessible in history.
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-upload"></i> Upload as v{{ $document->version + 1 }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function switchDocTab(active) {
    ['details', 'versions'].forEach(function(t) {
        document.getElementById('dpane-' + t).style.display = t === active ? 'block' : 'none';
        document.getElementById('dtab-' + t).classList.toggle('active', t === active);
    });
}
switchDocTab('details');
</script>
@endpush

@endsection