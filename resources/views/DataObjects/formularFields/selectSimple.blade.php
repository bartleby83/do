<div id="{{ $fieldFormularID }}_Box" class="form-group form-floating mb-3">
    <select class="form-select" id="{{ $fieldFormularID }}" name="{{ $fieldID }}"
            aria-label="Floating label select example">
        <option selected="">Auswählen...</option>
    </select>
    <label id="{{ $fieldFormularID }}_label" for="{{ $fieldFormularID }}">{{ $fieldName }}</label>
    <div class="form-text ms-1 mt-1">{!! $fieldDescription !!}</div>
</div>
