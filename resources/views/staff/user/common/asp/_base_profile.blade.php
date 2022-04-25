<div id="inputArea">
  @if($editMode === 'edit')
    <ul class="sideList">
      <li class="wd30"><span class="inputLabel">顧客番号</span>
          <input type="text" name="user_number" value="{{ $user->user_number ?? null }}" disabled>
      </li>
    </ul>
  @endif
  <ul class="sideList">
    <li class="wd40"><span class="inputLabel">氏名 ※姓名の間は半角スペース</span>
      <input type="text" name="userable[name]" value="{{ $defaultValue['userable']['name'] ?? null }}" placeholder="例）山田 太郎" maxlength="18">
    </li>
    <li class="wd40"><span class="inputLabel">氏名(カナ)</span>
      <input type="text" name="userable[name_kana]" value="{{ $defaultValue['userable']['name_kana'] ?? null }}" placeholder="例）ヤマダ タロウ" maxlength="32">
    </li>
    <li class="wd40 mr00"><span class="inputLabel">氏名(ローマ字)</span>
      <input type="text" name="userable[name_roman]" value="{{ $defaultValue['userable']['name_roman'] ?? null }}" placeholder="例）YAMADA TAROU" maxlength="32">
    </li>
  </ul>
  <ul class="sideList">
    <li class="wd20">
      <span class="inputLabel">性別</span>
      <ul class="baseRadio sideList half mt10">
        @foreach($formSelects['sexes'] as $val => $str)
          <li>
            <input type="radio" id="sex{{ $val }}" name="userable[sex]" value="{{ $val }}" @if(Arr::get($defaultValue, 'userable.sex', config('consts.users.DEFAULT_SEX')) === $val) checked @endif>
            <label for="sex{{ $val }}">{{ $str }}</label>
          </li>
        @endforeach
      </ul>
    </li>
    <li class="wd60"><span class="inputLabel">生年月日</span>
      <div class="selectSet wd100">
        <div class="selectBox wd40 mr10">
          <select name="userable[birthday_y]">
            @foreach($formSelects['birthdayYears'] as $val => $str)
              <option value="{{ $val }}" @if(Arr::get($defaultValue, 'userable.birthday_y', '') === $val) selected="selected" @endif>{{ $str }}</option>
            @endforeach
          </select>
        </div>
        <div class="selectBox wd30 mr10">
          <select name="userable[birthday_m]">
            @foreach($formSelects['birthdayMonths'] as $val => $str)
              <option value="{{ $val }}" @if(Arr::get($defaultValue, 'userable.birthday_m', '') === $val) selected="selected" @endif>{{ $str }}</option>
            @endforeach
          </select>
        </div>
        <div class="selectBox wd30">
          <select name="userable[birthday_d]">
            @foreach($formSelects['birthdayDays'] as $val => $str)
              <option value="{{ $val }}" @if(Arr::get($defaultValue, 'userable.birthday_d', '') === $val) selected="selected" @endif>{{ $str }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </li>
    <li class="wd20 mr00"><span class="inputLabel">年齢区分</span>
      <div class="selectBox">
        <select name="userable[user_ext][age_kbn]">
          @foreach($formSelects['ageKbns'] as $val => $str)
            <option value="{{ $val }}" @if(Arr::get($defaultValue, 'userable.user_ext.age_kbn', "") === $val) selected @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
  </ul>
  <hr class="sepBorder">
    
  <ul class="sideList half" id="contactInputArea" 
    defaultValue='@json($userableDefaultValue)'
    jsVars='@json($jsVars)'
  ></ul>

  <ul class="sideList half">
    @foreach(Arr::get($formSelects['userCustomItems'], config('consts.user_custom_items.POSITION_PERSON_EMERGENCY_CONTACT')) as $uci)
      <li>
        @include('staff.common._custom_field', [
          'row' => $uci,
          'value' => Arr::get($defaultValue, $uci->key), 
          'addClass' => '',
          'customCategoryCode' => $customCategoryCode,
          'unedit' => $uci->unedit_item
          ])
      </li>
    @endforeach{{-- 緊急連絡先のカスタムフィールド --}}
  </ul>
  
  <hr class="sepBorder">

  <ul class="baseList" id="addressInputArea" 
    defaultValue='@json($userableDefaultValue)' 
    formSelects='@json($addressFormSelects)'
    jsVars='@json($jsVars)'
  ></ul>

  <hr class="sepBorder">
  <ul class="sideList">
    <li><span class="inputLabel">旅券番号</span>
      <input type="text" name="userable[passport_number]" value="{{ $defaultValue['userable']['passport_number'] ?? null }}" maxlength="32">
    </li>
    <li>
      <span class="inputLabel">旅券発行日</span>
      <div class="calendar">
        <input type="text" name="userable[passport_issue_date]" value="{{ $defaultValue['userable']['passport_issue_date'] ?? null }}" autocomplete="off">
      </div>
    </li>
    <li>
      <span class="inputLabel">旅券有効期限</span>
      <div class="calendar">
        <input type="text" name="userable[passport_expiration_date]" value="{{ $defaultValue['userable']['passport_expiration_date'] ?? null }}" autocomplete="off">
      </div>
    </li>
    <li>
      <span class="inputLabel">旅券発行国</span>
      <div class="selectBox">
        <select name="userable[passport_issue_country_code]">
          @foreach($formSelects['countries'] as $val => $str)
            <option value="{{ $val }}" @if(Arr::get($defaultValue, 'userable.passport_issue_country_code', config('consts.users.DEFAULT_PASSPORT_ISSUE_COUNTRY')) === $val) selected="selected" @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
    <li class="mr00">
      <span class="inputLabel">国籍</span>
      <div class="selectBox">
        <select name="userable[citizenship_code]">
          @foreach($formSelects['countries'] as $val => $str)
            <option value="{{ $val }}" @if(Arr::get($defaultValue, 'userable.citizenship_code', config('consts.users.DEFAULT_CITIZENSHIP')) === $val) selected="selected"@endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
  </ul>
</div>