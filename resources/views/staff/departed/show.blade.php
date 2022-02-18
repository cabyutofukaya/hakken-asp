@extends('layouts.staff.app')

@section('content')
@can('view', $reserve)
  <main id="reserveShowArea"
  defaultTab='{{ $defaultTab }}'
  tabCodes='@json($tabCodes)'
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
@endcan

@endsection