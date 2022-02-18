@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.users.create') }}

      <h1>顧客登録（個人）</h1>
      {!! Form::open(['route'=>'admin.users.store']) !!}

      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="agencyId">従属会社</label>
          <select name="agency_id" id="agencyId" class="custom-select @if($errors->has('agency_id')) is-invalid @endif">
            @if($agency)<option value="{{$agency->id}}" selected="selected">#{{$agency->id}} - {{$agency->company_name}}</option>@else
            <option value="">選択してください</option>
            @endif
          </select>
          <div class="invalid-feedback">
            @if($errors->has('agency_id')) {{ $errors->first('agency_id') }} @endif
          </div>
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
          <label for="password">パスワード</label>
          <input type="password" name='password' id="password" class="form-control @if($errors->has('password')) is-invalid @endif" value="">
          <div class="invalid-feedback">
          @if($errors->has('password')) {{ $errors->first('password') }} @endif
          </div>
          <small id="emailHelp" class="form-text text-muted">※変更しない場合は未入力のまま送信</small>
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
        <div class="form-group col-md-2">
          <label for="inflows_id">流入サイト</label>
          <select class="custom-select @if($errors->has('inflows_id')) is-invalid @endif" id="inflows_id" name="inflows_id">
            @foreach($inflows as $key => $val)
              <option value="{{$key}}" @if(old('inflows_id')==$key) selected @endif>{{$val}}</option>
            @endforeach
          </select>
          <div class="invalid-feedback">
            @if($errors->has('inflows_id')) {{ $errors->first('inflows_id') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-2">
          <label for="status">状態</label>
          <select class="custom-select @if($errors->has('status')) is-invalid @endif" id="status" name="status">
            @foreach($status as $key => $val)
              <option value="{{$key}}" @if(old('status')==$key) selected @endif>{{$val}}</option>
            @endforeach
          </select>
          <div class="invalid-feedback">
            @if($errors->has('status')) {{ $errors->first('status') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-8">
          <label for="note">備考</label>
          <textarea name="note" id="note" class="form-control @if($errors->has('note')) is-invalid @endif"" rows="3">{{ old("note") }}</textarea>
          <div class="invalid-feedback">
            @if($errors->has('note')) {{ $errors->first('note') }} @endif
          </div>
        </div>
      </div>

        {!! Form::submit('登録', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </main>

  </div>
</div>

<script src="{{ mix('/admin/js/user-create.js') }}"></script>
@endsection
