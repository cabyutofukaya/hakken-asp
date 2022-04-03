@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">event_note</span>{{ $participant->name }} キャンセルチャージ設定</h1>
    @include('staff.reserve._breadcrumb', [
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
    participant='@json($participant)'
  ></div>
    
</main>

<script src="{{ mix('/staff/js/web-participant_cancel_charge-create.js') }}"></script>
@endsection
