@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.inflows.index') }}

      <h1>流入サイトマスタ</h1>

      <div class="mb-2">
        <a class="btn btn-primary" href="{{ route('admin.inflows.create') }}">新規登録</a>
      </div>

      <table class="table table-striped table-sm">
        <thead>
          <tr>
            <th scope="col">@sortablelink('id', '#')</th>
            <th scope="col">サイト名</th>
            <th scope="col">URL</th>
            <th scope="col">登録日</th>
            <th scope="col">管理</th>
          </tr>
        </thead>
        <tbody>
          @forelse($inflows as $inflow)
            <tr>
              <th scope="row">{{ $inflow->id }}</th>
              <td>{{ $inflow->site_name }}</td>
              <td>{{ $inflow->url }}</td>
              <td>{{ $inflow->created_at->format('Y-m-d H:i') }}</td>
              <td><a href="{{ route('admin.inflows.edit', $inflow->id) }}" class="btn btn-secondary">編集</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5">データがありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      {{ $inflows->appends(request()->query())->links() }}
    </main>
  </div>
</div>
@endsection
