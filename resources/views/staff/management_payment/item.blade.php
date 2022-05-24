@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">upload</span>
      @if($supplier)
        {{ $supplier->name }}({{ optional($reserve)->control_number }}) 
      @else
        予約{{ optional($reserve)->control_number }} 
      @endif
      支払管理
    </h1>
    <ol class="breadCrumbs mb20">
      <li>
        <a href="{{ route('staff.management.payment.reserve', [$agencyAccount]) }}">支払管理</a>
      </li>
      <li><span>仕入先商品一覧</span></li>
    </ol>

      <form method="GET" action="{{ route('staff.management.payment.item', ['agencyAccount' => $agencyAccount, 'reserveHashId' => $reserveHashId]) }}">
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
              <li class="wd30"><span class="inputLabel">予約番号</span>
                <input type="text" name="reserve_number" value="{{ $searchParam['reserve_number'] }}">
              </li>
              <li class="wd25"><span class="inputLabel">仕入先</span>
                <input type="text" name="supplier_name" value="{{ $searchParam['supplier_name'] }}">
              </li>
              <li class="wd25 mr00"><span class="inputLabel">自社担当</span>
                <div class="selectBox">
                  <select name="last_manager_id">
                    @foreach($formSelects['staffs'] as $id => $name)
                      <option value="{{ $id }}"@if($id == $searchParam['last_manager_id']) selected @endif>{{ $name }}</option>
                    @endforeach
                  </select>
                </div>
              </li>
            </ul>
            <ul class="sideList">
              <li class="wd25"><span class="inputLabel">商品名</span>
                <input type="text" name="item_name" value="{{ $searchParam['item_name'] }}">
              </li>
              <li class="wd25">
                <span class="inputLabel">商品コード</span>
                <input type="text" name="item_code" value="{{ $searchParam['item_code'] }}">
              </li>
              <li class="wd50 mr00"><span class="inputLabel">支払予定日</span>
                <ul class="periodList">
                  <li>
                    <div class="calendar">
                      <input type="text" name="payment_date_from" value="{{ $searchParam['payment_date_from'] }}">
                    </div>
                  </li>
                  <li>
                    <div class="calendar">
                      <input type="text" name="payment_date_to" value="{{ $searchParam['payment_date_to'] }}">
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

  <div id="paymentList" 
    reserveHashId='{{ $reserveHashId }}'
    supplierHashId='{{ $supplierHashId }}'
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
<script src="{{ mix('/staff/js/management_payment-item.js') }}"></script>
@endsection
