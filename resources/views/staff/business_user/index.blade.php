@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">business</span>法人顧客</h1>
    @can('create', App\Models\BusinessUser::class)
      <div class="rtBtn">
        <button onclick="location.href='{{ route('staff.client.business.create', $agencyAccount) }}'" class="addBtn"><span class="material-icons">business</span>新規登録</button>
      </div>
    @endcan
    <form method="GET" action="{{ route('staff.client.business.index', [$agencyAccount]) }}">
      <div id="searchBox">
        <div id="inputList">
          <ul class="sideList">
            <li class="wd20">
              <span class="inputLabel">顧客番号</span>
              <input type="text" name="user_number" value="{{ $searchParam['user_number'] }}">
            </li>
            <li class="wd30">
              <span class="inputLabel">法人名</span>
              <input type="text" name="name" value="{{ $searchParam['name'] }}">
            </li>
            <li class="wd30">
              <span class="inputLabel">電話番号</span>
              <input type="text" name="tel" value="{{ $searchParam['tel'] }}">
            </li>
            @if($row = $formSelects['userCustomItemDatas']->first(function ($item, $key) {
              return $item['code'] === config('consts.user_custom_items.CODE_BUSINESS_CUSTOMER_KBN');
            })){{-- 顧客区分 --}}
              <li class="wd30 mr00">
                @include('staff.common._custom_field', [
                  'row' => $row,
                  'value' => $searchParam[$row->key],
                  'addClass' => '',
                  'customCategoryCode' => $customCategoryCode,
                  'unedit' => $row->unedit_item
                  ])
              </li>
            @endif
          </ul>
          @include('staff.common.search_option', [
            'items' => $formSelects['userCustomItemDatas']->filter(function ($item, $key) {
                return $item['code'] !== config('consts.user_custom_items.CODE_BUSINESS_CUSTOMER_KBN'); // 「顧客区分」項目は除く
            }),
            'searchParam' => $searchParam,
            'customCategoryCode' => $customCategoryCode,
          ])
        </div>
        <div id="controlList">
          <ul>
            <li>
              <button class="orangeBtn icon-left"><span class="material-icons">search</span>検索</button>
            </li>
            <li>
              <button class="grayBtn slimBtn" type="reset">条件クリア</button>
            </li>
          </ul>
        </div>
      </div>
    </form>
  </div>
  
  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')
  
  <div id="businessUserList" class="tableWrap dragTable" agencyAccount='{{$agencyAccount}}' searchParam='@json($searchParam)' formSelects='@json($formSelects)' statusList='@json($statusList)'></div>

</main>

<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> 
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script> 
<script>
    flatpickr.localize(flatpickr.l10ns.ja);
    flatpickr('.calendar input', {
        allowInput: true,
		dateFormat: "Y/m/d"
    });
</script>
<script src="{{ mix('/staff/js/business_user-index.js') }}"></script>
@endsection
