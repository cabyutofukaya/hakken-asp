@extends('layouts.admin.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">person</span>個人顧客</h1>
    <form method="GET" action="{{ route('admin.web.web_users.index') }}">
      <div id="searchBox">
        <div id="inputList">
          <ul class="sideList">
            <li class="wd20"><span class="inputLabel">顧客番号</span>
              <input type="text" name="web_user_number" value="{{ request()->web_user_number }}">
            </li>
            <li class="wd30"><span class="inputLabel">氏名</span>
              <input type="text" name="name" value="{{ request()->name }}">
            </li>
            <li class="wd30"><span class="inputLabel">氏名(カナ)</span>
              <input type="text" name="name_kana" value="{{ request()->name_kana }}">
            </li>
            <li class="wd30 mr00"><span class="inputLabel">氏名(ローマ字)</span>
              <input type="text" name="name_roman" value="{{ request()->name_roman }}">
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
  
  @include("admin.common.decline_message")
  @include("admin.common.error_message")
  @include("admin.common.success_message")

  <div id="tableWrap" class="dragTable">
    <div id="tableCont">
      <table>
        <thead>
          <tr>
            <th class="sort" data-sort="web_user_number"><span>顧客番号</span></th>
            <th class="sort" data-sort="name"><span>氏名</span></th>
            <th class="sort" data-sort="name_kana"><span>氏名(カナ)</span></th>
            <th class="sort" data-sort="name_roman"><span>氏名(ローマ字)</span></th>
            <th class="sort" data-sort="mobile_phone"><span>電話番号</span></th>
            <th class="sort" data-sort="email"><span>メールアドレス</span></th>
            <th class="sort txtalc" data-sort="user_nustatusmber"><span>アカウント状態</span></th>
          </tr>
        </thead>
        <tbody>
          @forelse($webUsers as $webUser)
          <tr>
            <td><a href="{{ route('admin.web.web_users.edit', $webUser->id) }}">{{ $webUser->web_user_number }}</a></td>
            <td>{{ $webUser->name }}</td>
            <td>{{ $webUser->name_kana }}</td>
            <td>{{ $webUser->name_roman }}</td>
            <td>{{ $webUser->mobile_phone }}</td>
            <td>{{ $webUser->email }}</td>
            <td class="txtalc">
              @if($webUser->status)
                <span class="status green">有効</span>
              @else
                <span class="status gray">無効</span>
              @endif
            </td>
          </tr>
          @empty
            <tr>
              <td colspan="7">ユーザー情報がありません。</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      
      {{ $webUsers->appends(request()->query())->links('vendor.pagination.app') }}

    </div>
  </div>
</main>

<script>
  @include('admin.common._sortable_js')
</script>
<script src="{{ mix('/admin/js/sortable.js') }}"></script>
<script src="{{ mix('/admin/js/web_user-index.js') }}"></script>
@endsection
