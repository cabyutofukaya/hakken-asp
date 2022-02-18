@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">event_note</span>行程作成</h1>
    <p class="timeStump">
    作成日 {{ $reserve->created_at->format('Y/m/d') }}<br>
    更新日時 {{ $reserve->updated_at->format('Y/m/d H:i') }}
    </p>
    <ol class="breadCrumbs">
      <li>
        @if($reserve->application_step == config('consts.reserves.APPLICATION_STEP_DRAFT'))
          <a href="{{ route('staff.web.estimates.normal.index', $agencyAccount) }}">WEB見積管理</a>
        @elseif($reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE'))
          <a href="{{ route('staff.web.estimates.reserve.index', $agencyAccount) }}">WEB予約管理</a>
        @endif
      </li>
      <li>
        <a href="{{ $backUrl }}">
          @if($reserve->application_step == config('consts.reserves.APPLICATION_STEP_DRAFT'))
            見積情報 {{ $reserve->estimate_number }}
          @elseif($reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE'))
            予約情報 {{ $reserve->control_number }}
          @endif
        </a>
      </li>
      <li>
        <span>行程作成</span>
      </li>
    </ol>
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

  <form method="post" action="{{ $storeUrl }}" enctype="multipart/form-data">
    @csrf
    @if(Arr::get($defaultValue, 'dates'))
      <div 
        id="itineraryArea" 
        reception='{{ $reception }}'
        applicationStep='{{ $reserve->application_step }}'
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
    @endif
      

    @include('staff.web.reserve_itinerary.common._under_button', [
      'applicationStep' => $reserve->application_step,
      'isEnabled' => true,
      'backUrl' => $backUrl,
      'mode' => 'create'
    ])

  </form>
</main>

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
{{-- ASP用のjsと共通 --}}
<script src="{{ mix('/staff/js/reserve_itinerary-create-edit.js') }}"></script>
@endsection
