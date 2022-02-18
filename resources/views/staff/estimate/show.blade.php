@extends('layouts.staff.app')

@section('content')

@can('view', $reserve)
  <main id="estimateShowArea"
    defaultTab='{{ $defaultTab }}'
    reserve='@json($reserve)'
    defaultValue='@json($defaultValue)'
    targetConsultationNumber='{{ $targetConsultationNumber }}'
    formSelects='@json($formSelects)'
    customFields='@json($customFields)'
    consts='@json($consts)'
    permission='@json($permission)'
    flashMessage='@json($flashMessage)'
    jsVars='@json($jsVars)'
  ></main>
  <script src="{{ mix('/staff/js/estimate-show.js') }}"></script>
@endcan

@endsection
