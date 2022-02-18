{{-- 科目マスタのindexページ以外で使用 --}}
@if($items->isNotEmpty())
  <div class="{{ Arr::get($searchParam, 'search_option_open') == 1 ? 'toggleOption active' : 'toggleOption' }}">
    <p>検索オプション</p>
  </div>
  <div id="searchOption" style="{{ Arr::get($searchParam, 'search_option_open') == 1 ? 'display:block' : 'display:none' }}">

    <input type="hidden" name="search_option_open" value="{{ Arr::get($searchParam, 'search_option_open', '') }}"/>{{-- 検索オプションを標準で開いた状態にするか否かのパラメータ  --}}
    
    <ul class="sideList customSearch">
      @foreach($items as $ucid)
        <li>
          <span class="inputLabel">{{ $ucid->name }}<a href="{{ route('staff.system.custom.index', ['agencyAccount' => $agencyAccount, 'tab' => $customCategoryCode]) }}"><span class="material-icons">settings</span></a></span>
          @if($ucid->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT'))
            <input type="text" name="{{ $ucid->key }}" value="{{ Arr::get($searchParam, $ucid->key, '') }}">
          @elseif($ucid->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'))
            <div class="selectBox">
              <select name="{{ $ucid->key }}">
                @foreach($ucid->select_item([''=>'すべて']) as $val => $str)
                <option value="{{ $val }}"
                @if(Arr::get($searchParam, $ucid->key, '') == $val) selected @endif
                >{{ $str }}</option>
                @endforeach
              </select>
            </div>
          @elseif($ucid->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'))
            <div class="calendar"><input type="text" name="{{ $ucid->key }}" value="{{ Arr::get($searchParam, $ucid->key, '') }}" autocomplete="off"></div>
          @endif
        </li>
      @endforeach
    </ul>		
  </div><!-- //#searchOption -->
@endif