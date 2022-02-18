@extends('layouts.admin.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">person_add</span>新規顧客追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('admin.agencies.index') }}">顧客管理</a></li>
      <li><span>新規顧客追加</span></li>
    </ol>
  </div>

  <form method="POST" action="{{ route('admin.agencies.store') }}" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="number_staff_allowed" value="{{ config('consts.const.NUMBER_STAFF_ALLOWED_DEFAULT') }}"/><!-- スタッフ登録許可数 -->

      <div 
        id="agencyCreate" 
        errors='@json($errors->toArray())' 
        defaultValue='@json(session()->getOldInput())' 
        formSelects='@json($formSelects)'
        consts='@json($consts)'
        ></div>
  </form>
</main>
<script src="{{ mix('/admin/js/agencies-create.js') }}"></script>
@endsection