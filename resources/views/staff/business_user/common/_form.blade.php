<div id="inputArea">
  @if($editMode === 'edit')
    <ul class="sideList">
      <li class="wd30"><span class="inputLabel">顧客番号</span>
          <input type="text" name="user_number" value="{{ $businessUser->user_number ?? null }}" disabled>
      </li>
    </ul>
  @endif
  <ul class="sideList">
    <li class="wd40"><span class="inputLabel">法人名</span>
        <input type="text" name="name" value="{{ $defaultValue['name'] ?? null }}" placeholder="例）株式会社キャブステーション">
      </li>
    <li class="wd40"><span class="inputLabel">法人名(カナ) 
      </span>
        <input type="text" name="name_kana" value="{{ $defaultValue['name_kana'] ?? null }}" placeholder="例）カブシキガイシャ キャブステーション">
      </li>
    <li class="wd40 mr00"><span class="inputLabel">法人名(英語表記)</span>
        <input type="text" name="name_roman" value="{{ $defaultValue['name_roman'] ?? null }}" placeholder="例）CAB STATION.co.ltd">
      </li>
  </ul>

  <hr class="sepBorder">
  
  <ul class="sideList half">
    <li><span class="inputLabel">電話番号</span>
      <input type="tel" name="tel" value="{{ $defaultValue['tel'] ?? null }}" placeholder="例）03-1111-1111">
    </li>
    <li><span class="inputLabel">FAX</span>
      <input type="tel" name="fax" value="{{ $defaultValue['fax'] ?? null }}" placeholder="例）03-1111-1111">
    </li>
  </ul>

  <hr class="sepBorder">

  <ul class="baseList" id="addressInputArea" defaultValue='@json($addressDefaultValue)' formSelects='@json($addressFormSelects)'></ul>

</div>

<h2 class="subTit"><span class="material-icons">
playlist_add_check</span>取引先担当者</h2>
<div class="inputSubArea">
  <div id="managerInputArea" defaultValue='@json(Arr::get($managerDefaultValue, "business_user_managers", []))' formSelects='@json($managerFormSelects)'></div>
</div>

<h2 class="subTit">
  <span class="material-icons">playlist_add_check</span>管理情報(カスタムフィールド)
</h2>
<div class="inputSubArea">
  <ul class="baseList">
    <li>
      <span class="inputLabel">自社担当</span>
      <div class="selectBox wd40">
        <select name="manager_id">
          @foreach($formSelects['staffs'] as $val => $str)
            <option value="{{ $val }}" @if(Arr::get($defaultValue, 'manager_id') == $val) selected="selected" @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
  </ul>
  <ul class="sideList half">{{-- カスタム項目 --}}
    @foreach($formSelects['userCustomItems'][config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD')] as $row)
      <li>
        @include('staff.common._custom_field', [
          'customCategoryCode' => $customCategoryCode,
          'row' => $row, 
          'value' => Arr::get($defaultValue, $row->key),
          'addClass' => '',
          'unedit' => $row->unedit_item
        ])
      </li>
    @endforeach
  </ul>
  <ul class="baseList">
    <li class="wd20"><span class="inputLabel">一括支払契約</span>
      <div class="selectBox">
        <select name="pay_altogether">
          @foreach($formSelects['pay_altogethers'] as $val => $str)
            <option value="{{ $val }}" @if(Arr::get($defaultValue, 'pay_altogether', config('consts.business_users.DEFAULT_PAY_ALTOGETHER')) == $val) selected="selected" @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
    <li>
      <span class="inputLabel">備考</span>
      <textarea rows="3" name="note">{{ $defaultValue['note'] ?? null }}</textarea>
    </li>
  </ul>
</div>
