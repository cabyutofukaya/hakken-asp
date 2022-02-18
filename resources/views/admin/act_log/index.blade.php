@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.act_logs.index') }}

      <h1>操作ログ</h1>
        <table class="table table-sm">
          <thead>
            <tr>
              <th>ID</th>
              <th>アクセスログ</th>
            </tr>
          </thead>
          <tbody>
          @forelse($actLogs as $actLog)
            <tr>
              <td>{{ $actLog->id }}</td>
              <td>
                <table>
                  <tr>
                    <td>guard #id</td>
                    <td>{{ $actLog->guard }} {{ $actLog->user_id }}</td>
                  </tr>
                  <tr>
                    <td>ルート</td>
                    <td>{{ $actLog->route }}</td>
                  </tr>
                  <tr>
                    <td>URL</td>
                    <td>{{ $actLog->url }}</td>
                  </tr>
                  <tr>
                    <td>メソッド</td>
                    <td>{{ $actLog->method }}</td>
                  </tr>
                  <tr>
                    <td>status</td>
                    <td>{{ $actLog->status }}</td>
                  </tr>
                  <tr>
                    <td>データ</td>
                    <td>{{ $actLog->message }}</td>
                  </tr>
                  <tr>
                    <td>IP</td>
                    <td>{{ $actLog->remote_addr }}</td>
                  </tr>
                  <tr>
                    <td>UA</td>
                    <td>{{ $actLog->user_agent }}</td>
                  </tr>
                  <tr>
                    <td>日時</td>
                    <td>{{ $actLog->created_at->format('Y.m.d H:i:s') }}</td>
                  </tr>
                </table>
              </td>
            </tr>
          @empty
            <tr>
              <td>データがありません</td>
            </tr>
          @endforelse
          </tbody>
        </table>
      {{ $actLogs->appends(request()->query())->links() }}
    </main>
  </div>
</div>

@endsection
