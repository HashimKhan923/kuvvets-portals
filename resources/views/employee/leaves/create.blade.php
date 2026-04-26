@extends('employee.layouts.app')
@section('title', 'Apply for Leave')
@section('page-title', 'Apply for Leave')
@section('page-sub', 'Submit a new leave request')

@push('styles')
<style>
    .form-card {
        background: var(--bg-card); border: 1px solid var(--border);
        border-radius: 16px; padding: 24px;
        max-width: 740px;
    }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
    .form-grid.full { grid-template-columns: 1fr; }

    .field { margin-bottom: 18px; }
    .field label {
        display: block;
        font-size: 11px; font-weight: 600;
        color: var(--text-primary);
        letter-spacing: .5px; text-transform: uppercase;
        margin-bottom: 8px;
    }
    .field label .req { color: var(--red); }
    .field label .hint { font-size: 10px; color: var(--text-muted); font-weight: 500; text-transform: none; letter-spacing: 0; margin-left: 6px; }

    .field input[type="text"],
    .field input[type="date"],
    .field input[type="tel"],
    .field select,
    .field textarea {
        width: 100%; height: 44px;
        padding: 0 14px;
        background: var(--bg-input);
        border: 1.5px solid transparent;
        border-radius: 10px;
        font: inherit; font-size: 13.5px; color: var(--text-primary);
        transition: all .15s;
    }
    .field textarea { height: auto; padding: 12px 14px; min-height: 100px; resize: vertical; }
    .field input:focus, .field select:focus, .field textarea:focus {
        outline: none; border-color: var(--accent); background: #fff;
        box-shadow: 0 0 0 3px rgba(194,83,27,.08);
    }

    .field-help {
        font-size: 11px; color: var(--text-muted); margin-top: 6px;
        display: flex; align-items: center; gap: 5px;
    }
    .field-err {
        font-size: 11.5px; color: var(--red); margin-top: 6px; font-weight: 600;
        display: flex; align-items: center; gap: 5px;
    }

    /* Leave type selector */
    .type-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 8px;
    }
    .type-opt {
        position: relative; cursor: pointer;
    }
    .type-opt input {
        position: absolute; opacity: 0; pointer-events: none;
    }
    .type-opt .type-card {
        padding: 12px 14px;
        background: var(--bg-input);
        border: 1.5px solid transparent;
        border-radius: 10px;
        transition: all .15s;
    }
    .type-opt:hover .type-card { border-color: var(--border-strong); background: #fff; }
    .type-opt input:checked + .type-card {
        border-color: var(--accent);
        background: var(--accent-bg);
        box-shadow: 0 0 0 3px rgba(194,83,27,.06);
    }
    .type-name { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--text-primary); }
    .type-name .dot { width: 8px; height: 8px; border-radius: 50%; }
    .type-bal { font-size: 11px; color: var(--text-muted); margin-top: 3px; font-variant-numeric: tabular-nums; }
    .type-unavail { opacity: .4; cursor: not-allowed; }
    .type-unavail input:checked + .type-card { border-color: var(--border); background: var(--bg-input); }

    /* Day type radio */
    .day-radio-group { display: flex; gap: 8px; flex-wrap: wrap; }
    .day-radio { position: relative; cursor: pointer; flex: 1; min-width: 120px; }
    .day-radio input { position: absolute; opacity: 0; }
    .day-radio span {
        display: block; text-align: center;
        padding: 10px 14px;
        background: var(--bg-input); border: 1.5px solid transparent;
        border-radius: 10px;
        font-size: 12.5px; font-weight: 600; color: var(--text-secondary);
        transition: all .15s;
    }
    .day-radio:hover span { background: #fff; border-color: var(--border-strong); }
    .day-radio input:checked + span { border-color: var(--accent); background: var(--accent-bg); color: var(--accent); }

    /* Emergency checkbox */
    .checkbox-row {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; background: var(--bg-muted);
        border-radius: 10px; cursor: pointer;
        font-size: 13px; font-weight: 500;
    }
    .checkbox-row input { width: 16px; height: 16px; accent-color: var(--accent); cursor: pointer; }

    /* Live calc box */
    .calc-box {
        padding: 14px 16px; background: linear-gradient(135deg, var(--accent-bg), #fff);
        border: 1.5px solid var(--accent-border);
        border-radius: 12px;
        display: flex; align-items: center; gap: 14px;
    }
    .calc-big {
        font-family: 'Space Grotesk', sans-serif; font-size: 30px; font-weight: 700;
        color: var(--accent); line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .calc-big .unit { font-size: 14px; color: var(--text-muted); font-weight: 500; margin-left: 3px; }
    .calc-meta { font-size: 11.5px; color: var(--text-secondary); line-height: 1.6; }
    .calc-meta strong { color: var(--text-primary); }
    .calc-err { background: var(--red-bg); border-color: var(--red-border); color: var(--red); }
    .calc-err .calc-big { color: var(--red); }
    .calc-empty { background: var(--bg-muted); border-color: var(--border); }
    .calc-empty .calc-big { color: var(--text-muted); }

    /* File upload */
    .file-box {
        display: flex; align-items: center; gap: 12px;
        padding: 14px; background: var(--bg-muted);
        border: 1.5px dashed var(--border-strong); border-radius: 10px;
        cursor: pointer;
    }
    .file-box:hover { background: var(--bg-hover); border-color: var(--accent); }
    .file-box i { font-size: 22px; color: var(--accent); }
    .file-hint { font-size: 12px; color: var(--text-secondary); }
    .file-sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .file-box input { display: none; }
    .file-selected {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 12px; background: var(--green-bg);
        border: 1px solid var(--green-border); border-radius: 10px;
        font-size: 12.5px; color: var(--green); font-weight: 600; margin-top: 8px;
    }
    .file-selected button {
        margin-left: auto; background: none; border: none;
        color: var(--red); cursor: pointer; font-size: 13px;
    }

    /* Submit actions */
    .actions {
        display: flex; gap: 10px; justify-content: flex-end;
        padding-top: 18px; border-top: 1px solid var(--border);
        margin-top: 8px;
    }
</style>
@endpush

@section('content')

<form method="POST" action="{{ route('employee.leaves.store') }}" enctype="multipart/form-data" class="form-card" x-data="applyForm()" x-init="init()">
    @csrf

    {{-- ═══════════ Leave Type ═══════════ --}}
    <div class="field">
        <label>Leave Type <span class="req">*</span></label>
        <div class="type-grid">
            @foreach($balances as $b)
                @php
                    $type = $b->leaveType;
                    $avail = $b->available;
                    $disabled = $type->is_paid && $avail <= 0;
                @endphp
                <label class="type-opt {{ $disabled ? 'type-unavail' : '' }}" :class="{'ring':selectedTypeId == {{ $type->id }}}">
                    <input type="radio" name="leave_type_id" value="{{ $type->id }}"
                           x-model="selectedTypeId"
                           @change="selectedType = {{ json_encode([
                               'id' => $type->id,
                               'name' => $type->name,
                               'color' => $type->color ?? '#C2531B',
                               'is_paid' => $type->is_paid,
                               'requires_document' => $type->requires_document,
                               'min_days_notice' => $type->min_days_notice,
                               'max_consecutive_days' => $type->max_consecutive_days,
                               'available' => (float) $avail,
                           ]) }}"
                           {{ old('leave_type_id') == $type->id ? 'checked' : '' }}
                           {{ $disabled ? 'disabled' : '' }}>
                    <div class="type-card">
                        <div class="type-name">
                            <span class="dot" style="background: {{ $type->color ?? '#C2531B' }};"></span>
                            {{ $type->name }}
                        </div>
                        <div class="type-bal">
                            @if($type->is_paid)
                                {{ rtrim(rtrim(number_format((float)$avail,1),'0'),'.') }} day(s) available
                            @else
                                Unpaid leave
                            @endif
                        </div>
                    </div>
                </label>
            @endforeach
        </div>
        @error('leave_type_id') <div class="field-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror
    </div>

    {{-- ═══════════ Day type ═══════════ --}}
    <div class="field">
        <label>Day Type <span class="req">*</span></label>
        <div class="day-radio-group">
            <label class="day-radio">
                <input type="radio" name="day_type" value="full_day" x-model="dayType" @change="recalc()" checked>
                <span>Full Day(s)</span>
            </label>
            <label class="day-radio">
                <input type="radio" name="day_type" value="half_day_morning" x-model="dayType" @change="recalc()">
                <span>Half – Morning</span>
            </label>
            <label class="day-radio">
                <input type="radio" name="day_type" value="half_day_afternoon" x-model="dayType" @change="recalc()">
                <span>Half – Afternoon</span>
            </label>
        </div>
    </div>

    {{-- ═══════════ Dates ═══════════ --}}
    <div class="form-grid">
        <div class="field">
            <label>From Date <span class="req">*</span></label>
            <input type="date" name="from_date" x-model="fromDate" @change="onFromChange()" required>
            @error('from_date') <div class="field-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror
        </div>
        <div class="field">
            <label>To Date <span class="req">*</span></label>
            <input type="date" name="to_date" x-model="toDate" @change="recalc()" :disabled="dayType !== 'full_day'" required>
            <div class="field-help" x-show="dayType !== 'full_day'">
                <i class="fa-solid fa-circle-info"></i> Half-day auto-uses "From date"
            </div>
            @error('to_date') <div class="field-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- ═══════════ Calc preview ═══════════ --}}
    <div class="field">
        <div class="calc-box" :class="calcClass">
            <div class="calc-big">
                <template x-if="!calc.loading && !calc.error">
                    <span>
                        <span x-text="calcDaysDisplay"></span><span class="unit">day<span x-show="calc.days !== 1">s</span></span>
                    </span>
                </template>
                <template x-if="calc.loading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </template>
                <template x-if="calc.error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </template>
            </div>
            <div class="calc-meta" x-show="!calc.loading">
                <template x-if="calc.error">
                    <div x-text="calc.error"></div>
                </template>
                <template x-if="!calc.error && calc.days > 0">
                    <div>
                        <strong x-text="calc.days"></strong> working day(s) counted.<br>
                        <span x-show="calc.weekend > 0"><i class="fa-solid fa-calendar-xmark" style="margin-right:3px;"></i><span x-text="calc.weekend"></span> weekend day(s) excluded</span>
                        <span x-show="calc.holiday > 0"><i class="fa-solid fa-flag" style="margin-right:3px;margin-left:8px;"></i><span x-text="calc.holiday"></span> holiday(s) excluded</span>
                    </div>
                </template>
                <template x-if="!calc.error && calc.days === 0 && fromDate && toDate">
                    <div>Please select valid dates.</div>
                </template>
                <template x-if="!calc.loading && !calc.error && !fromDate">
                    <div>Select dates to see leave days.</div>
                </template>
            </div>
        </div>
    </div>

    {{-- ═══════════ Reason ═══════════ --}}
    <div class="field">
        <label>Reason <span class="req">*</span> <span class="hint">(min. 10 characters)</span></label>
        <textarea name="reason" required minlength="10" maxlength="1000" placeholder="Briefly explain the reason for your leave...">{{ old('reason') }}</textarea>
        @error('reason') <div class="field-err"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div> @enderror
    </div>

    {{-- ═══════════ Emergency + Contact ═══════════ --}}
    <div class="form-grid">
        <div class="field">
            <label>Emergency Leave?</label>
            <label class="checkbox-row">
                <input type="checkbox" name="is_emergency" value="1" x-model="isEmergency" {{ old('is_emergency') ? 'checked' : '' }}>
                <span>Mark as emergency (bypasses notice period)</span>
            </label>
        </div>
        <div class="field">
            <label>Contact During Leave <span class="hint">(optional)</span></label>
            <input type="tel" name="contact_during_leave" placeholder="+92 3xx xxxxxxx" value="{{ old('contact_during_leave') }}">
        </div>
    </div>

    {{-- ═══════════ Document upload ═══════════ --}}
    <div class="field" x-show="showDocument">
        <label>
            Supporting Document
            <span class="req" x-show="selectedType && selectedType.requires_document">*</span>
            <span class="hint">(PDF/JPG/PNG, max 5MB)</span>
        </label>
        <label class="file-box" x-show="!docFile">
            <i class="fa-solid fa-file-arrow-up"></i>
            <div>
                <div class="file-hint">Click to upload document</div>
                <div class="file-sub">Required for medical, study, and other specific leave types</div>
            </div>
            <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" @change="onFileChange($event)">
        </label>
        <div x-show="docFile" class="file-selected">
            <i class="fa-solid fa-file-check"></i>
            <span x-text="docFile"></span>
            <button type="button" @click="clearFile($event)"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>

    {{-- ═══════════ Submit ═══════════ --}}
    <div class="actions">
        <a href="{{ route('employee.leaves.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary" :disabled="calc.error || calc.days <= 0">
            <i class="fa-solid fa-paper-plane"></i> Submit Request
        </button>
    </div>
</form>

@endsection

@push('scripts')
<script>
function applyForm() {
    return {
        selectedTypeId: "{{ old('leave_type_id') }}",
        selectedType: null,
        dayType: "{{ old('day_type','full_day') }}",
        fromDate: "{{ old('from_date') }}",
        toDate: "{{ old('to_date') }}",
        isEmergency: {{ old('is_emergency') ? 'true' : 'false' }},
        docFile: null,
        calc: { days: 0, weekend: 0, holiday: 0, error: null, loading: false },
        calcTimeout: null,

        init() {
            // Pre-select if old value exists
            if (this.selectedTypeId) {
                document.querySelector('input[name="leave_type_id"]:checked')?.dispatchEvent(new Event('change'));
            }
            if (this.fromDate && this.toDate) this.recalc();
        },

        get showDocument() {
            return this.selectedType && this.selectedType.requires_document || this.selectedTypeId;
        },

        get calcClass() {
            if (this.calc.error) return 'calc-err';
            if (!this.fromDate || this.calc.days <= 0) return 'calc-empty';
            return '';
        },

        get calcDaysDisplay() {
            const n = this.calc.days;
            if (n % 1 === 0) return n.toString();
            return n.toFixed(1);
        },

        onFromChange() {
            if (this.dayType !== 'full_day') {
                this.toDate = this.fromDate;
            } else if (!this.toDate || new Date(this.toDate) < new Date(this.fromDate)) {
                this.toDate = this.fromDate;
            }
            this.recalc();
        },

        recalc() {
            if (this.dayType !== 'full_day') this.toDate = this.fromDate;
            if (!this.fromDate || !this.toDate) {
                this.calc = { days: 0, weekend: 0, holiday: 0, error: null, loading: false };
                return;
            }
            clearTimeout(this.calcTimeout);
            this.calc.loading = true;
            this.calcTimeout = setTimeout(() => this._fetchCalc(), 350);
        },

        async _fetchCalc() {
            try {
                const params = new URLSearchParams({
                    from_date: this.fromDate,
                    to_date:   this.toDate,
                    day_type:  this.dayType,
                });
                const res = await fetch("{{ route('employee.leaves.calculate') }}?" + params, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if (data.error) {
                    this.calc = { days: 0, weekend: 0, holiday: 0, error: data.error, loading: false };
                    return;
                }
                this.calc = {
                    days:    data.days,
                    weekend: data.weekend,
                    holiday: data.holiday,
                    error:   null,
                    loading: false,
                };

                // Front-end validation against balance
                if (this.selectedType && this.selectedType.is_paid && this.calc.days > this.selectedType.available) {
                    this.calc.error = `Insufficient balance. You have ${this.selectedType.available} day(s) available.`;
                }
                if (this.selectedType && this.selectedType.max_consecutive_days && this.calc.days > this.selectedType.max_consecutive_days) {
                    this.calc.error = `Max ${this.selectedType.max_consecutive_days} consecutive days allowed.`;
                }
            } catch (e) {
                this.calc = { days: 0, weekend: 0, holiday: 0, error: 'Could not calculate days.', loading: false };
            }
        },

        onFileChange(e) {
            const f = e.target.files[0];
            if (!f) return;
            if (f.size > 5 * 1024 * 1024) {
                alert('File must be under 5MB');
                e.target.value = '';
                return;
            }
            this.docFile = f.name;
        },

        clearFile(e) {
            this.docFile = null;
            document.querySelector('input[name="document"]').value = '';
        }
    };
}
</script>
@endpush