<main>
  <div id="reserveConfirmArea"
  reception='{{ $reception }}'
  isDeparted='{{ $isDeparted }}'
  isCanceled='{{ $isCanceled }}'
  applicationStep='{{ $applicationStep }}'
  reserveNumber='{{ $reserveNumber }}'
  estimateNumber='{{ $estimateNumber }}'
  itineraryNumber='{{ $itineraryNumber }}'
  defaultValue='@json($defaultValue)'
  documentCommonSetting='@json($documentCommonSetting)'
  documentSetting='@json($documentSetting)'
  formSelects='@json($formSelects)' 
  hotelContacts='@json($hotelContacts)' 
  hotelInfo='@json($hotelInfo)' 
  optionPrices='@json($optionPrices)'
  airticketPrices='@json($airticketPrices)' 
  hotelPrices='@json($hotelPrices)'
  consts='@json($consts)'
  jsVars='@json($jsVars)'
></div>
</main>

<script src="{{ mix('/staff/js/reserve_confirm-create-edit.js') }}"></script>