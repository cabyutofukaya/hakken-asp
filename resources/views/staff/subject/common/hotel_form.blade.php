<ul class="sideList">
  <li class="wd30">
    <?php $uci = $formSelects['userCustomItems']->firstWhere('code', config('consts.user_custom_items.CODE_SUBJECT_HOTEL_KBN'));?>
    @include('staff.common._custom_field', [
      'row' => $uci,
      'value' => $defaultValue[$uci->key],
      'addClass' => '',
      'customCategoryCode' => $customCategoryCode,
      'unedit' => $uci->unedit_item
      ])
  </li>{{-- カスタム項目。区分 --}}
  <li class="wd40 mr00">
    <span class="inputLabel req">商品コード</span>
    <input 
      type="text" 
      name="code" 
      value="{{ Arr::get($defaultValue, "code", "") }}"
      class="codeInput"
      @if($editMode === 'edit') disabled @endif
      >
  </li>
</ul>
<ul class="baseList">
  <li class="wd100">
    <span class="inputLabel req">商品名</span>
    <input type="text" name="name" value="{{ Arr::get($defaultValue, "name", "") }}">
  </li>
  <li class="wd100">
    <span class="inputLabel req">ホテル名</span>
    <input type="text" name="hotel_name" value="{{ Arr::get($defaultValue, "hotel_name", "") }}">
  </li>
  <li class="wd100">
    <span class="inputLabel">住所</span>
    <input type="text" name="address" value="{{ Arr::get($defaultValue, "address", "") }}">
  </li>
  <li class="wd40">
    <span class="inputLabel">電話番号</span>
    <input type="tel" name="tel" value="{{ Arr::get($defaultValue, "tel", "") }}">
  </li>
  <li class="wd40">
    <span class="inputLabel">FAX番号</span>
    <input type="tel" name="fax" value="{{ Arr::get($defaultValue, "fax", "") }}">
  </li>
  <li class="wd100">
    <span class="inputLabel">URL</span>
    <input type="text" name="url" value="{{ Arr::get($defaultValue, "url", "") }}">
  </li>
  {{-- <li class="wd40">
    <span class="inputLabel">都市・空港</span>
    <div class="selectBox">
      <select name="city_id">
        @foreach($formSelects['cities'] as $val => $str)
          <option value="{{ $val }}" @if(Arr::get($defaultValue, 'city_id', "") == $val) selected @endif>{{ $str }}</option>
        @endforeach
      </select>
    </div>
  </li> --}}
</ul>
<ul class="sideList half">
  <li>
    <?php $uci = $formSelects['userCustomItems']->firstWhere('code', config('consts.user_custom_items.CODE_SUBJECT_HOTEL_ROOM_TYPE'));?>
    @include('staff.common._custom_field', [
      'row' => $uci,
      'value' => $defaultValue[$uci->key],
      'addClass' => '',
      'customCategoryCode' => $customCategoryCode,
      'unedit' => $uci->unedit_item
      ])
  </li>{{-- カスタム項目。部屋タイプ --}}
  <li>
    <?php $uci = $formSelects['userCustomItems']->firstWhere('code', config('consts.user_custom_items.CODE_SUBJECT_HOTEL_MEAL_TYPE'));?>
    @include('staff.common._custom_field', [
      'row' => $uci,
      'value' => $defaultValue[$uci->key],
      'addClass' => '',
      'customCategoryCode' => $customCategoryCode,
      'unedit' => $uci->unedit_item
      ])
  </li>{{-- カスタム項目。食事タイプ --}}
</ul>

<hr class="sepBorder">

<ul class="baseList">
  <li class="wd50">
    <span class="inputLabel">仕入れ先</span>
    <div class="selectBox">
      <select name="supplier_id">
        @foreach($formSelects['suppliers'] as $id => $str)
          <option 
            value="{{ $id }}" 
            @if($id == Arr::get($defaultValue, "supplier_id", "")) selected @endif>{{ $str }}</option>
        @endforeach
      </select>
    </div>
  </li>
</ul>

<hr class="sepBorder" />

<div id="hotelPriceArea"
  agencyAccount='{{ $agencyAccount }}'
  zeiKbns='@json($formSelects['zeiKbns'])'
  defaultZeiKbn='{{ $consts['defaultZeiKbn'] }}'
  defaultValue='@json($defaultValue)'
></div>{{-- 料金入力エリア --}}

<hr class="sepBorder">

<ul class="baseList">
  <li>
    <span class="inputLabel">備考</span>
    <input type="text" name="note" value="{{ Arr::get($defaultValue, "note", "") }}">
  </li>
</ul>

<hr class="sepBorder">

{{-- カスタム項目 --}}
@if($formSelects['userCustomItems']->whereNotIn('code', [config('consts.user_custom_items.CODE_SUBJECT_HOTEL_KBN'),config('consts.user_custom_items.CODE_SUBJECT_HOTEL_ROOM_TYPE'),config('consts.user_custom_items.CODE_SUBJECT_HOTEL_MEAL_TYPE')])->isNotEmpty())
  <ul class="baseList">
    @foreach($formSelects['userCustomItems']->whereNotIn('code', [config('consts.user_custom_items.CODE_SUBJECT_HOTEL_KBN'),config('consts.user_custom_items.CODE_SUBJECT_HOTEL_ROOM_TYPE'),config('consts.user_custom_items.CODE_SUBJECT_HOTEL_MEAL_TYPE')]) as $uci)
      <li class="{{ $liClass ?? '' }}">
        @include('staff.common._custom_field', [
          'row' => $uci,
          'value' => $defaultValue[$uci->key],
          'addClass' => '',
          'customCategoryCode' => $customCategoryCode,
          'unedit' => $uci->unedit_item
          ])
      </li>
    @endforeach
  </ul>
@endif