@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.inflows.create') }}

      <h1>流入サイト編集</h1>

      <p>顧客が登録したサイトとして顧客情報と紐づきます</p>

      {!! Form::open(['route'=>'admin.inflows.store','method'=>'post']) !!}

      @if($errors->has('auth_error')) 
      <div class="alert alert-danger" role="alert">{{ $errors->first('auth_error') }}</div>
      @endif

      <div class="form-row">
        <div class="form-group col-md-4">
          <label for="site_name">サイト名</label>
          <input type="text" name='site_name' id="site_name" class="form-control @if($errors->has('site_name')) is-invalid @endif" value="{{ old('site_name') }}" placeholder="">
          <div class="invalid-feedback">
          @if($errors->has('site_name')) {{ $errors->first('site_name') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-6">
          <label for="url">URL</label>
          <input type="text" name='url' id="url" class="form-control @if($errors->has('url')) is-invalid @endif" value="{{ old('url') }}" placeholder="">
          <div class="invalid-feedback">
          @if($errors->has('url')) {{ $errors->first('url') }} @endif
          </div>
        </div>
      </div>
      
      {!! Form::submit('登録', ['class' => 'btn btn-primary']) !!}
      {!! Form::close() !!}
    </main>

  </div>
</div>
@endsection
