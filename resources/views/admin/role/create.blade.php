@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.roles.create') }}

      <h1>権限追加</h1>

      {!! Form::open(['route'=>'admin.roles.store','method'=>'post']) !!}

      @if($errors->has('auth_error')) 
      <div class="alert alert-danger" role="alert">{{ $errors->first('auth_error') }}</div>
      @endif

      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="name">権限名称</label>
          <input type="text" name='name' id="name" class="form-control @if($errors->has('name')) is-invalid @endif" value="{{ old('name') }}" placeholder="">
          <div class="invalid-feedback">
          @if($errors->has('name')) {{ $errors->first('name') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="name_en">権限名称（英語）</label>
          <input type="text" name='name_en' id="name_en" class="form-control @if($errors->has('name_en')) is-invalid @endif" value="{{ old('name_en') }}" placeholder="">
          <div class="invalid-feedback">
          @if($errors->has('name_en')) {{ $errors->first('name_en') }} @endif
          </div>
        </div>
      </div>
      
      @foreach($roleItems as $roleItem)
      <h4>{{ $roleItem['label'] }}</h4>
        <ul>
          @foreach($roleItem['items'] as $item)
            <li><input type="checkbox" id="{{ $roleItem['target'] }}_{{ $item['action'] }}" name="authority[{{ $roleItem['target'] }}][]" value="{{ $item['action'] }}" @if(in_array($item['action'], old("authority.{$roleItem['target']}", [config("consts.roles.READ")]))) checked @endif><label for="{{ $roleItem['target'] }}_{{ $item['action'] }}">{{ $item['label'] }}</label></li>
          @endforeach
        </ul>
      @endforeach

      {!! Form::submit('登録', ['class' => 'btn btn-primary']) !!}
      {!! Form::close() !!}
    </main>

  </div>
</div>
@endsection
