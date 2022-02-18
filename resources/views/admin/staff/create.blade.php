@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <h1>スタッフ登録</h1>
      {!! Form::open(['route'=>['admin.staffs.store', $agencyId],'method'=>'post']) !!}

      @if($errors->has('auth_error')) 
      <div class="alert alert-danger" role="alert">{{ $errors->first('auth_error') }}</div>
      @endif

      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="account">アカウント</label>
          <input type="text" name='account' id="account" class="form-control @if($errors->has('account')) is-invalid @endif" value="{{ old('account') }}" placeholder="半角英数" maxlength="32">
          <div class="invalid-feedback">
          @if($errors->has('account')) {{ $errors->first('account') }} @endif
          </div>
          <small id="emailHelp" class="form-text text-muted">※登録後の変更不可</small>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-3">
          <label for="lastName">姓</label>
          <input type="text" name='last_name' id="lastName" class="form-control @if($errors->has('last_name')) is-invalid @endif" value="{{ old('last_name') }}" placeholder="姓">
          <div class="invalid-feedback">
          @if($errors->has('last_name')) {{ $errors->first('last_name') }} @endif
          </div>
        </div>
        <div class="form-group">
          <label for="firstName">名</label>
          <input type="text" name='first_name' id="firstName" class="form-control @if($errors->has('first_name')) is-invalid @endif" value="{{ old('first_name') }}" placeholder="名">
          <div class="invalid-feedback">
          @if($errors->has('first_name')) {{ $errors->first('first_name') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="email">E-Mail</label>
          <input type="email" name='email' id="email" class="form-control @if($errors->has('email')) is-invalid @endif" value="{{ old('email') }}" placeholder="example@example.com">
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
              <option value="{{$role->id}}" @if(old('role')==$role->id) selected @endif>{{$role->name}}</option>
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
              <option value="{{$key}}" @if(old('status')==$key) selected @endif>{{$val}}</option>
            @endforeach
          </select>
          <div class="invalid-feedback">
            @if($errors->has('status')) {{ $errors->first('status') }} @endif
          </div>
        </div>
      </div>
      
        {!! Form::submit('登録', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </main>

  </div>
</div>

<script src="{{ mix('/admin/js/staff-create.js') }}"></script>
@endsection
