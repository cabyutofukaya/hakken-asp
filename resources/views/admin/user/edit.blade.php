@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.users.edit', $user) }}

      <h1>顧客編集（個人）</h1>

      {!! Form::open(['route'=>['admin.users.update',$user->id], 'method'=>'put', 'id'=>'updateForm']) !!}

      <input type="hidden" name="updated_at" value="{{ $user->updated_at }}"/>

        <div class="form-row">
          <div class="form-group col-md-1">
            <label for="ID">ID</label>
            <input type="text" name='ID' id="ID" class="form-control" value="{{ $user->id }}" disabled>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-4">
            <label for="agency_name">従属会社</label>
            <input type="text" name='agency_name' id="agency_name" class="form-control" value="{{ $user->agency->company_name }}" disabled>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-4">
            <label for="user_number">お客様番号</label>
            <input type="text" name='user_number' id="user_number" class="form-control @if($errors->has('user_number')) is-invalid @endif" value="{{ old('user_number', $user->user_number) }}" disabled>
            <div class="invalid-feedback">
            @if($errors->has('user_number')) {{ $errors->first('user_number') }} @endif
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-3">
            <label for="lastName">姓</label>
            <input type="text" name='last_name' id="lastName" class="form-control @if($errors->has('last_name')) is-invalid @endif" value="{{ old('last_name', $user->last_name) }}" placeholder="姓">
            <div class="invalid-feedback">
            @if($errors->has('last_name')) {{ $errors->first('last_name') }} @endif
            </div>
          </div>
          <div class="form-group col-md-3">
            <label for="firstName">名</label>
            <input type="text" name='first_name' id="firstName" class="form-control @if($errors->has('first_name')) is-invalid @endif" value="{{ old('first_name', $user->first_name) }}" placeholder="名">
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
            <input type="email" name='email' id="email" class="form-control @if($errors->has('email')) is-invalid @endif" value="{{ old('email', $user->email) }}" placeholder="example@example.com">
            <div class="invalid-feedback">
            @if($errors->has('email')) {{ $errors->first('email') }} @endif
            </div>
          </div>
        </div>

        @foreach($user->agency->user_custom_items as $userCustomItem)
          @if($userCustomItem->flg)
            <div class="form-row">
              <div class="form-group col-md-4">
                <label for="{{ $userCustomItem->id }}">{{ $userCustomItem->name }}</label>
                @if($userCustomItem->type === 'text')
                  <input type="text" name='{{ $userCustomItem->id }}' id="{{ $userCustomItem->id }}" value="{{ old($userCustomItem->id, optional($user->user_custom_values->firstWhere('user_custom_item_id', $userCustomItem->id))->val) }}" class="form-control"/>
                @elseif($userCustomItem->type === 'select')
                  <select class="custom-select" id="{{ $userCustomItem->id }}" name="{{ $userCustomItem->id }}">
                    @foreach($userCustomItem->list as $val)
                      <option value="{{$val}}" @if(old($userCustomItem->id, optional($user->user_custom_values->firstWhere('user_custom_item_id', $userCustomItem->id))->val) == $val) selected @endif>{{ $val }}</option>
                    @endforeach
                  </select>
                @endif
              </div>
            </div>
          @endif
        @endforeach

        <div class="form-row">
          <div class="form-group col-md-2">
            <label for="inflow_id">流入サイト</label>
            <select class="custom-select @if($errors->has('inflow_id')) is-invalid @endif" id="inflow_id" name="inflow_id">
              @foreach($inflows as $key => $val)
                <option value="{{$key}}" @if(old('inflow_id', $user->inflow_id)==$key) selected @endif>{{$val}}</option>
              @endforeach
            </select>
            <div class="invalid-feedback">
              @if($errors->has('inflow_id')) {{ $errors->first('inflow_id') }} @endif
            </div>
          </div>
        </div>  

        <div class="form-row">
          <div class="form-group col-md-2">
            <label for="status">状態</label>
            <select class="custom-select @if($errors->has('status')) is-invalid @endif" id="status" name="status">
              @foreach($status as $key => $val)
                <option value="{{$key}}" @if(old('status', $user->status) == $key) selected @endif>{{$val}}</option>
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
            <textarea name="note" id="note" class="form-control @if($errors->has('note')) is-invalid @endif"" rows="3">{{ old("note",$user->note) }}</textarea>
            <div class="invalid-feedback">
              @if($errors->has('note')) {{ $errors->first('note') }} @endif
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label for="updatedAt" class="col-sm-2 col-form-label">最終更新日</label>
          <div class="col-sm-2">
            <p>{{ $user->updated_at->format('Y-m-d H:i') }}</p>
          </div>
        </div>

        <div class="form-group row">
          <label for="createdAt" class="col-sm-2 col-form-label">登録日</label>
          <div class="col-sm-2">
            <p>{{ $user->created_at->format('Y-m-d H:i') }}</p>
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
        <h5 class="modal-title" id="deleteModalTitle">#{{$user->id}} ユーザー削除</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ユーザーを削除しますか？<br>この操作は取り消しできません。
      </div>
      <div class="modal-footer">
        {!! Form::open(['route'=>['admin.users.destroy',$user->id], 'method'=>'delete', 'id'=>'deleteForm']) !!}
          <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
          <button type="button" class="btn btn-danger" id="deleteButton">削除する</button>
        {!! Form::close() !!}
      </div>
    </div>
  </div>
</div>


<script src="{{ mix('/admin/js/users-edit.js') }}"></script>
@endsection
