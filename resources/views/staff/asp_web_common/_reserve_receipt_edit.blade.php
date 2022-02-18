<main>
  <div id="reserveReceiptArea"
  reserveNumber='{{ $reserveNumber }}'
  maximumAmount='{{ $maximumAmount }}'
  defaultValue='@json($defaultValue)'
  documentSetting='@json($documentSetting)'
  documentCommonSetting='@json($documentCommonSetting)'
  formSelects='@json($formSelects)' 
  consts='@json($consts)'
  jsVars='@json($jsVars)'
  reception='{{ $reception }}'
  ></div>
</main>

{{-- ASP用のjsと共通 --}}
<script src="{{ mix('/staff/js/reserve_receipt-create-edit.js') }}"></script>