<li class="checkBox">
  <input type="checkbox" id="{{ $idPrefix }}_{{ $parent }}" name="setting[{{ $name }}][]" value="{{ $parent }}"@if(is_array($checkValues) && 
    in_array($parent, $checkValues, true)) checked @endif>
  <label for="{{ $idPrefix }}_{{ $parent }}">{{ $parent }}</label>
</li>  
@if(count($childs)>0)
  @foreach($childs as $k => $child)
    <li class="checkBox ml30">
      <input type="checkbox" id="{{ $idPrefix }}_{{ $parent }}_{{ $child }}" name="setting[{{ $name }}][]" value="{{ $parent }}_{{ $child }}"@if(is_array($checkValues) && in_array("{$parent}_{$child}", $checkValues, true)) checked @endif>
      <label for="{{ $idPrefix }}_{{ $parent }}_{{ $child }}">{{ $child }}</label>
    </li>
  @endforeach
@endif

<script src="{{ mix('/staff/js/document-setting_row.js') }}"></script>