@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <h1>都道府県マスタ</h1>
      <table class="table table-striped table-sm">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">@sortablelink('code', '都道府県コード')</th>
            <th scope="col">都道府県名</th>
            <th scope="col">地方区分名</th>
          </tr>
        </thead>
        <tbody>
          @forelse($prefectures as $prefecture)
            <tr>
              <th scope="row">{{ $prefecture->id }}</th>
              <td>{{ $prefecture->code }}</td>
              <td>{{ $prefecture->name }}</td>
              <td>{{ $prefecture->block_name }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4">データがありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      {{ $prefectures->appends(request()->query())->links() }}
    </main>
  </div>
</div>
@endsection
