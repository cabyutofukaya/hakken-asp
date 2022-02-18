@extends('layouts.staff.app')

@section('content')
<main id="reserveShowArea"
  defaultTab='{{ $defaultTab }}'
  targetConsultationNumber='{{ $targetConsultationNumber }}'
  reserve='@json($reserve)'
  defaultValue='@json($defaultValue)'
  formSelects='@json($formSelects)'
  customFields='@json($customFields)'
  consts='@json($consts)'
  permission='@json($permission)'
  flashMessage='@json($flashMessage)'
  jsVars='@json($jsVars)'
></main>

<script src="{{ mix('/staff/js/reserve-show.js') }}"></script>
@endsection
