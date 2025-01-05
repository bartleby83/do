<div id="{{ $fieldFormularID }}_Box" class="form-group form-floating">
    <input class="form-control" id="{{ $fieldFormularID }}" name="{{ $fieldID }}" type="password"
           placeholder="Password"/>
    <label id="{{ $fieldFormularID }}_label" for="{{ $fieldFormularID }}">{{ $fieldName }}</label>
    <div class="form-text ms-1 mt-1">{{ $fieldDescription }}</div>
</div>

<div id="{{ $fieldFormularID }}_repeat_Box" class="form-group form-floating">
    <input class="form-control" id="{{ $fieldFormularID }}_repeat" name="{{ $fieldID }}_repeat" type="password"
           placeholder="Password"/>
    <label id="{{ $fieldFormularID }}_repeat_label" for="{{ $fieldFormularID }}_repeat">{{ $fieldName }}
        (Wiederholung)</label>
    <div class="form-text ms-1 mt-1">Wiederholen Sie die Eingabe zur Überprüfung</div>
</div>