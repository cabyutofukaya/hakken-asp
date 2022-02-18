@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <h1>目的管理</h1>
      <div class="mb-2">
        <a class="btn btn-primary" href="{{ route('admin.hakken.purposes.create') }}">新規登録</a>
      </div>

      <table class="table table-striped table-sm">
        <thead>
          <tr>
            <th scope="col">@sortablelink('id', '#')</th>
            <th scope="col">名称</th>
            <th scope="col">順番</th>
            <th scope="col">管理</th>
          </tr>
        </thead>
        <tbody>
          @forelse($purposes as $purpose)
            <tr>
              <th scope="row">{{ $purpose->id }}</th>
              <td>{{ $purpose->name }}</td>
              <td>{{ $purpose->seq }}</td>
              <td><a href="{{ route('admin.hakken.purposes.edit', $purpose->id) }}" class="btn btn-secondary">編集</a></td>
            </tr>
          @empty
            <tr>
              <td colspan="4">データがありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      {{ $purposes->appends(request()->query())->links() }}
    </main>
  </div>
</div>
@endsection
