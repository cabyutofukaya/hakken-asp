@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.roles.edit', $role) }}

      <h1>権限編集</h1>

      {!! Form::open(['route'=>['admin.roles.update',$role->id], 'method'=>'put', 'id'=>'updateForm']) !!}

      @if($errors->has('auth_error')) 
      <div class="alert alert-danger" role="alert">{{ $errors->first('auth_error') }}</div>
      @endif

      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="name">権限名称</label>
          <input type="text" name='name' id="name" class="form-control @if($errors->has('name')) is-invalid @endif" value="{{ old('name', $role->name) }}" placeholder="">
          <div class="invalid-feedback">
          @if($errors->has('name')) {{ $errors->first('name') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="name_en">権限名称（英語）</label>
          <input type="text" name='name_en' id="name_en" class="form-control @if($errors->has('name_en')) is-invalid @endif" value="{{ old('name_en', $role->name_en) }}" placeholder="">
          <div class="invalid-feedback">
          @if($errors->has('name_en')) {{ $errors->first('name_en') }} @endif
          </div>
        </div>
      </div>
      
      @foreach($roleItems as $roleItem)
      <h4>{{ $roleItem['label'] }}</h4>
        <ul>
          @foreach($roleItem['items'] as $item)
            <li><input type="checkbox" id="{{ $roleItem['target'] }}_{{ $item['action'] }}" name="authority[{{ $roleItem['target'] }}][]" value="{{ $item['action'] }}" @if(in_array($item['action'], old("authority.{$roleItem['target']}", data_get($role->authority, "{$roleItem['target']}", [])))) checked @endif><label for="{{ $roleItem['target'] }}_{{ $item['action'] }}">{{ $item['label'] }}</label></li>
          @endforeach
        </ul>
      @endforeach

      <button class="btn btn-primary" type="submit" id="updateButton">更新</button>
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
        <h5 class="modal-title" id="deleteModalTitle">#{{$role->id}} 権限削除</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        権限を削除しますか？<br>削除すると本権限に紐づくスタッフの操作がすべて行えなくなります。<br>この操作は取り消しできません。
      </div>
      <div class="modal-footer">
        {!! Form::open(['route'=>['admin.roles.destroy',$role->id], 'method'=>'delete', 'id'=>'deleteForm']) !!}
          <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
          <button type="button" class="btn btn-danger" id="deleteButton">削除する</button>
        {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>

<script src="{{ mix('/admin/js/roles-edit.js') }}"></script>
@endsection
