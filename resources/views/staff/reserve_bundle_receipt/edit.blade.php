@extends('layouts.staff.app')

@section('content')
<main>
  <div id="reserveReceiptArea"
  maximumAmount='{{ $maximumAmount }}'
  bundleId='{{ $bundleId }}'
  defaultValue='@json($defaultValue)'
  documentSetting='@json($documentSetting)'
  documentCommonSetting='@json($documentCommonSetting)'
  formSelects='@json($formSelects)' 
  consts='@json($consts)'
  jsVars='@json($jsVars)'
  ></div>
</main>

<script src="{{ mix('/staff/js/reserve_bundle_receipt-create-edit.js') }}"></script>
@endsection
