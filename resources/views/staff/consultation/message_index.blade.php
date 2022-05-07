@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">question_answer</span>メッセージ履歴</h1>
    <form method="GET" action="{{ route('staff.consultation.message.index', [$agencyAccount]) }}">
      <div id="searchBox">
        <div id="inputList">
          <ul class="sideList">
            <li class="wd25">
              <span class="inputLabel">予約番号</span>
              <input type="text" name="record_number" value="{{ $searchParam['record_number'] }}">
            </li>
            <li class="wd50">
              <span class="inputLabel">タイトル</span>
              <input type="text" name="message_log" value="{{ $searchParam['message_log'] }}">
            </li>
            <li class="wd25 mr00"><span class="inputLabel">ステータス</span>
              <div class="selectBox">
                <select name="reserve_status">
                  <option value=""@if("" == Arr::get($searchParam, 'reserve_status', '')) selected @endif>すべて</option>
                  @foreach($formSelects['statuses'] as $status)
                    <option value="{{ $status }}" @if($status == Arr::get($searchParam, 'reserve_status', '')) selected @endif>{{ $status }}</option>
                  @endforeach
                </select>
              </div>
            </li>
          </ul>
          <ul class="sideList half">
            <li><span class="inputLabel">申込日</span>
              <ul class="periodList">
                <li>
                  <div class="calendar">
                    <input type="text" name="application_date_from" value="{{ $searchParam['application_date_from'] }}">
                  </div>
                </li>
                <li>
                  <div class="calendar">
                    <input type="text" name="application_date_to" value="{{ $searchParam['application_date_to'] }}">
                  </div>
                </li>
              </ul>
            </li>
            <li><span class="inputLabel">最新受信日</span>
              <ul class="periodList">
                <li>
                  <div class="calendar">
                    <input type="text" name="received_at_from" value="{{ $searchParam['received_at_from'] }}">
                  </div>
                </li>
                <li>
                  <div class="calendar">
                    <input type="text" name="received_at_to" value="{{ $searchParam['received_at_to'] }}">
                  </div>
                </li>
              </ul>
            </li>
          </ul>
          <ul class="sideList half">
            <li><span class="inputLabel">出発日</span>
              <ul class="periodList">
                <li>
                  <div class="calendar">
                    <input type="text" name="departure_date_from" value="{{ $searchParam['departure_date_from'] }}">
                  </div>
                </li>
                <li>
                  <div class="calendar">
                    <input type="text" name="departure_date_to" value="{{ $searchParam['departure_date_to'] }}">
                  </div>
                </li>
              </ul>
            </li>
            <li></li>
          </ul>
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

  <div id="messageList" 
    searchParam='@json($searchParam)' 
    formSelects='@json($formSelects)'
    consts='@json($consts)'
    jsVars='@json($jsVars)'
    >
  </div>

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
<script src="{{ mix('/staff/js/consultation_message-index.js') }}"></script>
@endsection