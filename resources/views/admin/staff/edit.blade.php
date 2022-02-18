@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <h1>スタッフ編集 <small>#{{$staff->id}}</small></h1>

      {!! Form::open(['route'=>['admin.staffs.update',$agencyId, $staff->id], 'method'=>'put', 'id'=>'updateForm']) !!}

      <input type="hidden" name="updated_at" value="{{ $staff->updated_at }}"/>

      @if($errors->has('auth_error')) 
      <div class="alert alert-danger" role="alert">{{ $errors->first('auth_error') }}</div>
      @endif

      @if($errors->has('id')) {{ $errors->first('id') }} @endif

      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="account">アカウント</label>
          <input type="text" name='account' id="account" class="form-control @if($errors->has('account')) is-invalid @endif" value="{{ $staff->account }}" placeholder="半角英数" maxlength="32" disabled="disabled">
          <div class="invalid-feedback">
          @if($errors->has('account')) {{ $errors->first('account') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-3">
          <label for="lastName">姓</label>
          <input type="text" name='last_name' id="lastName" class="form-control @if($errors->has('last_name')) is-invalid @endif" value="{{ old('last_name', $staff->last_name) }}" placeholder="姓">
          <div class="invalid-feedback">
          @if($errors->has('last_name')) {{ $errors->first('last_name') }} @endif
          </div>
        </div>
        <div class="form-group">
          <label for="firstName">名</label>
          <input type="text" name='first_name' id="firstName" class="form-control @if($errors->has('first_name')) is-invalid @endif" value="{{ old('first_name', $staff->first_name) }}" placeholder="名">
          <div class="invalid-feedback">
          @if($errors->has('first_name')) {{ $errors->first('first_name') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="email">E-Mail</label>
          <input type="email" name='email' id="email" class="form-control @if($errors->has('email')) is-invalid @endif" value="{{ old('email', $staff->email) }}" placeholder="example@example.com">
          <div class="invalid-feedback">
          @if($errors->has('email')) {{ $errors->first('email') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="password">パスワード</label>
          <div class="input-group">
            <input type="password" name='password' id="password" class="form-control @if($errors->has('password')) is-invalid @endif" value="">            
            <div class="invalid-feedback">
            @if($errors->has('password')) {{ $errors->first('password') }} @endif
            </div>
            <span class="input-group-btn">
              <button type="button" id="showPassword" class="btn btn-outline-secondary">表示</button>
            </span>
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-2">
          <label for="role">種別</label>
          <select class="custom-select @if($errors->has('role')) is-invalid @endif" id="role" name="role_id">
            @foreach($roles as $role)
              <option value="{{$role->id}}" @if(old('role',$staff->role_id)==$role->id) selected @endif>{{$role->name}}</option>
            @endforeach
          </select>
          <div class="invalid-feedback">
            @if($errors->has('role')) {{ $errors->first('role') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-2">
          <label for="status">状態</label>
          <select class="custom-select @if($errors->has('status')) is-invalid @endif" id="status" name="status">
            @foreach($statuses as $key => $val)
              <option value="{{$key}}" @if(old('status',$staff->status)==$key) selected @endif>{{$val}}</option>
            @endforeach
          </select>
          <div class="invalid-feedback">
            @if($errors->has('status')) {{ $errors->first('status') }} @endif
          </div>
        </div>
      </div>

        <div class="form-group row">
          <label for="updatedAt" class="col-sm-2 col-form-label">最終更新日</label>
          <div class="col-sm-2">
            <p>{{ $staff->updated_at->format('Y-m-d H:i') }}</p>
          </div>
        </div>

        <div class="form-group row">
          <label for="createdAt" class="col-sm-2 col-form-label">登録日</label>
          <div class="col-sm-2">
            <p>{{ $staff->created_at->format('Y-m-d H:i') }}</p>
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
        <h5 class="modal-title" id="deleteModalTitle">#{{$staff->id}} スタッフ削除</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        スタッフを削除しますか？<br>この操作は取り消しできません。
      </div>
      <div class="modal-footer">
        {!! Form::open(['route'=>['admin.staffs.destroy',$agencyId, $staff->id], 'method'=>'delete', 'id'=>'deleteForm']) !!}
          <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
          <button type="button" class="btn btn-danger" id="deleteButton">削除する</button>
        {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>


<script src="{{ mix('/admin/js/staffs-edit.js') }}"></script>
@endsection
