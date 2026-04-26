@extends('layouts.app')
@section('title', 'Training Programs')
@section('page-title', 'Training Programs')
@section('breadcrumb', 'Training · Programs')

@section('content')

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

    {{-- Programs --}}
    <div>
        {{-- Filter --}}
        <div class="card card-sm" style="margin-bottom:16px;">
            <div class="toolbar">
                <form method="GET" action="{{ route('training.programs') }}" class="toolbar" style="flex:1;">
                    <div class="toolbar-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search programs…" class="form-input">
                    </div>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach(['safety','technical','soft_skills','compliance','leadership','equipment','onboarding','other'] as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $cat)) }}
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                </form>
                <a href="{{ route('training.sessions.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-calendar-plus"></i> Schedule Session
                </a>
            </div>
        </div>

        {{-- Program Cards --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;">
            @forelse($programs as $program)
            @php $catBadge = $program->category_badge; @endphp
            <div class="card" style="display:flex;flex-direction:column;transition:transform .2s,border-color .2s;"
                 onmouseover="this.style.transform='translateY(-2px)';this.style.borderColor='var(--accent-border)'"
                 onmouseout="this.style.transform='';this.style.borderColor='var(--border)'">

                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;">
                    <div style="flex:1;">
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:6px;">
                            <span class="badge" style="background:{{ $catBadge['bg'] }};color:{{ $catBadge['color'] }};border:1px solid {{ $catBadge['border'] }};font-size:10px;">
                                {{ ucfirst(str_replace('_', ' ', $program->category)) }}
                            </span>
                            @if($program->is_mandatory)
                            <span class="badge badge-red" style="font-size:10px;">Mandatory</span>
                            @endif
                        </div>
                        <div style="font-size:14px;font-weight:700;color:var(--text-primary);">
                            {{ $program->title }}
                        </div>
                        <div style="font-size:10px;color:var(--text-muted);margin-top:2px;">
                            {{ $program->code }}
                        </div>
                    </div>
                </div>

                @if($program->description)
                <p style="font-size:12px;color:var(--text-secondary);line-height:1.5;margin-bottom:12px;flex:1;">
                    {{ Str::limit($program->description, 100) }}
                </p>
                @endif

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:12px;
                            padding:10px;background:var(--bg-muted);border-radius:8px;font-size:11px;">
                    @foreach([
                        ['fa-clock',        $program->duration_hours . 'h duration'],
                        ['fa-chalkboard',   ucfirst(str_replace('_', ' ', $program->delivery_method))],
                        ['fa-money-bill',   'PKR ' . number_format($program->cost_per_person)],
                        ['fa-calendar',     $program->sessions_count . ' session(s)'],
                    ] as [$icon, $value])
                    <div style="display:flex;align-items:center;gap:5px;color:var(--text-secondary);">
                        <i class="fa-solid {{ $icon }}" style="font-size:10px;color:var(--accent);width:12px;text-align:center;"></i>
                        {{ $value }}
                    </div>
                    @endforeach
                </div>

                @if($program->certificate_name)
                <div style="font-size:11px;color:var(--accent);margin-bottom:10px;
                            padding:6px 10px;background:var(--accent-bg);
                            border:1px solid var(--accent-border);border-radius:6px;">
                    <i class="fa-solid fa-certificate" style="margin-right:5px;"></i>
                    {{ $program->certificate_name }}
                    @if($program->certificate_validity_months)
                    · Valid {{ $program->certificate_validity_months }}mo
                    @endif
                </div>
                @endif

                <a href="{{ route('training.sessions.create', ['program' => $program->id]) }}"
                   class="btn btn-secondary btn-sm" style="justify-content:center;margin-top:auto;">
                    <i class="fa-solid fa-calendar-plus"></i> Schedule Session
                </a>
            </div>
            @empty
            <div class="card" style="grid-column:1/-1;">
                <div class="empty-state">
                    <i class="fa-solid fa-book-open"></i>
                    No training programs yet. Create one using the form →
                </div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Create Program Form --}}
    <div class="card" style="position:sticky;top:0;">
        <div class="form-section">
            <i class="fa-solid fa-plus-circle"></i> New Training Program
        </div>
        <form method="POST" action="{{ route('training.programs.store') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:11px;">
                <div>
                    <label class="form-label">Program Title <span style="color:var(--red);">*</span></label>
                    <input type="text" name="title" required
                           placeholder="e.g. Forklift Safety Certification"
                           class="form-input">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Category <span style="color:var(--red);">*</span></label>
                        <select name="category" required class="form-select">
                            @foreach(['safety'=>'Safety','technical'=>'Technical','soft_skills'=>'Soft Skills','compliance'=>'Compliance','leadership'=>'Leadership','equipment'=>'Equipment','onboarding'=>'Onboarding','other'=>'Other'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Delivery Method</label>
                        <select name="delivery_method" class="form-select">
                            @foreach(['classroom'=>'Classroom','online'=>'Online','on_job'=>'On the Job','workshop'=>'Workshop','seminar'=>'Seminar','mentoring'=>'Mentoring','blended'=>'Blended'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Duration (Hours)</label>
                        <input type="number" name="duration_hours" value="4" min="1" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Cost/Person (PKR)</label>
                        <input type="number" name="cost_per_person" value="0" min="0" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label">Training Provider</label>
                    <input type="text" name="provider" placeholder="e.g. Internal / PSQCA" class="form-input">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <label class="form-label">Certificate Name</label>
                        <input type="text" name="certificate_name"
                               placeholder="e.g. Forklift Operator Cert" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Validity (Months)</label>
                        <input type="number" name="certificate_validity_months"
                               placeholder="0 = no expiry" class="form-input">
                    </div>
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-textarea"></textarea>
                </div>
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text-secondary);cursor:pointer;">
                    <input type="checkbox" name="is_mandatory" value="1" style="accent-color:var(--accent);">
                    Mark as Mandatory Training
                </label>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Create Program
                </button>
            </div>
        </form>
    </div>

</div>
@endsection