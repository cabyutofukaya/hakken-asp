@extends('layouts.staff.app')

@section('content')
<main id="reserveShowArea"
  consts='@json($consts)'
  customFields='@json($customFields)'
  defaultTab='{{ $defaultTab }}'
  defaultValue='@json($defaultValue)'
  flashMessage='@json($flashMessage)'
  formSelects='@json($formSelects)'
  jsVars='@json($jsVars)'
  permission='@json($permission)'
  reserve='@json($reserve)'
  roomKey='{{ $roomKey }}'
  targetConsultationNumber='{{ $targetConsultationNumber }}'
></main>
<script src="{{ mix('/staff/js/web-reserve-show.js') }}"></script>
@endsection
