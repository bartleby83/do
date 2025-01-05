<div id="{{ $fieldFormularID }}_Box" class="form-group form-floating mb-3">
    <input class="form-control" id="{{ $fieldFormularID }}" name="{{ $fieldID }}" type="text"
           value="{{ $fieldValue }}"
           placeholder="{{ $fieldPlaceholder }}"
           @if($minLength !== null) minlength="{{ $minLength }}" @endif
           @if($maxLength !== null) maxlength="{{ $maxLength }}" @endif
           @if($minValue !== null) min="{{ $minValue }}" @endif
           @if($maxValue !== null) max="{{ $maxValue }}" @endif
           @if($fieldPattern !== null) pattern="{{ $fieldPattern }}" @endif
           @if($fieldReadOnly === 'readonly') disabled="disabled" @endif
            {{ $requiredField }} {{ $fieldReadOnly }} />
    <label id="{{ $fieldFormularID }}_label" for="{{ $fieldFormularID }}">{{ $fieldName }}</label>
    <div class="form-text ms-1 mt-1">{{ $fieldDescription }}</div>
</div>
