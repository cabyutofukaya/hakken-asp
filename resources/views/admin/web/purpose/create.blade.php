@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <h1>目的登録</h1>
      {!! Form::open(['route'=>'admin.hakken.purposes.store']) !!}

      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="name">目的</label>
          <input type="name" name='name' id="name" class="form-control @if($errors->has('name')) is-invalid @endif" value="{{ old('name') }}">
          <div class="invalid-feedback">
          @if($errors->has('name')) {{ $errors->first('name') }} @endif
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-md-5">
          <label for="seq">順番</label>
          <input type="number" name='seq' id="seq" class="form-control @if($errors->has('seq')) is-invalid @endif" value="{{ old('seq') }}">
          <div class="invalid-feedback">
          @if($errors->has('seq')) {{ $errors->first('seq') }} @endif
          </div>
        </div>
      </div>

        {!! Form::submit('登録', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </main>

  </div>
</div>

@endsection
