<div id="inputArea" class="pt10">
  <h2 class="mb30 documentSubTit"><span class="material-icons">drive_file_rename_outline</span>テンプレート名・説明</h2>
    <ul class="baseList">
    <li class="wd40"><span class="inputLabel req">テンプレート名</span><input type="text" name="name" value="{{ $defaultValue['name'] ?? '' }}"></li>
    <li class="wd100"><span class="inputLabel">説明</span><input type="text" name="description" value="{{ $defaultValue['description'] ?? '' }}"></li></ul>
  <h2 class="mb30 documentSubTit pt00"><span class="material-icons">subject</span>出力項目設定</h2>
    <ul class="baseList">
      <li class="wd40"><span class="inputLabel req">表題</span>
        <input type="text" name="title" value="{{ $defaultValue['title'] ?? '' }}">
      </li>
      <li class="wd40">
        <span class="inputLabel">宛名/自社情報共通設定</span>
        <div class="selectBox">
          <select name="document_common_id">
            @foreach($formSelects['documentCommons'] as $val => $str)
              <option value="{{ $val }}"@if($defaultValue['document_common_id'] === $val) selected @endif>{{ $str }}</option>
            @endforeach
          </select>
        </div>
      </li>
    </ul>
    <ul class="documentCheckList">
      <li>
        <h3>{{ __("values.document_requests.display_block") }}</h3>
        <ul>
          <input type="hidden" name="setting[{{ config('consts.document_requests.DISPLAY_BLOCK') }}]" />{{-- チェックなしでも値が更新されるようにhiddenで初期化 --}}
          @foreach(array_filter(config('consts.document_requests.DISPLAY_BLOCK_LIST'), function ($key) {
            return $key !== '検印欄';
          }, ARRAY_FILTER_USE_KEY) as $parent => $childs) {{-- 「検印欄」は除外 --}}
            @include('staff.document_category.common._setting_row', [
              'checkValues' => Arr::get($defaultValue, 'setting.' . config('consts.document_requests.DISPLAY_BLOCK'), null),
              'parent' => $parent,
              'childs' => $childs,
              'name' => config('consts.document_requests.DISPLAY_BLOCK'),
              'idPrefix' => 'db',
            ])
          @endforeach
        </ul>
      </li>
      <li>
        <h3>{{ __("values.document_requests.reservation_info") }}</h3>
        <ul>
          <input type="hidden" name="setting[{{ config('consts.document_requests.RESERVATION_INFO') }}]" />{{-- チェックなしでも値が更新されるようにhiddenで初期化 --}}
          @foreach(config('consts.document_requests.RESERVATION_INFO_LIST') as $parent => $childs)
            @include('staff.document_category.common._setting_row', [
              'checkValues' => Arr::get($defaultValue, 'setting.' . config('consts.document_requests.RESERVATION_INFO'), null),
              'parent' => $parent,
              'childs' => $childs,
              'name' => config('consts.document_requests.RESERVATION_INFO'),
              'idPrefix' => 'ri',
            ])
          @endforeach
        </ul>
      </li>
      <li>
        <h3>{{ __("values.document_requests.air_ticket_info") }}</h3>
        <ul>
          <input type="hidden" name="setting[{{ config('consts.document_requests.AIR_TICKET_INFO') }}]" />{{-- チェックなしでも値が更新されるようにhiddenで初期化 --}}
          @foreach(config('consts.document_requests.AIR_TICKET_INFO_LIST') as $parent => $childs)
            @include('staff.document_category.common._setting_row', [
              'checkValues' => Arr::get($defaultValue, 'setting.' . config('consts.document_requests.AIR_TICKET_INFO'), null),
              'parent' => $parent,
              'childs' => $childs,
              'name' => config('consts.document_requests.AIR_TICKET_INFO'),
              'idPrefix' => 'ti',
            ])
          @endforeach
      </ul>
      <hr class="sepBorder">
      <h3 class="mt20">{{ __("values.document_requests.breakdown_price") }}</h3>
      <ul>
        <input type="hidden" name="setting[{{ config('consts.document_requests.BREAKDOWN_PRICE') }}]" />{{-- チェックなしでも値が更新されるようにhiddenで初期化 --}}
        @foreach(config('consts.document_requests.BREAKDOWN_PRICE_LIST') as $parent => $childs)
          @include('staff.document_category.common._setting_row', [
            'checkValues' => Arr::get($defaultValue, 'setting.' . config('consts.document_requests.BREAKDOWN_PRICE'), null),
            'parent' => $parent,
            'childs' => $childs,
            'name' => config('consts.document_requests.BREAKDOWN_PRICE'),
            'idPrefix' => 'bp',
          ])
        @endforeach
      </ul>
    </li>
    </ul>
    <hr class="sepBorder">

    <ul id="sealRow" class="documentCheckList" defaultValue='@json(collect($defaultValue)->only(['seal','seal_number','seal_items']))' formSelects='@json(collect($formSelects)->only(['sealNumbers']))'></ul>

    <ul class="baseList">
        <li class="wd100 mr00"><span class="inputLabel">枠下文言</span>
        <input type="text" name="seal_wording" value="{{ $defaultValue['seal_wording'] ?? '' }}" placeholder="例）検印なきものは無効です"></li></ul>
  <hr class="sepBorder">
    <h3 class="documentListTit mt30">案内文・振込先・備考</h3>
  <ul class="baseList mt20">		
      <li class="wd100"><span class="inputLabel">案内文</span>
        <textarea rows="5" name="information">{{ $defaultValue['information'] ?? '' }}</textarea>
      </li>
      <li class="wd100"><span class="inputLabel">振込先</span>
    <textarea rows="5" name="account_payable">{{ $defaultValue['account_payable'] ?? '' }}</textarea>
      </li>
      <li class="wd100"><span class="inputLabel">備考</span>
    <textarea rows="5" name="note">{{ $defaultValue['note'] ?? '' }}</textarea>
      </li>
      
  </ul>
  </div>