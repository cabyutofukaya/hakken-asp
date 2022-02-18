@extends('layouts.staff.app')

@section('content')
<main>
  <div id="bundleInvoiceArea"
    reserveBundleInvoiceId='{{ $reserveBundleInvoiceId }}'
    defaultValue='@json($defaultValue)'
    documentSetting='@json($documentSetting)'
    documentCommonSetting='@json($documentCommonSetting)'
    formSelects='@json($formSelects)' 
    reservePrices='@json($reservePrices)'
    consts='@json($consts)'
    jsVars='@json($jsVars)'
  ></div>

</main>

<script src="{{ mix('/staff/js/reserve_bundle_invoice-edit.js') }}"></script>
@endsection
