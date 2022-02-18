@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">person_search</span>ユーザー管理</h1>
    @can('create', [new App\Models\Staff, $agencyId])
      <div class="rtBtn">
        <button onclick="location.href='{{ route('staff.system.user.create', [$agencyAccount]) }}'" class="addBtn">
          <span class="material-icons">person_add</span>ユーザー追加
        </button>
      </div>
    @endcan
    <form method="GET" action="{{ route('staff.system.user.index', [$agencyAccount]) }}">
      <div id="searchBox">
        <div id="inputList">
          <ul class="sideList">
            <li class="wd35"><span class="inputLabel">アカウントID</span>
              <input type="text" name="account" value="{{ $searchParam['account'] }}">
            </li>
            <li class="wd35"><span class="inputLabel">ユーザー名&nbsp;</span>
              <input type="text" name="name" value="{{ $searchParam['name'] }}">
            </li>
            <li class="wd30 mr00"><span class="inputLabel">ユーザー権限<a href="{{ route('staff.system.role.index', [$agencyAccount]) }}"><span class="material-icons">settings</span></a></span>
              <div class="selectBox">
                <select name="agency_role_id">
                  @foreach($formSelects['agencyRoles'] as $val => $str)
                  <option value="{{ $val }}"
                    @if($searchParam['agency_role_id'] == $val) selected @endif
                    >{{ $str }}</option>
                  @endforeach
                </select>
              </div>
            </li>
          </ul>
          <ul class="sideList">
            <li class="wd35"><span class="inputLabel">メールアドレス</span>
              <input type="email" name="email" value="{{ $searchParam['email'] }}">
            </li>
            {{-- 所属 --}}
            <li class="wd35">
              <span class="inputLabel">{{ $formSelects['shozokuItemData']['name'] }}<a href="{{ route('staff.system.custom.index', ['agencyAccount' => $agencyAccount, 'tab' => $customCategoryCode]) }}"><span class="material-icons">settings</span></a></span>
              <div class="selectBox">
                <select name="{{ $formSelects['shozokuItemData']->key }}">
                  @foreach($formSelects['shozokuItemData']->select_item([''=>'すべて']) as $val => $str)
                    <option value="{{ $val }}"
                    @if(Arr::get($searchParam, $formSelects['shozokuItemData']->key, "") == $val) selected @endif
                    >{{ $str }}</option>
                  @endforeach
                </select>
              </div>
            </li>
            <li class="wd30 mr00"><span class="inputLabel">アカウント状態</span>
              <div class="selectBox">
                <select name="status">
                  @foreach($formSelects['statuses'] as $val => $str)
                    <option value="{{ $val }}"
                      @if(strlen($searchParam['status']) > 0 && $searchParam['status'] == $val) selected @endif
                      >{{ $str }}</option>
                  @endforeach
                </select>
              </div>
            </li>
          </ul><!-- //.sideList-->
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

  <div id="staffList" class="tableWrap dragTable" 
    agencyAccount='{{$agencyAccount}}' 
    searchParam='@json($searchParam)' 
    formSelects='@json($formSelects)'
    >
  </div>

</main>
<script src="{{ mix('/staff/js/staff-index.js') }}"></script>
@endsection
