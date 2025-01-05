<div id="{{ $fieldFormularID }}_Box" class="form-group form-floating mt-0 mb-3 pt-0">
    <select class="form-select selectpicker mt-0" data-options="{}" id="{{ $fieldFormularID }}" name="{{ $fieldID }}">
        <option value="">Auswählen...</option>
    </select>
    <label id="{{ $fieldFormularID }}_label" class="my-0" for="{{ $fieldFormularID }}">{{ $fieldName }}</label>
    <div class="form-text ms-1 mt-1">{!! $fieldDescription !!}</div>
</div>
