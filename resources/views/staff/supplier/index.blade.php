@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">move_to_inbox</span>仕入れ先マスタ</h1>
    @can('create', App\Models\Supplier::class)
      <div class="rtBtn">
        <button onclick="location.href='{{ route('staff.master.supplier.create', $agencyAccount) }}'" class="addBtn"><span class="material-icons">move_to_inbox</span>新規追加</button>
      </div>
    @endcan
    <form method="GET" action="{{ route('staff.master.supplier.index', [$agencyAccount]) }}">
      <div id="searchBox">
        <div id="inputList">
          <ul class="sideList">
            <li class="wd30"><span class="inputLabel">仕入れ先コード</span>
              <input type="text" name="code" value="{{ $searchParam['code'] }}">
            </li>
            <li class="wd70 mr00"><span class="inputLabel">仕入れ先名称</span>
              <input type="text" name="name" value="{{ $searchParam['name'] }}">
            </li>
          </ul><!-- //.sideList-->
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
  
  <div id="supplierList" class="tableWrap dragTable" 
    jsVars='@json($jsVars)'
    searchParam='@json($searchParam)' 
    formSelects='@json($formSelects)'
    ></div>

</main>

<script src="{{ mix('/staff/js/supplier-index.js') }}"></script>
@endsection
