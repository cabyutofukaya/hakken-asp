@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.inflows.edit', $inflow) }}

      <h1>流入サイト編集</h1>

      <p>顧客が登録したサイトとして顧客情報と紐づきます</p>

      {!! Form::open(['route'=>['admin.inflows.update',$inflow->id], 'method'=>'put', 'id'=>'updateForm']) !!}

      <input type="hidden" name="updated_at" value="{{ $inflow->updated_at }}"/>

      @if($errors->has('auth_error')) 
      <div class="alert alert-danger" role="alert">{{ $errors->first('auth_error') }}</div>
      @endif

      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="site_name">サイト名</label>
          <input type="text" name='site_name' id="site_name" class="form-control @if($errors->has('site_name')) is-invalid @endif" value="{{ old('site_name', $inflow->site_name) }}" placeholder="">
          <div class="invalid-feedback">
          @if($errors->has('site_name')) {{ $errors->first('site_name') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="url">URL</label>
          <input type="text" name='url' id="url" class="form-control @if($errors->has('url')) is-invalid @endif" value="{{ old('url', $inflow->url) }}" placeholder="">
          <div class="invalid-feedback">
          @if($errors->has('url')) {{ $errors->first('url') }} @endif
          </div>
        </div>
      </div>
      
      <button class="btn btn-primary" type="button" id="updateButton">更新</button>
      <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">削除</button>
      {!! Form::close() !!}
    </main>

  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalTitle">#{{$inflow->id}} 流入サイト削除</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        流入サイトを削除しますか？<br>
        流入サイトを削除すると旅行会社の顧客リストにて<br>流入サイトの表示が削除されます。<br>
        この操作は取り消しできません。
      </div>
      <div class="modal-footer">
        {!! Form::open(['route'=>['admin.inflows.destroy',$inflow->id], 'method'=>'delete', 'id'=>'deleteForm']) !!}
          <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
          <button type="button" class="btn btn-danger" id="deleteButton">削除する</button>
        {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>

<script src="{{ mix('/admin/js/inflow-edit.js') }}"></script>
@endsection
