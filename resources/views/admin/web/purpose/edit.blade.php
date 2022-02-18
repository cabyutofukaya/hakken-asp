@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <h1>目的更新</h1>

      {!! Form::open(['route'=>['admin.hakken.purposes.update',$purpose->id], 'method'=>'put', 'id'=>'updateForm']) !!}

      <div class="form-group row">
        <label for="createdAt" class="col-sm-2 col-form-label">ID</label>
        <div class="col-sm-2">
          <p>{{ $purpose->id }}</p>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="name">目的</label>
          <input type="name" name='name' id="name" class="form-control @if($errors->has('name')) is-invalid @endif" value="{{ old('name', $purpose->name) }}">
          <div class="invalid-feedback">
          @if($errors->has('name')) {{ $errors->first('name') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="seq">順番</label>
          <input type="number" name='seq' id="seq" class="form-control @if($errors->has('seq')) is-invalid @endif" value="{{ old('seq', $purpose->seq) }}">
          <div class="invalid-feedback">
          @if($errors->has('seq')) {{ $errors->first('seq') }} @endif
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
        <h5 class="modal-title" id="deleteModalTitle">#{{$purpose->id}} 目的削除</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        目的を削除しますか？<br>この操作は取り消しできません。
      </div>
      <div class="modal-footer">
        {!! Form::open(['route'=>['admin.hakken.purposes.destroy',$purpose->id], 'method'=>'delete', 'id'=>'deleteForm']) !!}
          <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
          <button type="button" class="btn btn-danger" id="deleteButton">削除する</button>
        {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>


<script src="{{ mix('/admin/js/purposes-edit.js') }}"></script>
@endsection
