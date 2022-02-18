{{-- 基本プロフィール編集フィールドはWebUserとAspUserで出し分け。WebUserは年齢区分以外編集不可 --}}
@if($userableType === 'App\Models\WebUser')
  @include('staff.user.common.web._base_profile')
@else
  @include('staff.user.common.asp._base_profile')
@endif

{{-- 勤務先/学校プロフィール編集フィールドはWebUserとAspUserで出し分け。WebUserは編集不可 --}}
@if($userableType === 'App\Models\WebUser')
  @include('staff.user.common.web._work_profile')
@else
  @include('staff.user.common.asp._work_profile')
@endif

<h2 class="subTit">
  <span class="material-icons">playlist_add_check</span>管理情報(カスタムフィールド)
</h2>
<div class="inputSubArea">
  <ul class="baseList">
    <li>
      <span class="inputLabel">自社担当</span>
      <div class="selectBox wd40">
        <select name="userable[user_ext][manager_id]">
          @foreach($formSelects['staffs'] as $val => $str)
            <option value="{{ $val }}" @if(Arr::get($defaultValue, 'userable.user_ext.manager_id') === $val) selected="selected" @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
  </ul>
  <ul class="sideList half">{{-- カスタム項目 --}}
    @foreach($formSelects['userCustomItems'][config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD')] as $uci)
      <li>
        @include('staff.common._custom_field', [
          'row' => $uci,
          'value' => Arr::get($defaultValue, $uci->key), 
          'addClass' => '',
          'customCategoryCode' => $customCategoryCode,
          'unedit' => $uci->unedit_item
          ])
      </li>
    @endforeach
  </ul>
  <ul class="baseList">
    <li class="wd20"><span class="inputLabel">DM</span>
      <div class="selectBox">
        <select name="userable[user_ext][dm]">
          @foreach($formSelects['dms'] as $val => $str)
            <option value="{{ $val }}" @if(Arr::get($defaultValue, 'userable.user_ext.dm', config('consts.users.DEFAULT_DM')) === $val) selected="selected" @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
    <li>
      <span class="inputLabel">備考</span>
      <textarea rows="3" name="userable[user_ext][note]">{{ Arr::get($defaultValue, 'userable.user_ext.note') }}</textarea>
    </li>
  </ul>
</div>

<h2 class="subTit">
  <span class="material-icons">more</span>その他オプション
</h2>
<div class="inputSubArea">
  <div id="visaInputArea" 
    defaultValue='@json(Arr::get($visaDefaultValue, "user_visas", []))' formSelects='@json($visaFormSelects)' 
    customCategoryCode='{{ $customCategoryCode }}'
    visaUserCustomItems='@json($visaUserCustomItems)'
    consts='@json($consts)'
    jsVars='@json($jsVars)'
    ></div>

  <div id="mileageInputArea" 
    defaultValue='@json(Arr::get($mileageDefaultValue, "user_mileages", []))' 
    formSelects='@json($mileageFormSelects)' 
    mileageUserCustomItems='@json($mileageUserCustomItems)' 
    customCategoryCode='{{ $customCategoryCode }}'
    consts='@json($consts)'
    jsVars='@json($jsVars)'
    ></div>

  <div id="memberCardInputArea" 
    defaultValue='@json(Arr::get($memberCardDefaultValue, "user_member_cards", []))' 
    formSelects='@json([])' 
    customCategoryCode='{{ $customCategoryCode }}'
    memberCardUserCustomItems='@json($memberCardUserCustomItems)'
    consts='@json($consts)'
    jsVars='@json($jsVars)'
    ></div>
</div>