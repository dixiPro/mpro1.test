@props(['label', 'name'])
<div class="row mb-3 align-items-center">
    <div class="col-2">
        <label class="form-label mb-0">{{ $label }}</label>
    </div>
    <div class="col-5">
        <div class="input-group">
            <span class="input-group-text">from</span>
            <input type="number" class="form-control" name="{{ $name }}_from" value="{{ request($name . '_from') }}" min="0">
        </div>
    </div>
    <div class="col-5">
        <div class="input-group">
            <span class="input-group-text">to</span>
            <input type="number" class="form-control" name="{{ $name }}_to" value="{{ request($name . '_to') }}" min="0">
        </div>
    </div>
</div>