@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">event_note</span>行程編集 {{ $reserveItinerary->control_number }}</h1>
    <p class="timeStump">
    作成日 {{ $reserveItinerary->created_at->format('Y/m/d') }}<br>
    更新日時 {{ $reserveItinerary->updated_at->format('Y/m/d H:i') }}
    </p>
    @include('staff.web.reserve_itinerary.common._breadcrumb', [
      'reserve' => $reserve,
      'agencyAccount' => $agencyAccount,
      'backUrl' => $consts['backUrl'],
      'current' => '行程編集'
    ])
  </div>

  @include('staff.common.error_message')

  {{-- 旅行詳細 --}}
  <h2 class="subTit"><span class="material-icons"> subject </span>
    @if($reserve->application_step == config('consts.reserves.APPLICATION_STEP_DRAFT'))
      見積情報
    @elseif($reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE'))
      予約情報
    @endif
  </h2>
  @include('staff.web.reserve_itinerary.common._reserve_info', ['reserve' => $reserve])
  
    <div 
      id="itineraryArea" 
      editMode="edit"
      isTravelDates='{{ $isTravelDates }}'
      reception='{{ $reception }}'
      applicationStep='{{ $reserve->application_step }}'
      isCanceled='{{ $isCanceled }}'
      isEnabled='{{ $isEnabled }}'
      applicationStepList='@json($consts['application_step_list'])'
      estimateNumber='{{ $reserve->estimate_number }}'
      reserveNumber='{{ $reserve->control_number }}'
      defaultValue='@json($defaultValue)' 
      formSelects='@json($formSelects)'
      consts='@json($consts)'
      customFields='@json($customFields)'
      subjectCustomCategoryCode='{{ $subjectCustomCategoryCode }}'
      participants='@json($participants)'
      modalInitialValues='@json($modalInitialValues)'
      jsVars='@json($jsVars)'
      ></div>
  </main>

{{-- ASP用のjsと共通 --}}
<script src="{{ mix('/staff/js/reserve_itinerary-create-edit.js') }}"></script>
@endsection
