@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">event_note</span>キャンセルチャージ設定</h1>
    @include('staff.web.reserve._breadcrumb', [
      'reserve' => $reserve,
      'agencyAccount' => $agencyAccount,
      'reserveUrl' => $consts['reserveUrl'],
      'current' => 'キャンセルチャージ設定'
    ])
  </div>

  @include('staff.common.error_message')

  <div id="cancelChargeArea" 
    consts='@json($consts)' 
    jsVars='@json($jsVars)'
    defaultValue='@json($defaultValue)'
    errors='@json($errors->toArray())'
  ></div>
    
</main>

<script src="{{ mix('/staff/js/web-reserve_cancel_charge-create.js') }}"></script>
@endsection