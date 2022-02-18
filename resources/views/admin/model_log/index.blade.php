@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.model_logs.index') }}

      <h1>操作ログ</h1>

      <table class="w-100 table table-striped table-sm">
        <thead>
          <tr>
            <th scope="col">@sortablelink('id', '#')</th>
            <th scope="col">モデル #ID</th>
            <th scope="col">操作ユーザー #ID</th>
            <th scope="col">操作</th>
            <th scope="col">内容</th>
            <th scope="col">日時</th>
            <th scope="col">管理</th>
          </tr>
        </thead>
        <tbody>
          @forelse($modelLogs as $modelLog)
            <tr>
              <th scope="row">{{ $modelLog->id }}</th>
              <td>{{ $modelLog->model }} #{{ $modelLog->model_id }}</td>
              <td>{{ $modelLog->guard }} #{{ $modelLog->user_id }}</td>
              <td>{{ $modelLog->operation_type }}</td>
              <td>{{ mb_strimwidth($modelLog->message, 0, 50, "...") }}</td>
              <td>{{ $modelLog->created_at->format('Y-m-d H:i') }}</td>
              <td>
                <button type="button" class="btn btn-primary show-detail" data-toggle="modal" data-target="#detailModal" data-json="{{ $modelLog }}">
                  詳細
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7">データがありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      {{ $modelLogs->appends(request()->query())->links() }}
    </main>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        {{-- <h5 class="modal-title" id="detailModalTitle">Modal title</h5> --}}
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h3 class="h4">モデル #ID</h3>
        <div id="modelTxt" class="mb-3 ml-2"></div>
        <h3 class="h4">ガード #ID</h3>
        <div id="roleTxt" class="mb-3 ml-2"></div>
        <h3 class="h4">操作</h3>
        <div id="operationTxt" class="mb-3 ml-2"></div>
        <h3 class="h4">内容</h3>
        <pre id="messageTxt" class="mb-3 ml-2"></pre>
        <h3 class="h4">日時</h3>
        <div id="createdAtTxt" class="mb-3 ml-2"></div>
      </div>
      <div class="modal-footer"></div>
    </div>
  </div>
</div>

<script src="{{ mix('/admin/js/model_log-index.js') }}"></script>
@endsection
