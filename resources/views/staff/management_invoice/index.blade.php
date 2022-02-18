@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">get_app</span>請求管理</h1>

      <form method="GET" action="{{ route('staff.management.invoice.index', [$agencyAccount]) }}">

        <div id="searchBox">
          <div id="inputList">
            <ul class="sideList">
              <li class="wd20"><span class="inputLabel">ステータス</span>
                <div class="selectBox">
                  <select name="status">
                    @foreach($formSelects['statuses'] as $val => $label)
                      <option value="{{ $val }}"@if($val == $searchParam['status']) selected @endif>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
              </li>
              <li class="wd30">
                <span class="inputLabel">予約番号</span>
                <input type="text" name="reserve_number" value="{{ $searchParam['reserve_number'] }}">
              </li>
              <li class="wd25">
                <span class="inputLabel">予約申込者</span>
                <input type="text" name="applicant_name" value="{{ $searchParam['applicant_name'] }}">
              </li>
              <li class="wd25 mr00">
                <span class="inputLabel">自社担当</span>
                <div class="selectBox">
                  <select name="last_manager_id">
                    @foreach($formSelects['staffs'] as $id => $name)
                      <option value="{{ $id }}"@if($id == $searchParam['last_manager_id']) selected @endif>{{ $name }}</option>
                    @endforeach
                  </select>
                </div>
              </li>
            </ul>
            <ul class="sideList half">
              <li>
                <span class="inputLabel">発行日</span>
                <ul class="periodList">
                  <li>
                    <div class="calendar">
                      <input type="text" name="issue_date_from" value="{{ $searchParam['issue_date_from'] }}">
                    </div>
                  </li>
                  <li>
                    <div class="calendar">
                      <input type="text" name="issue_date_to" value="{{ $searchParam['issue_date_to'] }}">
                    </div>
                  </li>
                </ul>
              </li>
              <li>
                <span class="inputLabel">支払期限</span>
                <ul class="periodList">
                  <li>
                    <div class="calendar">
                      <input type="text" name="payment_deadline_from" value="{{ $searchParam['payment_deadline_from'] }}">
                    </div>
                  </li>
                  <li>
                    <div class="calendar">
                      <input type="text" name="payment_deadline_to" value="{{ $searchParam['payment_deadline_to'] }}">
                    </div>
                  </li>
                </ul>
              </li>
            </ul>
            @include('staff.common.search_option', [
              'items' => $formSelects['userCustomItems'],
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

    <div id="invoiceList" 
      searchParam='@json($searchParam)' 
      formSelects='@json($formSelects)' 
      modalFormSelects='@json($modalFormSelects)'
      consts='@json($consts)' 
      customFields='@json($customFields)' 
      customCategoryCode='{{ $customCategoryCode }}'
      jsVars='@json($jsVars)'
    ></div>
</main>

<!-- 検索フォーム用のjquery flatpickr -->
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
<script src="{{ mix('/staff/js/management_invoice-index.js') }}"></script>
@endsection
