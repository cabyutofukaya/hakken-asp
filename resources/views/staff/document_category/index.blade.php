@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1>
      <span class="material-icons">description</span>帳票設定
    </h1>
  </div>
  
  @include("staff.common.success_message")
  @include('staff.common.decline_message')
  @include('staff.common.error_message')

  <div id="documentList" 
    agencyAccount='{{$agencyAccount}}' 
    documentCategories='@json($documentCategories)' 
    currentTab='{{$currentTab}}' 
    consts='@json($consts)'
    permission='@json($permission)'
    >
  </div>
</main>

<script src="{{ mix('/staff/js/document_category-index.js') }}"></script>
@endsection
