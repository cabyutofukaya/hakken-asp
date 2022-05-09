<ul class="sideList">
  <li class="wd30">
    <span class="inputLabel req">商品コード</span>
    <input 
      type="text" 
      name="code" 
      value="{{ Arr::get($defaultValue, "code", "") }}"
      class="codeInput"
      @if($editMode === 'edit') disabled @endif
      >
  </li>
  <li class="wd70 mr00">
    <span class="inputLabel req">商品名</span>
    <input type="text" name="name" value="{{ Arr::get($defaultValue, "name", "") }}">
  </li>
</ul>
<ul class="sideList">
  <li class="wd30">
    <?php $uci = $formSelects['userCustomItems']->firstWhere('code', config('consts.user_custom_items.CODE_SUBJECT_AIRPLANE_COMPANY'));?>
    @include('staff.common._custom_field', [
      'row' => $uci,
      'value' => $defaultValue[$uci->key],
      'addClass' => '',
      'customCategoryCode' => $customCategoryCode,
      'unedit' => $uci->unedit_item
      ])
  </li>{{-- カスタム項目。航空会社 --}}
  <li class="wd40 mr00">
    <span class="inputLabel">予約クラス</span>
    <input type="text" name="booking_class" value="{{ Arr::get($defaultValue, "booking_class", "") }}">
  </li>
</ul>

  <ul class="sideList half" id="placeArea"
    jsVars='@json($jsVars)'
    defaultValue='@json($defaultValue)'
  >
    {{-- <li>
      <span class="inputLabel">出発地</span>
      <div class="selectBox">
        <select name="departure_id">
          @foreach($formSelects['cities'] as $id => $str)
            <option 
              value="{{ $id }}" 
              @if($id == Arr::get($defaultValue, "departure_id", "")) selected @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
    <li>
      <span class="inputLabel">目的地</span>
      <div class="selectBox">
        <select name="destination_id">
          @foreach($formSelects['cities'] as $id => $str)
            <option 
              value="{{ $id }}" 
              @if($id == Arr::get($defaultValue, "destination_id", "")) selected @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li> --}}
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

<div id="airplanePriceArea"
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
@if($formSelects['userCustomItems']->where('code', '!=', config('consts.user_custom_items.CODE_SUBJECT_AIRPLANE_COMPANY'))->isNotEmpty())
  <ul class="baseList">
    @foreach($formSelects['userCustomItems']->where('code', '!=', config('consts.user_custom_items.CODE_SUBJECT_AIRPLANE_COMPANY')) as $uci)
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