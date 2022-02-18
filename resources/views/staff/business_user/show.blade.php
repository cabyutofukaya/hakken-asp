@extends('layouts.staff.app')

@section('content')

@can('view', $businessUser)
  <main id="businessUserShowArea" 
    defaultTab='{{ $defaultTab }}'
    tabCodes='@json($tabCodes)'
    businessUser='@json($businessUser)'
    permission='@json($permission)'
    defaultValue='@json($defaultValue)'
    formSelects='@json($formSelects)'
    customFields='@json($customFields)'
    consts='@json($consts)'
    jsVars='@json($jsVars)'
    ></main>

  <script src="{{ mix('/staff/js/business_user-show.js') }}"></script>
@endcan

@endsection
