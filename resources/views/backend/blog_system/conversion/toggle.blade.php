@php
    $enabled = (string) get_setting($name, 1) === '1';
@endphp

<div class="form-group row align-items-center">
    <label class="col-md-8 mb-0">{{ $label }}</label>
    <div class="col-md-4 text-md-right">
        <input type="hidden" name="{{ $name }}" value="0">
        <label class="aiz-switch aiz-switch-success mb-0">
            <input type="checkbox" name="{{ $name }}" value="1" @checked($enabled)>
            <span></span>
        </label>
    </div>
</div>
