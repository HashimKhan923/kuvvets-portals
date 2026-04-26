@extends('layouts.app')
@section('title','My Profile')
@section('page-title','My Profile')
@section('breadcrumb','Settings · My Profile')

@section('content')
<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">

    {{-- Profile Card --}}
    <div class="card card-gold" style="padding:24px;text-align:center;">
        <div style="position:relative;display:inline-block;margin-bottom:14px;">
            <img id="avatarPreview"
                 src="{{ $user->avatar_url }}"
                 style="width:90px;height:90px;border-radius:50%;object-fit:cover;
                        border:3px solid var(--gold-mid);">
        </div>
        <div style="font-family:'Space Grotesk',sans-serif;font-size:16px;font-weight:700;
                    color:var(--text-primary);margin-bottom:3px;">
            {{ $user->name }}
        </div>
        <div style="font-size:12px;color:var(--gold-dark);margin-bottom:3px;">
            {{ $user->display_role }}
        </div>
        <div style="font-size:11px;color:var(--text-muted);">
            {{ $user->email }}
        </div>
        <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border-light);">
            @foreach([
                ['User Type',   ucfirst(str_replace('_',' ',$user->user_type))],
                ['Username',    '@'.$user->username],
                ['Last Login',  $user->last_login_at?->format('d M Y h:i A') ?? 'N/A'],
                ['Login Count', number_format($user->login_count).' times'],
            ] as [$l,$v])
            <div style="display:flex;justify-content:space-between;padding:5px 0;
                        font-size:11px;border-bottom:1px solid var(--border-light);">
                <span style="color:var(--text-muted);">{{ $l }}</span>
                <span style="color:var(--text-primary);font-weight:500;">{{ $v }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Forms --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Profile Details --}}
        <div class="card card-gold" style="padding:24px;">
            <div class="section-title" style="margin-bottom:18px;">
                <i class="fa-solid fa-user-pen"></i> Update Profile
            </div>
            <form method="POST" action="{{ route('settings.profile.update') }}"
                  enctype="multipart/form-data">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div style="grid-column:span 2;display:flex;align-items:center;gap:16px;
                                padding-bottom:16px;border-bottom:1px solid var(--border-light);">
                        <img id="avatarPreview2" src="{{ $user->avatar_url }}"
                             style="width:56px;height:56px;border-radius:50%;object-fit:cover;
                                    border:2px solid var(--gold-mid);">
                        <div>
                            <div style="font-size:12px;color:var(--text-muted);margin-bottom:7px;">
                                Profile Photo
                            </div>
                            <label style="background:var(--white);border:1px solid var(--border);
                                          border-radius:7px;padding:6px 12px;font-size:12px;
                                          color:var(--gold-dark);cursor:pointer;display:inline-block;">
                                <i class="fa-solid fa-upload"></i> Change Photo
                                <input type="file" name="avatar" accept="image/*"
                                       style="display:none;"
                                       onchange="previewAvatar(this)">
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name" required
                               value="{{ old('name', $user->name) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Username</label>
                        <input type="text" name="username"
                               value="{{ old('username', $user->username) }}"
                               class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Email <span style="color:var(--danger);">*</span></label>
                        <input type="email" name="email" required
                               value="{{ old('email', $user->email) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone"
                               value="{{ old('phone', $user->phone ?? '') }}"
                               placeholder="+92-XXX-XXXXXXX" class="form-input">
                    </div>
                </div>
                <div style="margin-top:16px;text-align:right;">
                    <button type="submit" class="btn-gold">
                        <i class="fa-solid fa-floppy-disk"></i> Save Profile
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="card card-gold" style="padding:24px;">
            <div class="section-title" style="margin-bottom:18px;">
                <i class="fa-solid fa-key"></i> Change Password
            </div>
            <form method="POST" action="{{ route('settings.profile.password') }}">
                @csrf
                <div style="display:flex;flex-direction:column;gap:12px;max-width:440px;">
                    <div>
                        <label class="form-label">
                            Current Password <span style="color:var(--danger);">*</span>
                        </label>
                        <input type="password" name="current_password" required
                               class="form-input" placeholder="Enter current password">
                    </div>
                    <div>
                        <label class="form-label">
                            New Password <span style="color:var(--danger);">*</span>
                        </label>
                        <input type="password" name="password" required
                               id="newPassword"
                               class="form-input" placeholder="Min 8 characters"
                               oninput="checkPasswordStrength(this.value)">
                        <div id="strengthBar"
                             style="height:4px;background:var(--border-light);border-radius:2px;
                                    margin-top:6px;overflow:hidden;">
                            <div id="strengthFill"
                                 style="height:100%;width:0%;border-radius:2px;
                                        transition:width .3s,background .3s;"></div>
                        </div>
                        <div id="strengthText"
                             style="font-size:10px;color:var(--text-muted);margin-top:3px;"></div>
                    </div>
                    <div>
                        <label class="form-label">
                            Confirm New Password <span style="color:var(--danger);">*</span>
                        </label>
                        <input type="password" name="password_confirmation" required
                               class="form-input" placeholder="Repeat new password">
                    </div>

                    <div style="background:var(--info-bg);border:1px solid var(--info-border);
                                border-radius:8px;padding:12px 14px;font-size:12px;color:var(--info);">
                        <i class="fa-solid fa-circle-info" style="margin-right:6px;"></i>
                        Password must be at least 8 characters and different from current password.
                    </div>

                    <button type="submit" class="btn-gold" style="align-self:flex-start;">
                        <i class="fa-solid fa-key"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatarPreview').src  = e.target.result;
            document.getElementById('avatarPreview2').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function checkPasswordStrength(val) {
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    let score = 0;
    if (val.length >= 8)                      score++;
    if (/[A-Z]/.test(val))                    score++;
    if (/[0-9]/.test(val))                    score++;
    if (/[^A-Za-z0-9]/.test(val))             score++;

    const levels = [
        { pct:'25%', color:'#C53030', label:'Weak' },
        { pct:'50%', color:'#B7791F', label:'Fair' },
        { pct:'75%', color:'#2B6CB0', label:'Good' },
        { pct:'100%',color:'#2D7A4F', label:'Strong' },
    ];
    const l = levels[score - 1] || { pct:'0%', color:'var(--border)', label:'' };
    fill.style.width      = l.pct;
    fill.style.background = l.color;
    text.textContent      = l.label;
    text.style.color      = l.color;
}
</script>
@endpush
@endsection