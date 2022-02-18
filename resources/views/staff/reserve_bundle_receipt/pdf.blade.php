@extends('layouts.staff.pdf')

@section('content')
<div class="documentPrint">
  <h2 class="blockTitle">{{ Arr::get($value, 'document_setting.title') }}</h2>
  <div class="number">
    <p>領収書番号：{{ Arr::get($value, 'user_receipt_number') }}</p>
    <p>発行日：{{ Arr::get($value, 'issue_date') }}</p>
  </div>
  <div class="dcHead">
    <div>
      <p class="dispReceiptName">
        <span>
          @if(Arr::get($value, 'document_address.type') === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {{-- 法人申込 --}}
            @include('staff.common.receipt._business_superscription')
          @endif
        </span>
      </p>
    </div>
    @include('staff.common.receipt._own_company')
  </div>
  <div class="dispReceiptBox">
    <div class="dispRevenue">収入<br>印紙</div>
    <p class="dispReceipt">
    ￥{{ number_format(Arr::get($value, 'receipt_amount', 0)) }}
    </p>
    <p class="dispReceiptTxt">{!! nl2br(e(Arr::get($value, 'document_setting.proviso'))) !!}</p>
    </div>
    <p class="dispEtc mb20">{!! nl2br(e(Arr::get($value, 'document_setting.note'))) !!}</p>
</div>
@endsection