@if($row->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT')){{-- テキストタイプ --}}
  @if($row->input_type === config('consts.user_custom_items.INPUT_TYPE_TEXT_01'))
    <span class="inputLabel">{{ $row->name }}
      @if(!$unedit)
        <a href="{{ route('staff.system.custom.index', [
          'agencyAccount' => $agencyAccount, 
          'tab' => $customCategoryCode
          ]) }}">
          <span class="material-icons">settings</span>
        </a>
      @endif
    </span>
    <input type="text" name="{{ $row->key }}" value="{{ $value }}">
  @elseif($row->input_type === config('consts.user_custom_items.INPUT_TYPE_TEXT_02'))
    <span class="inputLabel">{{ $row->name }}
      @if(!$unedit)
        <a href="{{ route('staff.system.custom.index', [
          'agencyAccount' => $agencyAccount, 
          'tab' => $customCategoryCode
          ]) }}">
          <span class="material-icons">settings</span>
        </a>
      @endif
    </span>
    <textarea name="{{ $row->key }}">{{ $value }}</textarea>
  @endif

@elseif($row->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST')) {{-- リストタイプ --}}
  <span class="inputLabel">{{ $row->name }}
    @if(!$unedit)
      <a href="{{ route('staff.system.custom.index', [
        'agencyAccount' => $agencyAccount, 
        'tab' => $customCategoryCode
        ]) }}">
        <span class="material-icons">settings</span>
      </a>
    @endif
  </span>
  <div class="selectBox {{ $addClass }}">
    <select name="{{ $row->key }}">
      @foreach($row->select_item(['' => '-']) as $val => $str)
        <option value="{{ $val }}" @if($val == $value) selected @endif>{{ $str }}</option>
      @endforeach
    </select>
  </div>

@elseif($row->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE')) {{-- 日付タイプ --}}
  @if($row->input_type === config('consts.user_custom_items.INPUT_TYPE_DATE_01'))
    <span class="inputLabel">{{ $row->name }}
      @if(!$unedit)
        <a href="{{ route('staff.system.custom.index', [
          'agencyAccount' => $agencyAccount, 
          'tab' => $customCategoryCode
          ]) }}">
          <span class="material-icons">settings</span>
        </a>
      @endif
    </span>
    <div class="calendar">
      <input type="text" name="{{ $row->key }}" value="{{ $value }}" autocomplete="off">
    </div>
  @elseif($row->input_type === config('consts.user_custom_items.INPUT_TYPE_DATE_02'))
    <span class="inputLabel">{{ $row->name }}
      @if(!$unedit)
        <a href="{{ route('staff.system.custom.index', [
          'agencyAccount' => $agencyAccount, 
          'tab' => $customCategoryCode
          ]) }}">
          <span class="material-icons">settings</span>
        </a>
      @endif
    </span>
    <input type="text" name="{{ $row->key }}" value="{{ $value }}">
  @endif

@endif