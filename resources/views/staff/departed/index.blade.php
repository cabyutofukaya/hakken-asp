@extends('layouts.staff.app')

@section('content')
@can('viewAny', App\Models\Reserve::class)
  <main>
    <div id="pageHead">
      <h1><span class="material-icons">event_available</span>催行済み一覧</h1>
      <form method="GET" action="{{ route('staff.estimates.departed.index', [$agencyAccount]) }}">
        <div id="searchBox">
          <div id="inputList">
            <ul class="sideList">
              <li class="wd20"><span class="inputLabel">予約番号</span>
                <input type="text" name="control_number" value="{{ $searchParam['control_number'] }}">
              </li>
              <li class="wd25"><span class="inputLabel">出発日</span>
                <div class="calendar"><input type="text" name="departure_date" value="{{ $searchParam['departure_date'] }}" autocomplete="off"></div>
              </li>
              <li class="wd25"><span class="inputLabel">帰着日</span>
                <div class="calendar"><input type="text" name="return_date" value="{{ $searchParam['return_date'] }}" autocomplete="off"></div>
              </li>
              @if($row = $formSelects['userCustomItemDatas']->first(function ($item, $key) {
                return $item['code'] === config('consts.user_custom_items.CODE_APPLICATION_APPLICATION_DATE');
              })){{-- 申込日 --}}
                <li class="wd25 mr00">
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
            <ul class="sideList">
              <li class="wd25"><span class="inputLabel">出発地</span>
                <input type="text" name="departure" value="{{ $searchParam['departure'] }}">
              </li>
              <li class="wd25"><span class="inputLabel">目的地</span>
                <input type="text" name="destination" value="{{ $searchParam['destination'] }}">
              </li>
              <li class="wd25"><span class="inputLabel">申込者</span>
                <input type="text" name="applicant" value="{{ $searchParam['applicant'] }}">
              </li>
              <li class="wd25 mr00"><span class="inputLabel">代表参加者</span>
                <input type="text" name="representative" value="{{ $searchParam['representative'] }}">
              </li>
            </ul>
            @include('staff.common.search_option', [
              'items' => $formSelects['userCustomItemDatas']->filter(function ($item, $key) {
                  return $item['code'] !== config('consts.user_custom_items.CODE_APPLICATION_APPLICATION_DATE'); // 「申込日」項目は除く
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

    <div id="departedList" class="tableWrap dragTable" 
      searchParam='@json($searchParam)' 
      jsVars='@json($jsVars)'
      >
    </div>

  </main>
@endcan

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
<script src="{{ mix('/staff/js/departed-index.js') }}"></script>
@endsection