@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">explore</span>方面マスタ</h1>
    @can('create', App\Models\Direction::class)
      <div class="rtBtn">
        <button onclick="location.href='{{ route('staff.master.direction.create', $agencyAccount) }}'" class="addBtn"><span class="material-icons">explore</span>新規追加</button>
      </div>
    @endcan
    <form method="GET" action="{{ route('staff.master.direction.index', [$agencyAccount]) }}">
      <div id="searchBox">
        <div id="inputList">
          <ul class="sideList">
            <li class="wd30"><span class="inputLabel">方面コード</span>
              <input type="text" name="code" value="{{ $searchParam['code'] }}">
            </li>
            <li class="wd70 mr00"><span class="inputLabel">方面名称</span>
              <input type="text" name="name" value="{{ $searchParam['name'] }}">
            </li>
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

  <div id="directionList" class="tableWrap dragTable" agencyAccount='{{$agencyAccount}}' searchParam='@json($searchParam)'></div>

</main>

<script src="{{ mix('/staff/js/direction-index.js') }}"></script>
@endsection
