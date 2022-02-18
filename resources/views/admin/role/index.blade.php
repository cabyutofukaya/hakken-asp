@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.roles.index') }}

      <h1>権限一覧</h1>

      <div class="mb-2">
        <a class="btn btn-primary" href="{{ route('admin.roles.create') }}">新規登録</a>
      </div>

      <table class="table table-striped table-sm">
        <thead>
          <tr>
            <th scope="col">id</th>
            <th scope="col">権限</th>
            <th scope="col">英語名</th>
            <th scope="col">管理</th>
          </tr>
        </thead>
        <tbody>
          @forelse($roles as $role)
            <tr>
              <th scope="row">{{ $role->id }}</th>
              <td>{{ $role->name }}</td>
              <td>{{ $role->name_en }}</td>
              <td><a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-secondary">編集</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4">データがありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </main>
  </div>
</div>
@endsection
