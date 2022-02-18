@extends('layouts.admin.app')

@section('content')
<main>
  <div id="pageHead">
    <h1>
      <span class="material-icons">notifications</span>通知管理
    </h1>
    <div class="rtBtn">
      <button onclick="location.href='{{ route('admin.web.system_news.create') }}'" class="addBtn"><span class="material-icons">notifications</span>新規通知追加</button>
    </div>
  </div>
  <div id="tableWrap" class="dragTable">
    <div id="tableCont">
      <table>
        <thead>
          <tr>
            <th class="sort" data-sort="regist_date"><span>登録日</span></th>
            <th><span>通知内容</span></th>
            <th><span>本文</span></th>
            <th class="txtalc"><span>変更</span></th>
            <th class="txtalc"><span>削除</span></th>
          </tr>
        </thead>
        <tbody>
          @forelse ($systemNews as $row)
            <tr>
              <td><a href="{{ route('admin.web.system_news.edit', $row->id) }}">{{ $row->regist_date }}</a></td>
              <td>{{ $row->title }}</td>
              <td>{{ mb_strimwidth($row->content, 0, 44, "...", 'UTF-8') }}</td>
              <td class="txtalc"><a href="{{ route('admin.web.system_news.edit', $row->id) }}"><span class="material-icons">edit_note</span></a></td>
              <td class="txtalc"><span class="material-icons js-modal-open" data-target="mdDelete" data-id="{{ $row->id }}">delete</span></td>
            </tr>
          @empty
            <tr>
              <td colspan="5">通知の登録はありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>

      {{ $systemNews->appends(request()->query())->links('vendor.pagination.app') }}

    </div>
  </div>
</main>


<div id="mdDelete" class="modal js-modal">
  <div class="modal__bg js-modal-close"></div>
  <div class="modal__content">
    <p class="mdTit mb20">この通知を削除しますか？</p>
    <ul class="sideList">
      <li class="wd50"><button class="grayBtn js-modal-close">閉じる</button></li>
      <li class="wd50 mr00">
        <form id="deleteForm" action="{{ route('admin.web.system_news.delete') }}" method="post">
          @csrf
          @method('DELETE')
          <input type="hidden" name="id" value=""/>
          <button class="redBtn">削除する</button>
        </form>
      </li>
    </ul>
  </div>
</div>

<script>
// 削除アイコンを押したら、削除用FormにIDをセット
$("[data-target=mdDelete]").click(function(){
  $("#deleteForm [name=id]").val($(this).data("id"));
});
</script>
@endsection
