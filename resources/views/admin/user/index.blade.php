@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.users.index') }}

      <h1>顧客管理（個人）</h1>
      <div class="mb-2">
        <a class="btn btn-primary" href="{{ route('admin.users.create') }}">新規登録</a>
      </div>

      <table class="table table-striped table-sm">
        <thead>
          <tr>
            <th scope="col">@sortablelink('id', '#')</th>
            <th scope="col">名前</th>
            <th scope="col">従属会社</th>
            <th scope="col">お客様番号</th>
            <th scope="col">流入サイト</th>
            <th scope="col">メールアドレス</th>
            <th scope="col">状態</th>
            <th scope="col">登録日時</th>
            <th scope="col">管理</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            <tr>
              <th scope="row">{{ $user->id }}</th>
              <td>{{ $user->name }}</td>
              <td>{{ $user->agency->company_name }}</td>
              <td>{{ $user->user_number }}</td>
              <td>{{ $user->inflow->site_name }}</td>
              <td>{{ $user->email }}</td>
              <td>{{ $user->status_label }}</td>
              <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
              <td>
                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info">詳細</a>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-secondary">編集</a></td>
            </tr>
          @empty
            <tr>
              <td colspan="9">データがありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      {{ $users->appends(request()->query())->links() }}
    </main>
  </div>
</div>
@endsection
