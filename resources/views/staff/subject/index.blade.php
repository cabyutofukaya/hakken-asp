@extends('layouts.staff.app')

@section('content')
<main 
  id="subjectIndexArea" 
  agencyAccount="{{$agencyAccount}}" 
  customCategoryCode="{{$customCategoryCode}}"
  defaultTab="{{ $defaultTab }}"
  createLinks='@json($createLinks)'
  formSelects='@json($formSelects)'
  customItemTypes='@json($customItemTypes)'
  subjectCategoryCodes='@json($subjectCategoryCodes)'
  consts='@json($consts)'
  jsVars='@json($jsVars)'
  permission='@json($permission)'
  successMessage='{{session('success_message')}}' {{-- 登録・更新ページからの遷移 --}}
  ></main>

<script src="{{ mix('/staff/js/subject_category-index.js') }}"></script>
@endsection
