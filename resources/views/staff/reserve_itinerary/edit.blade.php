@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">event_note</span>行程編集 {{ $reserveItinerary->control_number }}</h1>
    <p class="timeStump">
    作成日 {{ $reserveItinerary->created_at->format('Y/m/d') }}<br>
    更新日時 {{ $reserveItinerary->updated_at->format('Y/m/d H:i') }}
    </p>
    @include('staff.reserve_itinerary.common._breadcrumb', [
      'reserve' => $reserve,
      'agencyAccount' => $agencyAccount,
      'backUrl' => $backUrl,
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
  @include('staff.reserve_itinerary.common._reserve_info', ['reserve' => $reserve])
  
  <form method="post" action="{{ $updateUrl }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    @if($isTravelDates)
    <div 
      id="itineraryArea" 
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
    @else
      <div>旅行日が設定されていません（出発日、帰着日）。</div>
      <input type="hidden" name="updated_at" value="{{ $defaultValue['updated_at'] }}"/>
    @endif
    
    @include('staff.reserve_itinerary.common._under_button', [
      'applicationStep' => $reserve->application_step,
      'isTravelDates' => $isTravelDates,
      'isEnabled' => $reserveItinerary->enabled,
      'backUrl' => $backUrl,
      'mode' => 'edit'
    ])

    </form>
  </main>

<script src="{{ mix('/staff/js/reserve_itinerary-create-edit.js') }}"></script>
<script>
  // reserve_itinerary登録、編集ページ共通スクリプト
  $(() => {
      // 仕入科目を更新or登録した際に、どうしてもformがsubmitされてしまうのでform送信ボタンを押した時のみformが送信されるようにjqueryで制御
      $("form").on("submit", function(e) {
          e.preventDefault();
      });
      $("#formControl .blueBtn").on("click", function() {
          $("form").off("submit");
          $("form").trigger("submit");
      });
  });
</script>
@endsection
