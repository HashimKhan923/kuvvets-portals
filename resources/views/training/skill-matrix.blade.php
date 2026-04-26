@extends('layouts.app')
@section('title', 'Skill Matrix')
@section('page-title', 'Skill Matrix')
@section('breadcrumb', 'Training · Skill Matrix')

@section('content')

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start;">

    {{-- Matrix --}}
    <div>

        {{-- Filter --}}
        <div class="card card-sm" style="margin-bottom:16px;">
            <div class="toolbar">
                <form method="GET" action="{{ route('training.skill-matrix') }}" class="toolbar">
                    <select name="department" class="form-select" style="min-width:180px;">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department') == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-filter"></i>
                    </button>
                </form>
                <div style="font-size:12px;color:var(--text-muted);">
                    {{ $employees->count() }} employees · {{ $skills->count() }} skills
                </div>
            </div>
        </div>

        @if($skills->isEmpty())
        <div class="card">
            <div class="empty-state">
                <i class="fa-solid fa-table-cells"></i>
                No skills defined yet. Add skills using the form →
            </div>
        </div>
        @else

        {{-- Legend --}}
        <div style="display:flex;gap:12px;margin-bottom:12px;flex-wrap:wrap;">
            @foreach([
                ['Not Assessed', 'var(--bg-muted)',    'var(--border)',        'var(--text-muted)'],
                ['Beginner',     'var(--yellow-bg)',   'var(--yellow-border)', 'var(--yellow)'],
                ['Intermediate', 'var(--blue-bg)',     'var(--blue-border)',   'var(--blue)'],
                ['Advanced',     'var(--green-bg)',    'var(--green-border)',  'var(--green)'],
                ['Expert',       'var(--accent-bg)',   'var(--accent-border)', 'var(--accent)'],
            ] as [$label, $bg, $border, $color])
            <div style="display:flex;align-items:center;gap:5px;">
                <div style="width:14px;height:14px;background:{{ $bg }};
                            border:1px solid {{ $border }};border-radius:3px;"></div>
                <span style="font-size:11px;color:{{ $color }};">{{ $label }}</span>
            </div>
            @endforeach
        </div>

        {{-- Matrix Table --}}
        <div class="card card-flush" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:{{ max(600, $skills->count() * 100 + 200) }}px;">
                <thead>
                    <tr style="background:var(--bg-muted);border-bottom:1px solid var(--border);">
                        <th style="padding:10px 14px;text-align:left;font-size:11px;color:var(--text-muted);
                                   font-weight:700;text-transform:uppercase;letter-spacing:.5px;
                                   min-width:180px;position:sticky;left:0;background:var(--bg-muted);z-index:1;">
                            Employee
                        </th>
                        @foreach($skills as $skill)
                        <th style="padding:10px 8px;text-align:center;font-size:10px;color:var(--text-muted);
                                   font-weight:700;max-width:90px;min-width:80px;">
                            <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:90px;"
                                 title="{{ $skill->name }}">{{ $skill->name }}</div>
                            @if($skill->category)
                            <div style="font-size:9px;color:var(--text-muted);font-weight:400;">{{ $skill->category }}</div>
                            @endif
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                    @php $empSkillMap = $emp->skills->keyBy('skill_id'); @endphp
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 14px;position:sticky;left:0;background:var(--bg-card);
                                   z-index:1;border-right:1px solid var(--border);">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <img src="{{ $emp->avatar_url }}"
                                     class="avatar avatar-sm" style="flex-shrink:0;">
                                <div>
                                    <div style="font-size:12px;font-weight:600;color:var(--text-primary);white-space:nowrap;">
                                        {{ $emp->full_name }}
                                    </div>
                                    <div style="font-size:10px;color:var(--text-muted);">
                                        {{ $emp->department?->name ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        @foreach($skills as $skill)
                        @php
                            $empSkill = $empSkillMap->get($skill->id);
                            $levelMap = [
                                null           => ['bg'=>'var(--bg-muted)',   'border'=>'var(--border)',        'color'=>'var(--border-strong)', 'label'=>'—'],
                                'beginner'     => ['bg'=>'var(--yellow-bg)', 'border'=>'var(--yellow-border)', 'color'=>'var(--yellow)',        'label'=>'B'],
                                'intermediate' => ['bg'=>'var(--blue-bg)',   'border'=>'var(--blue-border)',   'color'=>'var(--blue)',          'label'=>'I'],
                                'advanced'     => ['bg'=>'var(--green-bg)',  'border'=>'var(--green-border)',  'color'=>'var(--green)',         'label'=>'A'],
                                'expert'       => ['bg'=>'var(--accent-bg)', 'border'=>'var(--accent-border)','color'=>'var(--accent)',        'label'=>'E'],
                            ];
                            $lc = $levelMap[$empSkill?->level] ?? $levelMap[null];
                        @endphp
                        <td style="padding:6px;text-align:center;">
                            <div class="skill-cell"
                                 onclick="openSkillModal({{ $emp->id }}, {{ $skill->id }}, '{{ addslashes($emp->full_name) }}', '{{ addslashes($skill->name) }}', '{{ $empSkill?->level ?? '' }}', {{ $empSkill?->rating ?? 1 }})"
                                 style="cursor:pointer;background:{{ $lc['bg'] }};border:1px solid {{ $lc['border'] }};
                                        border-radius:6px;padding:7px 4px;min-height:36px;
                                        display:flex;align-items:center;justify-content:center;transition:border-color .15s;"
                                 onmouseover="this.style.borderColor='var(--accent)'"
                                 onmouseout="this.style.borderColor='{{ $lc['border'] }}'">
                                <div>
                                    <div style="font-size:12px;font-weight:700;color:{{ $lc['color'] }};">
                                        {{ $lc['label'] }}
                                    </div>
                                    @if($empSkill?->rating)
                                    <div style="font-size:9px;color:{{ $lc['color'] }};">
                                        {{ str_repeat('★', $empSkill->rating) }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Right: Add Skill --}}
    <div class="card" style="position:sticky;top:0;">
        <div class="form-section">
            <i class="fa-solid fa-plus-circle"></i> Add Skill
        </div>
        <form method="POST" action="{{ route('training.skills.store') }}">
            @csrf
            <div style="display:flex;flex-direction:column;gap:10px;">
                <div>
                    <label class="form-label">Skill Name <span style="color:var(--red);">*</span></label>
                    <input type="text" name="name" required
                           placeholder="e.g. Forklift Operation" class="form-input">
                </div>
                <div>
                    <label class="form-label">Category</label>
                    <input type="text" name="category"
                           placeholder="e.g. Equipment / Safety" class="form-input">
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-textarea"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Add Skill
                </button>
            </div>
        </form>

        @if($skills->count())
        <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--border);">
            <div class="section-label" style="margin-bottom:10px;">All Skills</div>
            <div style="display:flex;flex-direction:column;gap:5px;max-height:300px;overflow-y:auto;">
                @foreach($skills->groupBy('category') as $category => $catSkills)
                <div style="font-size:10px;color:var(--text-muted);font-weight:700;letter-spacing:.5px;
                            text-transform:uppercase;margin-top:8px;margin-bottom:3px;">
                    {{ $category ?: 'General' }}
                </div>
                @foreach($catSkills as $skill)
                <div style="font-size:12px;color:var(--text-secondary);padding:5px 8px;
                            background:var(--bg-muted);border-radius:5px;
                            display:flex;justify-content:space-between;align-items:center;">
                    {{ $skill->name }}
                    <span style="font-size:10px;color:var(--text-muted);">
                        {{ $skill->employeeSkills->count() ?? 0 }} assessed
                    </span>
                </div>
                @endforeach
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Skill Edit Modal --}}
<div id="skillModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-title">
            <i class="fa-solid fa-table-cells"></i> Update Skill Assessment
        </div>
        <div id="skillModalSub" style="font-size:12px;color:var(--text-muted);margin-bottom:18px;"></div>

        <div style="display:flex;flex-direction:column;gap:14px;">
            <div>
                <label class="form-label">Proficiency Level</label>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;" id="levelSelector">
                    @foreach([
                        ['beginner',     'Beginner',     'yellow'],
                        ['intermediate', 'Intermediate', 'blue'],
                        ['advanced',     'Advanced',     'green'],
                        ['expert',       'Expert',       'accent'],
                    ] as [$v, $l, $c])
                    <div class="level-opt" data-level="{{ $v }}" onclick="selectLevel('{{ $v }}')"
                         style="text-align:center;padding:10px 6px;border-radius:8px;cursor:pointer;
                                background:var(--{{ $c }}-bg);border:2px solid var(--{{ $c }}-border);
                                transition:all .15s;">
                        <div style="font-size:11px;font-weight:700;color:var(--{{ $c }});">{{ $l }}</div>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" id="selectedLevel" value="beginner">
            </div>

            <div>
                <label class="form-label">Rating (1–5)</label>
                <div style="display:flex;gap:8px;justify-content:center;">
                    @foreach([1,2,3,4,5] as $r)
                    <button type="button" class="rating-star" data-rating="{{ $r }}"
                            onclick="selectRating({{ $r }})"
                            style="width:36px;height:36px;background:var(--accent-bg);
                                   border:1px solid var(--accent-border);border-radius:8px;
                                   font-size:18px;cursor:pointer;transition:all .15s;">
                        ★
                    </button>
                    @endforeach
                </div>
                <input type="hidden" id="selectedRating" value="1">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeSkillModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveSkill()">
                <i class="fa-solid fa-floppy-disk"></i> Save
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
var currentEmpId   = null;
var currentSkillId = null;

function openSkillModal(empId, skillId, empName, skillName, level, rating) {
    currentEmpId   = empId;
    currentSkillId = skillId;
    document.getElementById('skillModalSub').textContent = empName + ' — ' + skillName;
    document.getElementById('selectedLevel').value  = level  || 'beginner';
    document.getElementById('selectedRating').value = rating || 1;
    selectLevel(level  || 'beginner');
    selectRating(rating || 1);
    document.getElementById('skillModal').classList.add('open');
}

function closeSkillModal() {
    document.getElementById('skillModal').classList.remove('open');
}

function selectLevel(level) {
    document.getElementById('selectedLevel').value = level;
    document.querySelectorAll('.level-opt').forEach(function(opt) {
        opt.style.transform = opt.dataset.level === level ? 'scale(1.05)' : 'scale(1)';
        opt.style.boxShadow = opt.dataset.level === level ? '0 0 0 2px var(--accent)' : 'none';
    });
}

function selectRating(r) {
    document.getElementById('selectedRating').value = r;
    document.querySelectorAll('.rating-star').forEach(function(btn) {
        var isActive = parseInt(btn.dataset.rating) <= r;
        btn.style.background  = isActive ? 'var(--accent-bg)'     : 'var(--bg-muted)';
        btn.style.borderColor = isActive ? 'var(--accent-border)' : 'var(--border)';
        btn.style.color       = isActive ? 'var(--accent)'        : 'var(--text-muted)';
    });
}

function saveSkill() {
    var data = {
        employee_id: currentEmpId,
        skill_id:    currentSkillId,
        level:       document.getElementById('selectedLevel').value,
        rating:      document.getElementById('selectedRating').value,
        _token:      document.querySelector('meta[name="csrf-token"]').content
    };
    fetch('{{ route("training.skills.update") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) { closeSkillModal(); window.location.reload(); }
    })
    .catch(console.error);
}

document.getElementById('skillModal').addEventListener('click', function(e) {
    if (e.target === this) closeSkillModal();
});
</script>
@endpush

@endsection