<form method="POST" action="{{ route('employee.profile.password') }}" style="max-width: 560px;" x-data="pwForm()">
    @csrf
    @method('PUT')

    <div class="form-card">
        <div class="form-section-title"><i class="fa-solid fa-key"></i>Change Password</div>

        <div style="font-size:12px;color:var(--text-muted);margin-bottom:18px;padding:12px 14px;background:var(--blue-bg);border:1px solid var(--blue-border);border-radius:10px;">
            <i class="fa-solid fa-shield-halved" style="color:var(--blue);margin-right:6px;"></i>
            After changing your password, you'll stay logged in on this device.
        </div>

        <div class="form-row full">
            <div class="field">
                <label>Current Password <span style="color:var(--red);">*</span></label>
                <input type="password" name="current_password" required autocomplete="current-password">
                @error('current_password') <div class="field-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-row">
            <div class="field">
                <label>New Password <span style="color:var(--red);">*</span></label>
                <input type="password" name="password" required autocomplete="new-password"
                       x-model="pw" @input="check()">
                @error('password') <div class="field-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror

                <div class="pw-strength-bar" x-show="pw.length > 0" x-transition>
                    <div class="pw-strength-fill" :class="strength"></div>
                </div>
                <div class="pw-strength-label" :class="strength" x-show="pw.length > 0" x-transition x-text="strengthLabel"></div>

                <div class="pw-reqs" x-show="pw.length > 0 || showReqs" x-transition>
                    <div class="pw-req" :class="{met: reqs.length}">
                        <i class="fa-solid" :class="reqs.length ? 'fa-circle-check' : 'fa-circle'"></i>
                        At least 8 characters
                    </div>
                    <div class="pw-req" :class="{met: reqs.mixedCase}">
                        <i class="fa-solid" :class="reqs.mixedCase ? 'fa-circle-check' : 'fa-circle'"></i>
                        Mix of uppercase and lowercase
                    </div>
                    <div class="pw-req" :class="{met: reqs.number}">
                        <i class="fa-solid" :class="reqs.number ? 'fa-circle-check' : 'fa-circle'"></i>
                        At least 1 number
                    </div>
                    <div class="pw-req" :class="{met: reqs.different}">
                        <i class="fa-solid" :class="reqs.different ? 'fa-circle-check' : 'fa-circle'"></i>
                        Different from current password
                    </div>
                </div>
            </div>
            <div class="field">
                <label>Confirm New Password <span style="color:var(--red);">*</span></label>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                       x-model="confirm">
                <div class="pw-req" :class="{met: matchOk}" x-show="confirm.length > 0" style="margin-top:8px;">
                    <i class="fa-solid" :class="matchOk ? 'fa-circle-check' : 'fa-circle-xmark'" :style="matchOk ? '' : 'color:var(--red);'"></i>
                    <span x-text="matchOk ? 'Passwords match' : 'Passwords do not match'"></span>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary" :disabled="!canSubmit">
                <i class="fa-solid fa-check"></i> Update Password
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
function pwForm() {
    return {
        pw: '', confirm: '',
        reqs: { length: false, mixedCase: false, number: false, different: true },
        strength: '',
        strengthLabel: '',
        showReqs: false,

        check() {
            this.reqs.length    = this.pw.length >= 8;
            this.reqs.mixedCase = /[a-z]/.test(this.pw) && /[A-Z]/.test(this.pw);
            this.reqs.number    = /[0-9]/.test(this.pw);
            this.reqs.different = true;   // can't really check client-side

            let score = 0;
            if (this.reqs.length)    score++;
            if (this.reqs.mixedCase) score++;
            if (this.reqs.number)    score++;
            if (/[^A-Za-z0-9]/.test(this.pw)) score++;
            if (this.pw.length >= 12) score++;

            if (this.pw.length < 4)  { this.strength = 'weak';   this.strengthLabel = 'Too weak'; }
            else if (score <= 2)     { this.strength = 'weak';   this.strengthLabel = 'Weak password'; }
            else if (score === 3)    { this.strength = 'fair';   this.strengthLabel = 'Fair password'; }
            else if (score === 4)    { this.strength = 'good';   this.strengthLabel = 'Good password'; }
            else                     { this.strength = 'strong'; this.strengthLabel = 'Strong password'; }
        },

        get matchOk() { return this.confirm.length > 0 && this.pw === this.confirm; },
        get canSubmit() {
            return this.reqs.length && this.reqs.mixedCase && this.reqs.number && this.matchOk;
        }
    };
}
</script>
@endpush