<div id="{{ $fieldFormularID }}_Box" class="form-group form-floating mb-3">
    <input class="form-control" id="{{ $fieldFormularID }}" name="{{ $fieldID }}" type="text" value=""
           placeholder="{{ $fieldPlaceholder }}"
        {{ $requiredField }} {{ $fieldReadOnly }} />
    <label class="form-label" id="{{ $fieldFormularID }}_label" for="{{ $fieldFormularID }}">{{ $fieldName }}</label>
    <div class="form-text ms-1 mt-1">{!! $fieldDescription !!}</div>

</div>


