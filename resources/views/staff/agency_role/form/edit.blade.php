<ul class="sideList half">
  <li><span class="inputLabel">権限名称</span>
    <input type="text" name="name" value="{{ old('name', isset($agencyRole) ? $agencyRole->name : null) }}" />
  </li>
  <li><span class="inputLabel">説明</span>
    <input type="text" name="description" value="{{ old('description', isset($agencyRole) ? $agencyRole->description : null) }}" />
  </li>
  <hr class="sepBorder">
</ul>

<ul class="sideList half">
  @foreach($formSelects['roleItems'] as $rows)
  <li>
    <h2 class="inputSubTit">{{ $rows['label'] }}<span class="selectControl"><span data-target_on="{{ $rows['target'] }}">全選択</span>/<span data-target_off="{{ $rows['target'] }}">全解除</span></span></h2>
    <ul class="checkBox sideList authList" data-target="{{ $rows['target'] }}">
      @foreach($rows['items'] as $item)
        <li>
          <input type="checkbox" id="{{ $rows['target'] }}_{{ $item['action'] }}" name="authority[{{ $rows['target'] }}][]" value="{{ $item['action'] }}" 
            @if(isset($agencyRole))
              @if(in_array($item['action'], old("authority.{$rows['target']}", data_get($agencyRole->authority, "{$rows['target']}", [])))) checked @endif
            @else
              @if(in_array($item['action'], old("authority.{$rows['target']}", []))) checked @endif
            @endif
            @if(data_get($agencyRole, 'master', false)) disabled @endif {{-- マスター権限は変更不可 --}}
            >
          <label for="{{ $rows['target'] }}_{{ $item['action'] }}">
            {{ $item['label'] }}
          </label>
        </li>
      @endforeach
      </ul>
    </li>
  @endforeach
</ul>