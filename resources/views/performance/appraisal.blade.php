@extends('layouts.app')
@section('title', 'Appraisal — ' . $appraisal->employee->full_name)
@section('page-title', 'Performance Appraisal')
@section('breadcrumb', 'Performance · ' . $appraisal->cycle->name . ' · ' . $appraisal->employee->full_name)

@section('content')

<div class="grid-sidebar-main">

    {{-- LEFT: Employee Info --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Profile Card --}}
        <div class="card" style="text-align:center;">
            <img src="{{ $appraisal->employee->avatar_url }}"
                 class="avatar avatar-xl"
                 style="display:block;margin:0 auto 12px;">
            <div style="font-size:15px;font-weight:700;color:var(--text-primary);">
                {{ $appraisal->employee->full_name }}
            </div>
            <div style="font-size:12px;color:var(--accent);margin-top:2px;">
                {{ $appraisal->employee->employee_id }}
            </div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                {{ $appraisal->employee->designation?->title ?? '—' }}
            </div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                {{ $appraisal->employee->department?->name ?? '—' }}
            </div>

            @php $sBadge = $appraisal->status_badge; @endphp
            <div style="margin-top:12px;">
                <span class="badge" style="background:{{ $sBadge['bg'] }};color:{{ $sBadge['color'] }};border:1px solid {{ $sBadge['border'] }};">
                    {{ ucfirst(str_replace('_', ' ', $appraisal->status)) }}
                </span>
            </div>

            {{-- Overall Score --}}
            @if($appraisal->overall_score)
            <div style="margin-top:16px;padding:14px;background:var(--accent-bg);
                        border:1px solid var(--accent-border);border-radius:10px;">
                <div class="section-label" style="margin-bottom:5px;">Overall Score</div>
                <div style="font-size:32px;font-weight:700;color:var(--accent);">
                    {{ number_format($appraisal->overall_score, 1) }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);">out of 5.0</div>
                @php $rBadge = $appraisal->rating_badge; @endphp
                <div style="margin-top:8px;">
                    <span class="badge" style="background:{{ $rBadge['bg'] }};color:{{ $rBadge['color'] }};border:1px solid {{ $rBadge['border'] }};font-size:11px;">
                        {{ $rBadge['label'] }}
                    </span>
                </div>
                @if($appraisal->increment_recommended)
                <div style="font-size:11px;color:var(--green);margin-top:6px;font-weight:600;">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                    {{ $appraisal->increment_recommended }}% increment recommended
                </div>
                @endif
                @if($appraisal->promotion_recommended)
                <div style="font-size:11px;color:var(--accent);margin-top:3px;font-weight:600;">
                    <i class="fa-solid fa-star"></i> Promotion recommended
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Appraisal Info --}}
        <div class="card">
            <div class="section-label">Appraisal Info</div>
            @foreach([
                ['Number',    $appraisal->appraisal_number],
                ['Cycle',     $appraisal->cycle->name],
                ['Period',    $appraisal->cycle->duration],
                ['Appraiser', $appraisal->appraiser->name],
                ['Submitted', $appraisal->submitted_at?->format('d M Y') ?? 'Pending'],
            ] as [$l, $v])
            <div style="display:flex;justify-content:space-between;padding:6px 0;
                        border-bottom:1px solid var(--border);font-size:12px;">
                <span style="color:var(--text-muted);">{{ $l }}</span>
                <span style="color:var(--text-primary);font-weight:500;text-align:right;max-width:160px;">
                    {{ $v }}
                </span>
            </div>
            @endforeach
        </div>

        {{-- Goals Summary --}}
        @if($goals->count())
        <div class="card">
            <div class="section-label">Goals ({{ $goals->count() }})</div>
            @foreach($goals as $goal)
            @php $gBadge = $goal->status_badge; @endphp
            <div style="margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--border);">
                <div style="font-size:12px;font-weight:600;color:var(--text-primary);margin-bottom:4px;">
                    {{ Str::limit($goal->title, 40) }}
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div class="progress-track" style="flex:1;">
                        <div class="progress-fill" style="width:{{ $goal->progress }}%;"></div>
                    </div>
                    <span style="font-size:10px;color:var(--text-muted);">{{ $goal->progress }}%</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>

    {{-- RIGHT: Scoring Form --}}
    <div>

        @if($appraisal->status !== 'completed')

        <div class="card" style="margin-bottom:16px;">
            <div class="card-title">
                <i class="fa-solid fa-star"></i> Performance Scoring
            </div>
            <form method="POST" action="{{ route('performance.appraisal.submit', $appraisal) }}"
                  id="appraisalForm">
                @csrf

                @php
                $criteria = [
                    ['job_knowledge_score',    'Job Knowledge & Skills',        'Technical competency, expertise, and understanding of role requirements'],
                    ['work_quality_score',     'Work Quality',                  'Accuracy, thoroughness, and standard of work delivered'],
                    ['productivity_score',     'Productivity & Output',         'Volume of work completed within given timeframes'],
                    ['communication_score',    'Communication Skills',          'Written, verbal, and interpersonal communication effectiveness'],
                    ['teamwork_score',         'Teamwork & Collaboration',      'Contribution to team goals and working relationships'],
                    ['initiative_score',       'Initiative & Problem Solving',  'Proactive approach to challenges and independent decision-making'],
                    ['attendance_score',       'Attendance & Punctuality',      'Reliability, presence, and time management'],
                    ['leadership_score',       'Leadership Qualities',          'Guidance, mentoring, and influence on team members'],
                    ['goal_achievement_score', 'Goal Achievement',              'Delivery against assigned KPIs and objectives'],
                ];
                @endphp

                <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:24px;">
                    @foreach($criteria as $ci => [$field, $label, $desc])
                    <div style="background:var(--bg-muted);border-radius:10px;padding:16px;">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;
                                    margin-bottom:12px;gap:12px;">
                            <div style="flex:1;">
                                <div style="font-size:13px;font-weight:700;color:var(--text-primary);">
                                    {{ $label }}
                                </div>
                                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                                    {{ $desc }}
                                </div>
                            </div>
                            <div id="score-display-{{ $ci }}"
                                 style="font-size:22px;font-weight:700;color:var(--accent);
                                        min-width:40px;text-align:center;">
                                {{ old($field, $appraisal->{$field} ?? '—') }}
                            </div>
                        </div>

                        <div style="display:flex;gap:6px;">
                            @foreach([1=>['Poor','red'],2=>['Below Avg','yellow'],3=>['Average','blue'],4=>['Good','green'],5=>['Excellent','green']] as $score => [$slabel, $scolor])
                            <label style="cursor:pointer;flex:1;">
                                <input type="radio" name="{{ $field }}" value="{{ $score }}"
                                       {{ old($field, $appraisal->{$field}) == $score ? 'checked' : '' }}
                                       required
                                       style="display:none;"
                                       onchange="updateScore({{ $ci }}, {{ $score }}, '{{ $scolor }}')">
                                <div class="score-btn-{{ $ci }}"
                                     data-score="{{ $score }}"
                                     style="text-align:center;padding:8px 4px;border-radius:8px;
                                            border:2px solid {{ old($field, $appraisal->{$field}) == $score ? 'var(--' . $scolor . ')' : 'var(--border)' }};
                                            background:{{ old($field, $appraisal->{$field}) == $score ? 'var(--' . $scolor . '-bg)' : 'var(--bg-card)' }};
                                            transition:all .15s;cursor:pointer;">
                                    <div style="font-size:15px;">
                                        {{ $score >= 4 ? '⭐' : ($score >= 3 ? '✅' : ($score >= 2 ? '🔶' : '❌')) }}
                                    </div>
                                    <div style="font-size:10px;font-weight:700;margin-top:3px;
                                                color:{{ old($field, $appraisal->{$field}) == $score ? 'var(--' . $scolor . ')' : 'var(--text-muted)' }};">
                                        {{ $score }}
                                    </div>
                                    <div style="font-size:9px;color:var(--text-muted);margin-top:1px;">
                                        {{ $slabel }}
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Live Score Display --}}
                <div style="background:var(--bg-muted);border:1px solid var(--border);
                            border-radius:10px;padding:18px;margin-bottom:20px;
                            display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div class="section-label" style="margin-bottom:4px;">Calculated Overall Score</div>
                        <div id="liveRating" style="font-size:12px;color:var(--text-muted);margin-top:4px;"></div>
                    </div>
                    <div style="text-align:right;">
                        <div id="liveScore"
                             style="font-size:36px;font-weight:700;color:var(--accent);">—</div>
                        <div style="font-size:11px;color:var(--text-muted);">out of 5.0</div>
                    </div>
                </div>

                {{-- Text Feedback --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
                    @foreach([
                        ['strengths',        'Key Strengths',         'What does this employee do exceptionally well?'],
                        ['improvements',     'Areas for Improvement', 'What specific areas need development?'],
                        ['achievements',     'Notable Achievements',  'Key accomplishments during this period'],
                        ['training_needs',   'Training Needs',        'Recommended training or development programs'],
                    ] as [$name, $label, $placeholder])
                    <div>
                        <label class="form-label">{{ $label }}</label>
                        <textarea name="{{ $name }}" rows="3"
                                  placeholder="{{ $placeholder }}"
                                  class="form-textarea">{{ old($name, $appraisal->{$name}) }}</textarea>
                    </div>
                    @endforeach
                    <div style="grid-column:span 2;">
                        <label class="form-label">Manager Comments</label>
                        <textarea name="manager_comments" rows="3"
                                  placeholder="Overall assessment and forward-looking comments"
                                  class="form-textarea">{{ old('manager_comments', $appraisal->manager_comments) }}</textarea>
                    </div>
                </div>

                {{-- Recommendations --}}
                <div style="background:var(--accent-bg);border:1px solid var(--accent-border);
                            border-radius:10px;padding:16px;margin-bottom:20px;">
                    <div class="form-section" style="margin-bottom:12px;">
                        <i class="fa-solid fa-star"></i> Recommendations
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:center;">
                        <div>
                            <label class="form-label">Increment Recommended (%)</label>
                            <input type="number" name="increment_recommended"
                                   value="{{ old('increment_recommended', $appraisal->increment_recommended ?? 0) }}"
                                   min="0" max="100" step="0.5"
                                   class="form-input" placeholder="e.g. 10">
                        </div>
                        <div style="padding-top:20px;">
                            <label style="display:flex;align-items:center;gap:8px;
                                           font-size:13px;color:var(--text-secondary);cursor:pointer;">
                                <input type="checkbox" name="promotion_recommended" value="1"
                                       {{ old('promotion_recommended', $appraisal->promotion_recommended) ? 'checked' : '' }}
                                       style="accent-color:var(--accent);width:16px;height:16px;">
                                Recommend for Promotion
                            </label>
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <a href="{{ route('performance.cycle', $appraisal->cycle) }}" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Submit this appraisal? This action cannot be undone.')">
                        <i class="fa-solid fa-paper-plane"></i> Submit Appraisal
                    </button>
                </div>

            </form>
        </div>

        @else
        {{-- Completed Read-Only View --}}
        <div class="card">
            <div class="card-title">
                <i class="fa-solid fa-circle-check" style="color:var(--green);"></i>
                Appraisal Completed — {{ $appraisal->completed_at?->format('d M Y') }}
            </div>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px;">
                @foreach([
                    ['Job Knowledge',    $appraisal->job_knowledge_score],
                    ['Work Quality',     $appraisal->work_quality_score],
                    ['Productivity',     $appraisal->productivity_score],
                    ['Communication',    $appraisal->communication_score],
                    ['Teamwork',         $appraisal->teamwork_score],
                    ['Initiative',       $appraisal->initiative_score],
                    ['Attendance',       $appraisal->attendance_score],
                    ['Leadership',       $appraisal->leadership_score],
                    ['Goal Achievement', $appraisal->goal_achievement_score],
                ] as [$label, $score])
                <div class="detail-block">
                    <div class="detail-block-label">{{ $label }}</div>
                    <div style="display:flex;align-items:center;gap:8px;margin-top:4px;">
                        <div style="font-size:20px;font-weight:700;color:var(--accent);">
                            {{ number_format($score ?? 0, 1) }}
                        </div>
                        <div class="progress-track" style="flex:1;">
                            <div class="progress-fill"
                                 style="width:{{ (($score ?? 0) / 5) * 100 }}%;"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @foreach([
                ['strengths',        'Key Strengths'],
                ['improvements',     'Areas for Improvement'],
                ['achievements',     'Notable Achievements'],
                ['training_needs',   'Training Needs'],
                ['manager_comments', 'Manager Comments'],
            ] as [$field, $label])
            @if($appraisal->{$field})
            <div class="note-block" style="margin-bottom:12px;">
                <div class="note-block-label">{{ $label }}</div>
                <div class="note-block-text" style="margin-top:6px;">
                    {{ $appraisal->{$field} }}
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @endif

    </div>

</div>

@push('scripts')
<script>
var scoreColors = { 1: 'red', 2: 'yellow', 3: 'blue', 4: 'green', 5: 'green' };
var ratingLabels = {
    outstanding: 'Outstanding',
    exceeds_expectations: 'Exceeds Expectations',
    meets_expectations: 'Meets Expectations',
    needs_improvement: 'Needs Improvement',
    unsatisfactory: 'Unsatisfactory'
};

function updateScore(index, score, colorKey) {
    var display = document.getElementById('score-display-' + index);
    var colorVal = 'var(--' + colorKey + ')';
    if (display) {
        display.textContent = score;
        display.style.color = colorVal;
    }
    document.querySelectorAll('.score-btn-' + index).forEach(function(btn) {
        var btnScore = parseInt(btn.dataset.score);
        var isSelected = btnScore === score;
        btn.style.background  = isSelected ? 'var(--' + colorKey + '-bg)' : 'var(--bg-card)';
        btn.style.borderColor = isSelected ? colorVal : 'var(--border)';
        var numEl = btn.querySelector('div:nth-child(2)');
        if (numEl) numEl.style.color = isSelected ? colorVal : 'var(--text-muted)';
    });
    calculateLiveScore();
}

function calculateLiveScore() {
    var fields = [
        'job_knowledge_score','work_quality_score','productivity_score',
        'communication_score','teamwork_score','initiative_score',
        'attendance_score','leadership_score','goal_achievement_score'
    ];
    var scores = [];
    fields.forEach(function(f) {
        var checked = document.querySelector('input[name="' + f + '"]:checked');
        if (checked) scores.push(parseFloat(checked.value));
    });

    if (scores.length === 0) return;

    var sum = 0;
    scores.forEach(function(s) { sum += s; });
    var avg = sum / scores.length;

    var display = document.getElementById('liveScore');
    var ratingEl = document.getElementById('liveRating');
    if (display) display.textContent = avg.toFixed(2);

    var rating = '';
    if (avg >= 4.5)      rating = 'outstanding';
    else if (avg >= 3.5) rating = 'exceeds_expectations';
    else if (avg >= 2.5) rating = 'meets_expectations';
    else if (avg >= 1.5) rating = 'needs_improvement';
    else                 rating = 'unsatisfactory';

    if (ratingEl) ratingEl.textContent = ratingLabels[rating] || '';
}

calculateLiveScore();
</script>
@endpush

@endsection