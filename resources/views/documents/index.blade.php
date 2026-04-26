@extends('layouts.app')
@section('title', 'Documents')
@section('page-title', 'Document Management')
@section('breadcrumb', 'Documents · Dashboard')

@section('content')

{{-- Stats --}}
@php $totalMB = round($stats['total_size'] / 1048576, 1); @endphp
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:22px;">
    @foreach([
        ['Total Documents', $stats['total'],        'fa-file-lines',  'blue'],
        ['Active',          $stats['active'],        'fa-circle-check','green'],
        ['Expiring (30d)',  $stats['expiring_soon'], 'fa-clock',       'yellow'],
        ['Expired',         $stats['expired'],       'fa-circle-xmark','red'],
        ['Storage Used',    $totalMB . ' MB',        'fa-hard-drive',  'accent'],
    ] as [$label, $val, $icon, $color])
    <div class="stat-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-icon stat-icon-{{ $color }}"><i class="fa-solid {{ $icon }}"></i></div>
        </div>
        <div style="font-size:26px;font-weight:700;color:var(--text-primary);">{{ $val }}</div>
    </div>
    @endforeach
</div>

{{-- Search --}}
<div class="card card-sm" style="margin-bottom:20px;">
    <div style="position:relative;">
        <i class="fa-solid fa-magnifying-glass"
           style="position:absolute;left:14px;top:50%;transform:translateY(-50%);
                  color:var(--text-muted);font-size:14px;"></i>
        <input type="text" id="docSearch"
               placeholder="Search documents by title, number, or tags…"
               autocomplete="off"
               style="width:100%;background:var(--bg-input);border:1px solid var(--border);
                      border-radius:9px;padding:12px 14px 12px 42px;color:var(--text-primary);
                      font-size:14px;outline:none;transition:border-color .2s;"
               onfocus="this.style.borderColor='var(--accent)'"
               onblur="this.style.borderColor='var(--border)'">
        <div id="searchResults"
             style="display:none;position:absolute;top:100%;left:0;right:0;z-index:50;
                    background:var(--bg-card);border:1px solid var(--accent-border);
                    border-radius:9px;margin-top:4px;
                    box-shadow:0 8px 24px rgba(45,31,20,.1);overflow:hidden;">
        </div>
    </div>
</div>

{{-- Quick Links + Upload --}}
<div class="quick-links" style="margin-bottom:20px;">
    <a href="{{ route('documents.list') }}"       class="quick-link ql-blue"><i class="fa-solid fa-list"></i> All Documents</a>
    <a href="{{ route('documents.categories') }}" class="quick-link ql-accent"><i class="fa-solid fa-tags"></i> Categories</a>
    <button onclick="document.getElementById('uploadModal').classList.add('open')"
            class="btn btn-primary btn-sm" style="margin-left:auto;">
        <i class="fa-solid fa-upload"></i> Upload Document
    </button>
</div>

<div class="grid-2" style="margin-bottom:20px;">

    {{-- Categories --}}
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div class="card-title" style="margin-bottom:0;"><i class="fa-solid fa-tags"></i> Document Categories</div>
            <a href="{{ route('documents.categories') }}"
               style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:500;">Manage →</a>
        </div>
        @forelse($categories as $cat)
        <a href="{{ route('documents.list', ['category' => $cat->id]) }}"
           style="display:flex;align-items:center;gap:12px;padding:10px 12px;
                  background:var(--bg-muted);border-radius:9px;margin-bottom:6px;
                  text-decoration:none;border:1px solid var(--border);transition:border-color .15s;"
           onmouseover="this.style.borderColor='var(--accent-border)'"
           onmouseout="this.style.borderColor='var(--border)'">
            <div style="width:36px;height:36px;border-radius:8px;flex-shrink:0;
                        display:flex;align-items:center;justify-content:center;
                        background:{{ $cat->color }}20;border:1px solid {{ $cat->color }}40;">
                <i class="fa-solid {{ $cat->icon }}" style="font-size:15px;color:{{ $cat->color }};"></i>
            </div>
            <div style="flex:1;">
                <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $cat->name }}</div>
                @if($cat->description)
                <div style="font-size:11px;color:var(--text-muted);">{{ Str::limit($cat->description, 50) }}</div>
                @endif
            </div>
            <span style="font-size:13px;font-weight:700;color:var(--accent);">{{ $cat->documents_count }}</span>
        </a>
        @empty
        <div class="empty-state" style="padding:28px;">
            <i class="fa-solid fa-tags"></i>
            No categories yet.
            <a href="{{ route('documents.categories') }}">Create one →</a>
        </div>
        @endforelse
    </div>

    {{-- Type Distribution Chart --}}
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-chart-pie"></i> Documents by Type</div>
        @if($typeDist->sum() > 0)
        <canvas id="typeChart" height="200"></canvas>
        @else
        <div class="empty-state" style="padding:32px;">No documents yet</div>
        @endif
    </div>

</div>

{{-- Expiring Documents Alert --}}
@if($expiringDocuments->count())
<div style="background:var(--yellow-bg);border:1px solid var(--yellow-border);
            border-radius:10px;padding:16px 20px;margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
        <i class="fa-solid fa-triangle-exclamation" style="color:var(--yellow);font-size:16px;"></i>
        <span style="font-size:14px;font-weight:700;color:var(--yellow);">
            {{ $expiringDocuments->count() }} Documents Expiring Within 60 Days
        </span>
        <a href="{{ route('documents.list', ['status' => 'active']) }}"
           style="margin-left:auto;font-size:12px;color:var(--yellow);text-decoration:none;font-weight:600;">
            View all →
        </a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:8px;">
        @foreach($expiringDocuments as $doc)
        <a href="{{ route('documents.show', $doc) }}"
           style="background:var(--bg-card);border:1px solid var(--yellow-border);border-radius:8px;
                  padding:10px 14px;display:flex;align-items:center;gap:10px;
                  text-decoration:none;transition:border-color .15s;"
           onmouseover="this.style.borderColor='var(--yellow)'"
           onmouseout="this.style.borderColor='var(--yellow-border)'">
            <div style="width:34px;height:34px;background:var(--yellow-bg);border-radius:7px;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fa-solid {{ $doc->file_icon }}" style="font-size:14px;color:{{ $doc->file_icon_color }};"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:600;color:var(--text-primary);
                            overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ $doc->title }}
                </div>
                <div style="font-size:10px;color:var(--yellow);font-weight:600;margin-top:1px;">
                    Expires: {{ $doc->expiry_date->format('d M Y') }} ({{ $doc->expiry_date->diffForHumans() }})
                </div>
                @if($doc->employee)
                <div style="font-size:10px;color:var(--text-muted);">{{ $doc->employee->full_name }}</div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif

{{-- Recent Documents --}}
<div class="card card-flush">
    <div style="display:flex;align-items:center;justify-content:space-between;
                padding:14px 20px;border-bottom:1px solid var(--border);">
        <div class="card-title" style="margin-bottom:0;">
            <i class="fa-solid fa-clock-rotate-left"></i> Recently Uploaded
        </div>
        <a href="{{ route('documents.list') }}"
           style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:500;">View all →</a>
    </div>
    @include('documents._document_table', ['documents' => $recentDocuments])
</div>

{{-- Upload Modal --}}
@include('documents._upload_modal')

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
@if($typeDist->sum() > 0)
var typeLabels = @json($typeDist->keys()->map(fn($k) => ucfirst(str_replace('_', ' ', $k))));
var typeData   = @json($typeDist->values());
new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: {
        labels: typeLabels,
        datasets: [{ data: typeData, backgroundColor: ['#3B82F6','#22C55E','#C2531B','#22C55E','#EF4444','#8B5CF6','#F59E0B','#3B82F6','#A89080'], borderColor: '#FFFFFF', borderWidth: 2, hoverOffset: 5 }]
    },
    options: {
        cutout: '60%',
        plugins: {
            legend: { position: 'bottom', labels: { color: '#A89080', font: { family: 'Plus Jakarta Sans', size: 11 }, boxWidth: 10, padding: 8 } },
            tooltip: { backgroundColor: '#FFFFFF', borderColor: '#F0EAE2', borderWidth: 1, titleColor: '#2D1F14', bodyColor: '#6B5347' }
        }
    }
});
@endif

var searchTimeout;
document.getElementById('docSearch').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    var q = this.value.trim();
    var results = document.getElementById('searchResults');
    if (q.length < 2) { results.style.display = 'none'; return; }

    searchTimeout = setTimeout(function() {
        fetch('{{ route("documents.search") }}?q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.length) { results.style.display = 'none'; return; }
                results.innerHTML = data.map(function(d) {
                    return '<a href="' + d.url + '" style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--border);text-decoration:none;transition:background .15s;" onmouseover="this.style.background=\'var(--bg-muted)\'" onmouseout="this.style.background=\'\'">'
                        + '<i class="fa-solid fa-file-lines" style="font-size:13px;color:var(--accent);"></i>'
                        + '<div><div style="font-size:13px;font-weight:600;color:var(--text-primary);">' + d.title + '</div>'
                        + '<div style="font-size:10px;color:var(--text-muted);">' + d.type + (d.category ? ' · ' + d.category : '') + '</div></div>'
                        + '</a>';
                }).join('');
                results.style.display = 'block';
            });
    }, 300);
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('#docSearch') && !e.target.closest('#searchResults')) {
        document.getElementById('searchResults').style.display = 'none';
    }
});
</script>
@endpush

@endsection