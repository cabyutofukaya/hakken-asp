@extends('layouts.admin.app')

@section('content')
<main>
  <form method="POST" action="{{ route('admin.agencies.update', $agency->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

      <input type="hidden" name="number_staff_allowed" value="{{ $agency->number_staff_allowed }}"/><!-- スタッフ登録許可数 -->
      <input type="hidden" name="status" value="{{ $agency->status }}" /><!-- 状態 -->
      <input type="hidden" name="updated_at" value="{{ $agency->updated_at }}" />

      <div 
        id="agencyEdit" 
        errors='@json($errors->toArray())' 
        defaultValue='@json($defaultValue)' 
        formSelects='@json($formSelects)'
        consts='@json($consts)'
      ></div>

    </form>
</main>

<script src="{{ mix('/admin/js/agencies-edit.js') }}"></script>
@endsection