<label id="{{ $fieldFormularID }}_label" class="text ms-3 mb-0"><b>{{ $fieldName }}</b></label>
<div id="{{ $fieldFormularID }}_Box" class="form-group mb-0 ms-3">
    @if(count($options) > 0)
        @foreach($options as $option)
               <div class="form-check">
                    <input class="form-check-input" id="{{ $fieldFormularID }}_{{ $option['value'] }}" value="{{ $option['value'] }}" type="checkbox" />
                    <label class="form-check-label" for="{{ $fieldFormularID }}_{{ $option['value'] }}">{{ $option['text'] }}</label>
                </div>
          @endforeach
    @endif
      <div class="form-text ms-1 mt-1 mb-1">{!! $fieldDescription !!}</div>
</div>
