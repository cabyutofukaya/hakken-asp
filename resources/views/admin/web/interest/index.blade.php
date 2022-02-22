@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <h1>興味管理</h1>
      <div class="mb-2">
        {{-- <a class="btn btn-primary" href="{{ route('admin.hakken.interests.create') }}">新規登録</a> --}}
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
          @forelse($interests as $interest)
            <tr>
              <th scope="row">{{ $interest->id }}</th>
              <td>{{ $interest->name }}</td>
              <td>{{ $interest->seq }}</td>
              {{-- <td><a href="{{ route('admin.hakken.interests.edit', $interest->id) }}" class="btn btn-secondary">編集</a></td> --}}
            </tr>
          @empty
            <tr>
              <td colspan="4">データがありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      {{ $interests->appends(request()->query())->links() }}
    </main>
    
  </div>
</div>
@endsection
