@extends('layouts.staff.app')

@section('content')
<h1>カスタム項目追加</h1>

{{ Form::open(['route' => ['staff.system.custom_items.store', [$agencyAccount, $category]], 'method' => 'post']) }}

<input type="hidden" name="agency_id" value="{{ $agencyId }}"/> 
<input type="hidden" name="type" value="{{ $type }}"/> 

名前: <input type="text" name="name" value="{{ old('name') }}" />
<input type="hidden" name="flg" value="0"/><!-- ⇦ flgがoffの場合でも値を送るための隠しフォーム -->
<input type="checkbox" id="flg" name="flg" value="1"@if(old('flg', 1)==1)checked @endif>
<label for="flg">有効</label>
<input type="submit" value="登録" />

@if($errors->has('category')) {{ $errors->first('category') }} @endif
@if($errors->has('type')) {{ $errors->first('type') }} @endif
@if($errors->has('name')) {{ $errors->first('name') }} @endif
@if($errors->has('flg')) {{ $errors->first('flg') }} @endif
{{ Form::close() }}

@endsection
