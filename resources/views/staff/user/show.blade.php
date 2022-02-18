@extends('layouts.staff.app')

@section('content')

@can('view', $user)
  <main id="userShowArea"
    defaultTab='{{ $defaultTab }}'
    customCategoryCode='{{ $customCategoryCode }}'
    tabCodes='@json($tabCodes)'
    user='@json($user)'
    permission='@json($permission)'
    defaultValue='@json($defaultValue)'
    formSelects='@json($formSelects)'
    customFields='@json($customFields)'
    consts='@json($consts)'
    jsVars='@json($jsVars)'
  ></main>

  <script src="{{ mix('/staff/js/user-show.js') }}"></script>
@endcan

@endsection
