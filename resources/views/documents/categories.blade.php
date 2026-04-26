@extends('layouts.app')
@section('title', 'Document Categories')
@section('page-title', 'Document Categories')
@section('breadcrumb', 'Documents · Categories')

@section('content')

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

    {{-- Category Cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;align-content:start;">
        @forelse($categories as $cat)
        <div class="card" style="transition:transform .2s,border-color .2s;"
             onmouseover="this.style.transform='translateY(-2px)';this.style.borderColor='var(--accent-border)'"
             onmouseout="this.style.transform='';this.style.borderColor='var(--border)'">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div style="width:44px;height:44px;border-radius:10px;flex-shrink:0;
                            display:flex;align-items:center;justify-content:center;
                            background:{{ $cat->color }}20;border:1px solid {{ $cat->color }}40;">
                    <i class="fa-solid {{ $cat->icon }}" style="font-size:20px;color:{{ $cat->color }};"></i>
                </div>
                <div>
                    <div style="font-size:14px;font-weight:700;color:var(--text-primary);">{{ $cat->name }}</div>
                    @if($cat->requires_expiry)
                    <span class="badge badge-yellow" style="font-size:10px;">Requires Expiry</span>
                    @endif
                </div>
            </div>
            @if($cat->description)
            <div style="font-size:12px;color:var(--text-secondary);margin-bottom:10px;line-height:1.5;">
                {{ $cat->description }}
            </div>
            @endif
            <div style="display:flex;align-items:center;justify-content:space-between;
                        padding-top:10px;border-top:1px solid var(--border);">
                <a href="{{ route('documents.list', ['category' => $cat->id]) }}"
                   style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:500;">
                    {{ $cat->documents_count }} documents →
                </a>
                <span class="badge {{ $cat->is_active ? 'badge-green' : 'badge-red' }}">
                    {{ $cat->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
        @empty
        <div class="card" style="grid-column:1/-1;">
            <div class="empty-state"><i class="fa-solid fa-tags"></i>No categories yet.</div>
        </div>
        @endforelse
    </div>

    {{-- Create Form --}}
    <div class="card" style="position:sticky;top:0;">
        <div class="form-section"><i class="fa-solid fa-plus-circle"></i> New Category</div>
        <form method="POST" action="{{ route('documents.categories.store') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:11px;">
                <div>
                    <label class="form-label">Name <span style="color:var(--red);">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. HR Policies" class="form-input">
                </div>
                <div>
                    <label class="form-label">Icon</label>
                    <input type="text" name="icon" value="fa-folder" placeholder="fa-folder" class="form-input">
                    <div style="font-size:10px;color:var(--text-muted);margin-top:3px;">
                        Font Awesome icon e.g. fa-file-contract, fa-shield
                    </div>
                </div>
                <div>
                    <label class="form-label">Colour</label>
                    <input type="color" name="color" value="#C2531B"
                           style="width:100%;height:38px;background:var(--bg-input);
                                  border:1px solid var(--border-strong);border-radius:8px;
                                  padding:3px;cursor:pointer;outline:none;">
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-textarea"></textarea>
                </div>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-secondary);cursor:pointer;">
                    <input type="checkbox" name="requires_expiry" value="1" style="accent-color:var(--accent);">
                    Requires Expiry Date
                </label>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Create Category
                </button>
            </div>
        </form>

        {{-- Quick Defaults --}}
        <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--border);">
            <div class="section-label" style="margin-bottom:10px;">Quick Add — KUVVET Defaults</div>
            @foreach([
                ['HR Policies',        'fa-file-lines',    '#3B82F6', false],
                ['Contracts',          'fa-file-contract', '#C2531B', false],
                ['Certifications',     'fa-certificate',   '#22C55E', true],
                ['Compliance Docs',    'fa-shield',        '#EF4444', true],
                ['Training Materials', 'fa-graduation-cap','#8B5CF6', false],
                ['Financial Records',  'fa-file-invoice',  '#22C55E', false],
            ] as [$name, $icon, $color, $exp])
            <form method="POST" action="{{ route('documents.categories.store') }}" style="margin-bottom:5px;">
                @csrf
                <input type="hidden" name="name"            value="{{ $name }}">
                <input type="hidden" name="icon"            value="{{ $icon }}">
                <input type="hidden" name="color"           value="{{ $color }}">
                <input type="hidden" name="requires_expiry" value="{{ $exp ? '1' : '0' }}">
                <button type="submit"
                        style="width:100%;padding:7px 10px;background:var(--bg-muted);
                               border:1px solid var(--border);border-radius:6px;
                               color:var(--text-secondary);font-size:12px;cursor:pointer;
                               text-align:left;display:flex;align-items:center;gap:8px;
                               transition:border-color .15s;"
                        onmouseover="this.style.borderColor='var(--accent-border)'"
                        onmouseout="this.style.borderColor='var(--border)'">
                    <i class="fa-solid {{ $icon }}" style="font-size:12px;color:{{ $color }};width:14px;text-align:center;"></i>
                    {{ $name }}
                    @if($exp)
                    <span style="font-size:9px;color:var(--yellow);margin-left:auto;">Expiry req.</span>
                    @endif
                </button>
            </form>
            @endforeach
        </div>
    </div>

</div>
@endsection