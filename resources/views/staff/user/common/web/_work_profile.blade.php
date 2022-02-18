<h2 class="subTit">
  <span class="material-icons">perm_contact_calendar</span>勤務先/学校
</h2>
<div class="inputSubArea">
  <ul class="baseList">
    <li class="wd50">
      <span class="inputLabel">名称</span>
      <input type="text" name="userable[workspace_name]" value="{{ $defaultValue['userable']['workspace_name'] ?? null }}" maxlength="32" disabled>
    </li>
    <li class="wd100">
      <span class="inputLabel">住所</span>
      <input type="text" name="userable[workspace_address]" value="{{ $defaultValue['userable']['workspace_address'] ?? null }}" maxlength="100" disabled>
    </li>
    <li class="wd50">
      <span class="inputLabel">電話番号</span>
      <input type="tel" name="userable[workspace_tel]" value="{{ $defaultValue['userable']['workspace_tel'] ?? null }}" maxlength="32" disabled>
    </li>
    @foreach(Arr::get($formSelects['userCustomItems'], config('consts.user_custom_items.POSITION_PERSON_WORKSPACE_SCHOOL')) as $uci)
      <li class="wd50">
        @include('staff.common._custom_field', [
          'row' => $uci,
          'value' => Arr::get($defaultValue, $uci->key), 
          'addClass' => '',
          'customCategoryCode' => $customCategoryCode,
          'unedit' => $uci->unedit_item
          ])
      </li>
    @endforeach{{-- 務先/学校のカスタムフィールド --}}
    <li class="wd100">
      <span class="inputLabel">備考</span>
      <textarea rows="3" name="userable[workspace_note]" disabled>{{ $defaultValue['userable']['workspace_note'] ?? null }}</textarea>
    </li>
  </ul>
</div>