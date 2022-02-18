@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">get_app</span>請求管理</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.management.invoice.index', [$agencyAccount]) }}">請求管理</a></li>
      <li><span>{{ $reserveBundleInvoice->business_user->name ?? '-' }}一括請求内訳</span></li>
    </ol>
  </div>

  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')

  <div id="breakdownList" 
    formSelects='@json($formSelects)' 
    modalFormSelects='@json($modalFormSelects)'
    consts='@json($consts)' 
    customFields='@json($customFields)' 
    customCategoryCode='{{ $customCategoryCode }}'
    reserveBundleInvoiceId='{{ $reserveBundleInvoiceId }}'
    jsVars='@json($jsVars)'
  ></div>
</main>

<script src="{{ mix('/staff/js/management_invoice-breakdown.js') }}"></script>
@endsection
