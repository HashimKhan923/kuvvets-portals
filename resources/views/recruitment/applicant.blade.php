@extends('layouts.app')
@section('title', $applicant->full_name)
@section('page-title', $applicant->full_name)
@section('breadcrumb', 'Recruitment · Applicants · ' . $applicant->full_name)

@section('content')

<div class="grid-sidebar-main">

    {{-- LEFT: Profile --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Profile Card --}}
        <div class="card" style="text-align:center;">
            <img src="{{ $applicant->avatar_url }}"
                 class="avatar avatar-xl"
                 style="display:block;margin:0 auto 12px;">
            <div style="font-size:15px;font-weight:700;color:var(--text-primary);">
                {{ $applicant->full_name }}
            </div>
            <div style="font-size:12px;color:var(--accent);margin-top:2px;">
                {{ $applicant->current_designation ?? 'Applicant' }}
            </div>
            <div style="font-size:11px;color:var(--text-muted);margin:3px 0 12px;">
                {{ $applicant->current_employer ?? '—' }}
            </div>
            @php $badge = $applicant->stage_badge; @endphp
            <span class="badge" style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border:1px solid {{ $badge['border'] }};">
                {{ $badge['label'] }}
            </span>

            {{-- Star Rating --}}
            <div style="margin-top:16px;">
                <div class="section-label" style="margin-bottom:8px;">HR Rating</div>
                <div style="display:flex;justify-content:center;gap:4px;">
                    @for($i = 1; $i <= 5; $i++)
                    <form method="POST" action="{{ route('recruitment.applicants.rate', $applicant) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="rating" value="{{ $i }}">
                        <button type="submit"
                                style="background:none;border:none;cursor:pointer;font-size:20px;
                                       color:{{ $applicant->rating && $i <= $applicant->rating ? 'var(--accent)' : 'var(--border-strong)' }};">
                            ★
                        </button>
                    </form>
                    @endfor
                </div>
            </div>
        </div>

        {{-- Quick Info --}}
        <div class="card">
            <div class="section-label">Details</div>
            @foreach([
                ['fa-envelope',    $applicant->email],
                ['fa-phone',       $applicant->phone ?? '—'],
                ['fa-id-card',     $applicant->cnic ?? '—'],
                ['fa-location-dot',$applicant->city ?? '—'],
                ['fa-briefcase',   $applicant->total_experience_years . ' years experience'],
                ['fa-money-bill',  'Expected: PKR ' . number_format($applicant->expected_salary ?? 0)],
                ['fa-clock',       ($applicant->notice_period_days ?? 0) . ' days notice'],
                ['fa-share-nodes', $applicant->source ?? '—'],
            ] as [$icon, $value])
            <div class="info-row">
                <i class="fa-solid {{ $icon }}"></i>
                <div class="info-row-value">{{ $value }}</div>
            </div>
            @endforeach
        </div>

        {{-- Applied For --}}
        <div class="card">
            <div class="section-label">Applied For</div>
            <a href="{{ route('recruitment.jobs.show', $applicant->jobPosting) }}"
               style="text-decoration:none;">
                <div style="font-size:13px;color:var(--accent);font-weight:600;">
                    {{ $applicant->jobPosting->title }}
                </div>
                <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                    {{ $applicant->jobPosting->reference_no }}
                </div>
            </a>
        </div>

        @if($applicant->cv_url)
        <a href="{{ $applicant->cv_url }}" target="_blank" class="btn btn-secondary"
           style="justify-content:center;">
            <i class="fa-solid fa-file-pdf"></i> Download CV
        </a>
        @endif

    </div>

    {{-- RIGHT: Actions + History --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Move Stage --}}
        <div class="card">
            <div class="form-section">
                <i class="fa-solid fa-arrows-left-right"></i> Move Pipeline Stage
            </div>
            <form method="POST" action="{{ route('recruitment.applicants.stage', $applicant) }}">
                @csrf @method('PATCH')
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <select name="stage" class="form-select" style="flex:1;min-width:180px;">
                        @foreach([
                            'applied'             => 'Applied',
                            'screening'           => 'Screening',
                            'shortlisted'         => 'Shortlisted',
                            'interview_scheduled' => 'Interview Scheduled',
                            'interviewed'         => 'Interviewed',
                            'assessment'          => 'Assessment',
                            'offer_sent'          => 'Offer Sent',
                            'offer_accepted'      => 'Offer Accepted',
                            'offer_declined'      => 'Offer Declined',
                            'hired'               => 'Hired',
                            'rejected'            => 'Rejected',
                            'withdrawn'           => 'Withdrawn',
                        ] as $v => $l)
                        <option value="{{ $v }}" {{ $applicant->stage === $v ? 'selected' : '' }}>
                            {{ $l }}
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Move Stage</button>
                    @if($applicant->stage !== 'hired')
                    <form method="POST" action="{{ route('recruitment.applicants.hire', $applicant) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-success"
                                onclick="return confirm('Mark {{ $applicant->full_name }} as Hired?')">
                            <i class="fa-solid fa-user-check"></i> Mark Hired
                        </button>
                    </form>
                    @endif
                </div>
            </form>
        </div>

        {{-- Schedule Interview --}}
        <div class="card">
            <div class="form-section">
                <i class="fa-solid fa-calendar-plus"></i> Schedule Interview
            </div>
            <form method="POST" action="{{ route('recruitment.interviews.store', $applicant) }}">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="form-label">Round</label>
                        <input type="number" name="round" value="1" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            @foreach(['phone'=>'Phone','video'=>'Video','in_person'=>'In Person','technical'=>'Technical','hr'=>'HR','panel'=>'Panel'] as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Duration (min)</label>
                        <input type="number" name="duration_minutes" value="60" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Date & Time <span style="color:var(--red);">*</span></label>
                        <input type="datetime-local" name="scheduled_at" required class="form-input">
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Location / Zoom Link</label>
                        <input type="text" name="location"
                               placeholder="e.g. HR Office / https://zoom.us/…" class="form-input">
                    </div>
                </div>
                <button type="submit" class="btn btn-purple">
                    <i class="fa-solid fa-calendar-check"></i> Schedule
                </button>
            </form>
        </div>

        {{-- Interview History --}}
        @if($applicant->interviews->isNotEmpty())
        <div class="card">
            <div class="form-section">
                <i class="fa-solid fa-list-check"></i> Interview History
            </div>
            @foreach($applicant->interviews->sortByDesc('scheduled_at') as $iv)
            @php $ibadge = $iv->status_badge; @endphp
            <div style="background:var(--bg-muted);border:1px solid var(--border);border-radius:8px;
                        padding:14px;margin-bottom:8px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <div>
                        <div style="font-size:12px;font-weight:600;color:var(--text-primary);">
                            Round {{ $iv->round }} — {{ ucfirst(str_replace('_', ' ', $iv->type)) }}
                        </div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                            {{ $iv->scheduled_at->setTimezone('Asia/Karachi')->format('d M Y · h:i A') }} ·
                            {{ $iv->duration_minutes }}min
                            @if($iv->location) · {{ $iv->location }} @endif
                        </div>
                    </div>
                    <span class="badge" style="background:{{ $ibadge['bg'] }};color:{{ $ibadge['color'] }};border:1px solid {{ $ibadge['border'] }};">
                        {{ ucfirst($iv->status) }}
                    </span>
                </div>

                @if($iv->status === 'scheduled')
                <form method="POST" action="{{ route('recruitment.interviews.result', $iv) }}"
                      style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;
                             padding-top:8px;border-top:1px solid var(--border);">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select" style="width:auto;">
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="no_show">No Show</option>
                    </select>
                    <input type="number" name="score" min="1" max="10"
                           placeholder="Score /10" class="form-input" style="width:90px;">
                    <select name="recommendation" class="form-select" style="width:auto;">
                        <option value="">Recommendation</option>
                        <option value="strong_hire">Strong Hire</option>
                        <option value="hire">Hire</option>
                        <option value="maybe">Maybe</option>
                        <option value="no_hire">No Hire</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Save Result</button>
                </form>
                @elseif($iv->score)
                <div style="display:flex;gap:12px;font-size:11px;color:var(--text-muted);margin-top:6px;">
                    <span>Score:
                        <strong style="color:var(--accent);">{{ $iv->score }}/10</strong>
                    </span>
                    @if($iv->recommendation)
                    <span>Rec:
                        <strong style="color:var(--green);">
                            {{ ucfirst(str_replace('_', ' ', $iv->recommendation)) }}
                        </strong>
                    </span>
                    @endif
                </div>
                @if($iv->feedback)
                <div style="font-size:12px;color:var(--text-secondary);margin-top:6px;font-style:italic;">
                    {{ $iv->feedback }}
                </div>
                @endif
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Send Offer --}}
        @if(in_array($applicant->stage, ['interviewed', 'assessment', 'shortlisted']))
        <div class="card">
            <div class="form-section">
                <i class="fa-solid fa-file-contract"></i> Send Offer Letter
            </div>
            <form method="POST" action="{{ route('recruitment.offer.send', $applicant) }}">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div>
                        <label class="form-label">Offered Salary (PKR) <span style="color:var(--red);">*</span></label>
                        <input type="number" name="offered_salary" required
                               placeholder="60000" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Joining Date <span style="color:var(--red);">*</span></label>
                        <input type="date" name="joining_date" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Offer Expiry <span style="color:var(--red);">*</span></label>
                        <input type="date" name="offer_expiry" required class="form-input">
                    </div>
                </div>
                <div style="margin-bottom:12px;">
                    <label class="form-label">Special Terms / Notes</label>
                    <textarea name="terms" rows="2" class="form-textarea"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i> Send Offer
                </button>
            </form>
        </div>
        @endif

        {{-- Offer Letters --}}
        @foreach($applicant->offerLetters as $offer)
        @php
        $offerBadge = match($offer->status) {
            'accepted' => 'badge-green',
            'declined' => 'badge-red',
            'sent'     => 'badge-accent',
            default    => 'badge-muted',
        };
        @endphp
        <div class="card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <div class="card-title" style="margin-bottom:0;">
                    <i class="fa-solid fa-file-contract"></i> Offer {{ $offer->offer_number }}
                </div>
                <span class="badge {{ $offerBadge }}">{{ ucfirst($offer->status) }}</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:12px;">
                <div class="detail-block">
                    <div class="detail-block-label">Salary</div>
                    <div class="detail-block-value">PKR {{ number_format($offer->offered_salary) }}</div>
                </div>
                <div class="detail-block">
                    <div class="detail-block-label">Joining</div>
                    <div class="detail-block-value">{{ $offer->joining_date->format('d M Y') }}</div>
                </div>
                <div class="detail-block">
                    <div class="detail-block-label">Expires</div>
                    <div class="detail-block-value">{{ $offer->offer_expiry->format('d M Y') }}</div>
                </div>
            </div>
            @if($offer->status === 'sent')
            <form method="POST" action="{{ route('recruitment.offer.respond', $offer) }}"
                  style="display:flex;gap:8px;">
                @csrf @method('PATCH')
                <input type="hidden" name="response" value="accepted">
                <button type="submit" class="btn btn-success" style="flex:1;justify-content:center;">
                    <i class="fa-solid fa-check"></i> Accept
                </button>
                <button type="submit" class="btn btn-danger" style="flex:1;justify-content:center;"
                        onclick="this.form.querySelector('[name=response]').value='declined'">
                    <i class="fa-solid fa-xmark"></i> Decline
                </button>
            </form>
            @endif
        </div>
        @endforeach

    </div>

</div>

@endsection