<div class="form-group">
    <label class="form-label">
        {{ $label }} @if($required ?? false)<span class="req">*</span>@endif
    </label>
    <input type="{{ $type }}"
           name="{{ $name }}"
           value="{{ $value ?? '' }}"
           @if($required ?? false) required @endif
           placeholder="{{ $placeholder ?? '' }}"
           class="form-input">
    @error($name)
        <div class="form-error">{{ $message }}</div>
    @enderror
</div>