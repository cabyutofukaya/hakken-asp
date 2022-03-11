@extends('layouts.staff.pdf')

@section('content')
<div class="documentPrint">
  <h2 class="blockTitle">
    @if(Arr::get($value, 'document_setting.title'))
      {{ Arr::get($value, 'document_setting.title') }}
    @endif
  </h2>
  <div class="number">
    <p>請求番号：{{ Arr::get($value, 'user_bundle_invoice_number') }}</p>
    <p>発行日：{{ Arr::get($value, 'issue_date') }}</p>
  </div>
  <div class="dcHead">
    <div>
      @if(check_business_form_pdf_item($value, "宛名", 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK'))){{-- 宛名エリア --}}
        @if(Arr::get($value, 'document_address.type') === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS'))
          <div class="dispSign">
            <p class="dispPostal">
              @if(check_business_form_pdf_item($value, "郵便番号", 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS')) && Arr::get($value, 'document_address.zip_code'))
                {{ sprintf("〒%s-%s", substr($value['document_address']['zip_code'], 0, 3), substr($value['document_address']['zip_code'], 3)) }}
              @endif
            </p>  
            <p class="dispAddress">
              @if(check_business_form_pdf_item($value, "都道府県", 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS')))
                {{ Arr::get($value, 'document_address.prefecture') }}
              @endif
              @if(check_business_form_pdf_item($value, "住所1", 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS')))
                {{ Arr::get($value, 'document_address.address1') }}
              @endif
              @if(check_business_form_pdf_item($value, "住所2", 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS')))
                {{ Arr::get($value, 'document_address.address2') }}
              @endif
            </p>
          </div>
          <p className="dispName">
            @if(check_business_form_pdf_item($value, "法人名", 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS')) && Arr::get($value, 'document_address.company_name'))
              {{ Arr::get($value, 'document_address.company_name') }}{{ Arr::get($formSelects['honorifics'], Arr::get($value, 'document_address.honorific')) }}{{-- 敬称 --}}
            @endif
          </p>
        @endif
      @endif

      @if(check_business_form_pdf_item($value, "予約情報(件名・期間・担当者)", 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK'))){{-- 予約情報(件名・期間・担当者) --}}

        @if(check_business_form_pdf_item($value, "件名", 'document_setting.setting.'.config('consts.document_request_alls.RESERVATION_INFO')) && Arr::get($value, 'name'))
          <p class="dispTitle">
            件名 {{ Arr::get($value, 'name') }}
          </p>
        @endif

        @if(check_business_form_pdf_item($value, "期間", 'document_setting.setting.'.config('consts.document_request_alls.RESERVATION_INFO')) && (Arr::get($value, 'period_from') || Arr::get($value, 'period_to')))
          <p class="dispPeriod">
            期間 {{ Arr::get($value, 'period_from') }}
              @if(Arr::get($value, 'period_from') && Arr::get($value, 'period_to'))〜@endif
            {{ Arr::get($value, 'period_to') }}分
          </p>
        @endif

        <p class="dispParticipant">
          @if(check_business_form_pdf_item($value, "御社担当", 'document_setting.setting.'.config('consts.document_request_alls.RESERVATION_INFO')) && Arr::get($formSelects, "partnerManagers"))
  
            御社担当 @foreach($formSelects['partnerManagers'] as $partnerManager)
              {{ $partnerManager['org_name'] }}
              @if(check_business_form_pdf_item($value, "御社担当_御社担当(敬称)", 'document_setting.setting.'.config('consts.document_request_alls.RESERVATION_INFO')))
                様
              @endif {{-- 御社担当敬称 --}}
  
              @if(!$loop->last) / @endif
  
            @endforeach
          @endif {{-- 御社担当 --}}
        </p>

      @endif
    </div>

  <div class="dispCorp">
    @if(check_business_form_pdf_item($value, "自社情報", 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'))){{-- 自社情報エリア --}}
      @if(check_business_form_pdf_item($value, "自社名", 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO')))
        <p class="dispCompany">
          {{ Arr::get($value, 'document_common_setting.company_name') }}
        </p>
      @endif
      @if(check_business_form_pdf_item($value, "補足情報1", 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO')))
        <p class="dispEtc01">
          {{ Arr::get($value, 'document_common_setting.supplement1') }}
        </p>
      @endif
      @if(check_business_form_pdf_item($value, "補足情報2", 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO')))
        <p class="dispEtc02">
          {{ Arr::get($value, 'document_common_setting.supplement2') }}
        </p>
        @endif
        @if(check_business_form_pdf_item($value, "郵便番号", 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO')) && Arr::get($value, 'document_common_setting.zip_code'))
          <p class="dispPostal">
            {{ sprintf("〒%s-%s", substr($value['document_common_setting']['zip_code'], 0, 3), substr($value['document_common_setting']['zip_code'], 3)) }}
          </p>
        @endif  
        <p class="dispCorpAddress">
          @if(check_business_form_pdf_item($value, "住所1", 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO')))
            {{ Arr::get($value, 'document_common_setting.address1') }}
          @endif
          <br>
          @if(check_business_form_pdf_item($value, "住所2", 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO')))
            {{ Arr::get($value, 'document_common_setting.address2') }}
          @endif
        </p>
        <p class="dispCorpContact">
          @if(check_business_form_pdf_item($value, "TEL", 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO')) && Arr::get($value, 'document_common_setting.tel'))
            {{ sprintf("TEL:%s", $value['document_common_setting']['tel']) }}
          @endif
          @if(check_business_form_pdf_item($value, "FAX", 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO')) && Arr::get($value, 'document_common_setting.fax'))
            {{ sprintf(" / FAX:%s ", $value['document_common_setting']['fax']) }}
          @endif
        </p>
        @if(check_business_form_pdf_item($value, "担当者", 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO')) && Arr::get($value, 'manager'))
          <p class="dispManager">担当 {{ $value['manager'] }}</p>
        @endif  
    @endif {{-- 自社情報エリア ここまで --}}

    @if(check_business_form_pdf_item($value, "検印欄", 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK'))){{-- 検印欄エリア --}}
      <div class="dispStump">
        <ul>
          @for($i=0; $i<Arr::get($value, 'document_setting.seal_number', 0); $i++)
            <li><span>{{ Arr::get($value, 'document_setting.seal_items.' . $i) }}</span></li>
          @endfor
        </ul>
        <p>{{ Arr::get($value, 'document_setting.seal_wording') }}</p>
      </div>
    @endif {{-- 検印欄エリア ここまで --}}

  </div>
</div>

@if(check_business_form_pdf_item($value, "案内文", 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK')) && Arr::get($value, 'document_setting.information')){{-- 案内文 --}}
  <p class="dispAnounce">{!! nl2br(e(Arr::get($value, 'document_setting.information'))) !!}</p>
@endif

<div class="dispTotalPrice">
  <dl>
    <dt>ご請求金額</dt>
    <dd>￥{{ number_format(Arr::get($value, 'amount_total')) }}</dd>
    <dt>お支払期限</dt>
    <dd>{{ Arr::get($value, 'payment_deadline') }}</dd>
  </dl>
</div>

@if(check_business_form_pdf_item($value, "振込先", 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK')) && Arr::get($value, 'document_setting.account_payable')){{-- 振込先 --}}
  <div class="dispBank">
    <h3>お振込先</h3>
    <p>{!! nl2br(e(Arr::get($value, 'document_setting.account_payable'))) !!}</p>
  </div>
@endif

<p class="dispEtc mb30">
  @if(check_business_form_pdf_item($value, "備考", 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK')) && Arr::get($value, 'document_setting.note')){{-- 備考 --}}
    {!! nl2br(e(Arr::get($value, 'document_setting.note'))) !!}
  @endif
</p>

@if(check_business_form_pdf_item($value, "代金内訳", 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'))){{-- 代金内訳 --}}

<div class="dispPrice">
<h3>代金内訳</h3>
<table>
<thead>
  <tr>
    <th>予約番号</th>
    @if(check_business_form_pdf_item($value, "御社担当", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')))
    <th>単価</th>
    @endif
    @if(check_business_form_pdf_item($value, "単価・金額", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')))
      <th>単価</th>
    @endif
    <th>数量</th>
    @if(check_business_form_pdf_item($value, "消費税", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')))
      <th>消費税</th>
    @endif
    @if(check_business_form_pdf_item($value, "単価・金額", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')))
      <th>金額</th>
    @endif
  </tr>
</thead>
<tbody>
  @foreach($reservePriceBreakdown as $reserveNumber => $rows)
    @foreach($rows as $row)
      <tr>
        <td>
          {{ $reserveNumber }}
          @if(Arr::get($reserveCancelInfo, $reserveNumber, false)){{ config('consts.const.RESERVE_CANCEL_LABEL') }}@endif {{-- キャンセル予約の場合はキャセルラベルを表記 --}}
        </td>
        @if(check_business_form_pdf_item($value, "御社担当", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')))
          <td>
            {{ Arr::get($row, 'partner_manager') }} 
            @if(check_business_form_pdf_item($value, "御社担当_御社担当(敬称)", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')))
              様
            @endif {{-- 御社担当敬称 --}}
          </td>
        @endif
        @if(check_business_form_pdf_item($value, "単価・金額", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')))
          <td>
            @if(Arr::get($reserveCancelInfo, $reserveNumber, false)) {{-- キャンセル予約の場合はキャンセルチャージ金額 --}}
              ￥{{ number_format( Arr::get($row, 'cancel_charge', 0) ) }}
            @else
              ￥{{ number_format( Arr::get($row, 'gross_ex', 0) ) }}
            @endif
          </td>
        @endif
        <td>{{ number_format( Arr::get($row, 'quantity', 0) ) }}</td>
        @if(check_business_form_pdf_item($value, "消費税", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')))
          <td>
            @if(Arr::get($reserveCancelInfo, $reserveNumber, false)) {{-- キャンセル予約の場合は消費税の表記ナシ --}}
              -
            @else
              {{ Arr::get($formSelects['zeiKbns'], Arr::get($row, 'zei_kbn'), "-") }}
            @endif
          </td>
        @endif
        @if(check_business_form_pdf_item($value, "単価・金額", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')))
          <td>
            @if(Arr::get($reserveCancelInfo, $reserveNumber, false)) {{-- キャンセル予約の場合はキャンセルチャージ金額 --}}
              ￥{{ number_format( Arr::get($row, 'cancel_charge', 0) ) }}
            @else
              ￥{{ number_format( Arr::get($row, 'gross', 0)) }}
            @endif
          </td>
        @endif
      </tr>
    @endforeach
  @endforeach
  <tr class="total">
    <td colspan="{{ 5 - (check_business_form_pdf_item($value, "消費税", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')) ? 0 : 1) - (check_business_form_pdf_item($value, "単価・金額", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')) ? 0 : 2) - (check_business_form_pdf_item($value, "御社担当", 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE')) ? 0 : 1) }}">合計金額</td>
    <td>￥{{ number_format(Arr::get($value, 'amount_total', 0)) }}</td>
  </tr></tbody></table>
</div>
@endif
</div>
@endsection