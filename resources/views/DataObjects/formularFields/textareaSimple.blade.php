<div id="{{ $fieldFormularID }}_Box" class="form-group form-floating">
    <textarea class="form-control" id="{{ $fieldFormularID }}" name="{{ $fieldID }}" placeholder="Leave a comment here"
              style="height: 150px"
              @if($fieldReadOnly === 'readonly') disabled="disabled" @endif
            {{ $requiredField }} {{ $fieldReadOnly }}>
    </textarea>
    <label id="{{ $fieldFormularID }}_label" for="{{ $fieldFormularID }}">{{ $fieldName }}</label>
    <div class="form-text ms-1 mt-1">{{ $fieldDescription }}</div>
</div>
