<main>
  <div id="reserveInvoiceArea" 
  applicationStep='{{ $applicationStep }}'
  reserveNumber='{{ $reserveNumber }}'
  defaultValue='@json($defaultValue)'
  documentSetting='@json($documentSetting)'
  documentCommonSetting='@json($documentCommonSetting)'
  formSelects='@json($formSelects)' 
  hotelContacts='@json($hotelContacts)' 
  hotelInfo='@json($hotelInfo)' 
  optionPrices='@json($optionPrices)'
  airticketPrices='@json($airticketPrices)' 
  hotelPrices='@json($hotelPrices)'
  consts='@json($consts)'
  jsVars='@json($jsVars)'
  reception='{{ $reception }}'
  ></div>
</main>

<script src="{{ mix('/staff/js/reserve_invoice-create-edit.js') }}"></script>