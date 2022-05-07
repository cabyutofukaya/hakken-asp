@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">question_answer</span>相談履歴</h1>
    <form method="GET" action="{{ route('staff.consultation.index', [$agencyAccount]) }}">
      <div id="searchBox">
        <div id="inputList">
          <ul class="sideList">
            <li class="wd25">
              <span class="inputLabel">見積/予約番号</span>
              <input type="text" name="reserve_estimate_number" value="{{ $searchParam['reserve_estimate_number'] }}">
            </li>
            <li class="wd50">
              <span class="inputLabel">タイトル</span>
              <input type="text" name="title" value="{{ $searchParam['title'] }}">
            </li>
            <li class="wd25 mr00">
              <span class="inputLabel">種別</span>
              <div class="selectBox">
                <select name="kind">
                  @foreach($formSelects['kinds'] as $k => $v)
                    <option value="{{ $k }}" @if($k == Arr::get($searchParam, 'kind', '')) selected @endif>{{ $v }}</option>
                  @endforeach
                </select>
              </div>
            </li>
          </ul>
        <ul class="sideList half">
          <li><span class="inputLabel">受付日</span>
            <ul class="periodList">
              <li>
                <div class="calendar">
                  <input type="text" name="reception_date_from" value="{{ $searchParam['reception_date_from'] }}" autocomplete="off">
                </div>
              </li>
              <li>
                <div class="calendar">
                  <input type="text" name="reception_date_to" value="{{ $searchParam['reception_date_to'] }}" autocomplete="off">
                </div>
              </li>
            </ul>
          </li>
          <li>
            <span class="inputLabel">期限</span>
            <ul class="periodList">
              <li><div class="calendar"><input type="text" name="deadline_from" value="{{ $searchParam['deadline_from'] }}" autocomplete="off"></div></li>
              <li><div class="calendar"><input type="text" name="deadline_to" value="{{ $searchParam['deadline_to'] }}" autocomplete="off"></div></li></ul>
          </li>
        </ul>
        <ul class="sideList half">
          <li><span class="inputLabel">出発日</span>
            <ul class="periodList">
              <li>
                <div class="calendar">
                  <input type="text" name="departure_date_from" value="{{ $searchParam['departure_date_from'] }}" autocomplete="off">
                </div>
              </li>
              <li>
                <div class="calendar">
                  <input type="text" name="departure_date_to" value="{{ $searchParam['departure_date_to'] }}" autocomplete="off">
                </div>
              </li>
            </ul>
          </li>
        </ul>
        @include('staff.common.search_option', [
          'items' => $formSelects['userCustomItemDatas'],
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

  <div id="consultationList" 
    defaultValue='@json($defaultValue)'
    searchParam='@json($searchParam)' 
    formSelects='@json($formSelects)'
    consts='@json($consts)'
    jsVars='@json($jsVars)'
    >
  </div>

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
<script src="{{ mix('/staff/js/consultation-index.js') }}"></script>
@endsection