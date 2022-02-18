@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">person</span>個人顧客</h1>
    @can('create', App\Models\User::class)
      <div class="rtBtn">
        <button onclick="location.href='{{ route('staff.client.person.create', $agencyAccount) }}'" class="addBtn"><span class="material-icons">person</span>新規登録</button>
      </div>
    @endcan
    <form method="GET" action="{{ route('staff.client.person.index', [$agencyAccount]) }}">
      <div id="searchBox">
        <div id="inputList">
          <ul class="sideList">
            <li class="wd20"><span class="inputLabel">顧客番号</span>
              <input type="text" name="user_number" value="{{ $searchParam['user_number'] }}">
            </li>
            <li class="wd30"><span class="inputLabel">氏名</span>
              <input type="text" name="name" value="{{ $searchParam['name'] }}">
            </li>
            <li class="wd30"><span class="inputLabel">氏名(カナ)</span>
              <input type="text" name="name_kana" value="{{ $searchParam['name_kana'] }}">
            </li>
            <li class="wd30 mr00"><span class="inputLabel">氏名(ローマ字)</span>
              <input type="text" name="name_roman" value="{{ $searchParam['name_roman'] }}">
            </li>
          </ul>
          @include('staff.common.search_option', [
            'items' => $formSelects['userCustomItemDatas'],
            'searchParam' => $searchParam,
            'customCategoryCode' => $customCategoryCode
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

  <div id="userList" class="tableWrap dragTable" agencyAccount='{{$agencyAccount}}' searchParam='@json($searchParam)' formSelects='@json($formSelects)' statusList='@json($statusList)'></div>

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
<script src="{{ mix('/staff/js/user-index.js') }}"></script>
@endsection
