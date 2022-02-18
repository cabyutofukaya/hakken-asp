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
      @if(in_array("宛名", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK'),[]))){{-- 宛名エリア --}}
        @if(Arr::get($value, 'document_address.type') === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS'))
          <div class="dispSign">
            <p class="dispPostal">
              @if(in_array("郵便番号", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS'),[])) && Arr::get($value, 'document_address.zip_code'))
                {{ sprintf("〒%s-%s", substr($value['document_address']['zip_code'], 0, 3), substr($value['document_address']['zip_code'], 3)) }}
              @endif
            </p>  
            <p class="dispAddress">
              @if(in_array("都道府県", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS'),[])))
                {{ Arr::get($value, 'document_address.prefecture') }}
              @endif
              @if(in_array("住所1", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS'),[])))
                {{ Arr::get($value, 'document_address.address1') }}
              @endif
              @if(in_array("住所2", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS'),[])))
                {{ Arr::get($value, 'document_address.address2') }}
              @endif
            </p>
          </div>
          <p className="dispName">
            @if(in_array("法人名", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS'),[])) && Arr::get($value, 'document_address.company_name'))
              {{ Arr::get($value, 'document_address.company_name') }}{{ Arr::get($formSelects['honorifics'], Arr::get($value, 'document_address.honorific')) }}{{-- 敬称 --}}
            @endif
          </p>
        @endif
      @endif

      @if(in_array("予約情報(件名・期間・担当者)", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK'),[]))){{-- 予約情報(件名・期間・担当者) --}}

        @if(in_array("件名", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.RESERVATION_INFO'),[])) && Arr::get($value, 'name'))
          <p class="dispTitle">
            件名 {{ Arr::get($value, 'name') }}
          </p>
        @endif

        @if(in_array("期間", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.RESERVATION_INFO'),[])) && (Arr::get($value, 'period_from') || Arr::get($value, 'period_to')))
          <p class="dispPeriod">
            期間 {{ Arr::get($value, 'period_from') }}
              @if(Arr::get($value, 'period_from') && Arr::get($value, 'period_to'))〜@endif
            {{ Arr::get($value, 'period_to') }}分
          </p>
        @endif

        <p class="dispParticipant">
          @if(in_array("御社担当", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.RESERVATION_INFO'),[])) && Arr::get($formSelects, "partnerManagers"))
  
            御社担当 @foreach($formSelects['partnerManagers'] as $partnerManager)
              {{ $partnerManager['org_name'] }}
              @if(in_array("御社担当_御社担当(敬称)", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.RESERVATION_INFO'),[])))
                様
              @endif {{-- 御社担当敬称 --}}
  
              @if(!$loop->last) / @endif
  
            @endforeach
          @endif {{-- 御社担当 --}}
        </p>

      @endif
    </div>

  <div class="dispCorp">
    @if(in_array("自社情報", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[]))){{-- 自社情報エリア --}}
      @if(in_array("自社名", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO'),[])))
        <p class="dispCompany">
          {{ Arr::get($value, 'document_common_setting.company_name') }}
        </p>
      @endif
      @if(in_array("補足情報1", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO'),[])))
        <p class="dispEtc01">
          {{ Arr::get($value, 'document_common_setting.supplement1') }}
        </p>
      @endif
      @if(in_array("補足情報2", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO'),[])))
        <p class="dispEtc02">
          {{ Arr::get($value, 'document_common_setting.supplement2') }}
        </p>
        @endif
        @if(in_array("郵便番号", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO'),[])) && Arr::get($value, 'document_common_setting.zip_code'))
          <p class="dispPostal">
            {{ sprintf("〒%s-%s", substr($value['document_common_setting']['zip_code'], 0, 3), substr($value['document_common_setting']['zip_code'], 3)) }}
          </p>
        @endif  
        <p class="dispCorpAddress">
          @if(in_array("住所1", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO'),[])))
            {{ Arr::get($value, 'document_common_setting.address1') }}
          @endif
          <br>
          @if(in_array("住所2", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO'),[])))
            {{ Arr::get($value, 'document_common_setting.address2') }}
          @endif
        </p>
        <p class="dispCorpContact">
          @if(in_array("TEL", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO'),[])) && Arr::get($value, 'document_common_setting.tel'))
            {{ sprintf("TEL:%s", $value['document_common_setting']['tel']) }}
          @endif
          @if(in_array("FAX", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO'),[])) && Arr::get($value, 'document_common_setting.fax'))
            {{ sprintf(" / FAX:%s ", $value['document_common_setting']['fax']) }}
          @endif
        </p>
        @if(in_array("担当者", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO'),[])) && Arr::get($value, 'manager'))
          <p class="dispManager">担当 {{ $value['manager'] }}</p>
        @endif  
    @endif {{-- 自社情報エリア ここまで --}}

    @if(in_array("検印欄", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK'),[]))){{-- 検印欄エリア --}}
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

@if(in_array("案内文", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK'),[])) && Arr::get($value, 'document_setting.information')){{-- 案内文 --}}
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

@if(in_array("振込先", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK'),[])) && Arr::get($value, 'document_setting.account_payable')){{-- 振込先 --}}
  <div class="dispBank">
    <h3>お振込先</h3>
    <p>{!! nl2br(e(Arr::get($value, 'document_setting.account_payable'))) !!}</p>
  </div>
@endif

<p class="dispEtc mb30">
  @if(in_array("備考", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.DISPLAY_BLOCK'),[])) && Arr::get($value, 'document_setting.note')){{-- 備考 --}}
    {!! nl2br(e(Arr::get($value, 'document_setting.note'))) !!}
  @endif
</p>

@if(in_array("代金内訳", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[]))){{-- 代金内訳 --}}

<div class="dispPrice">
<h3>代金内訳</h3>
<table>
<thead>
  <tr>
    <th>予約番号</th>
    <th>御社担当</th>
    @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE'),[])))
      <th>単価</th>
    @endif
    <th>数量</th>
    @if(in_array("消費税", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE'),[])))
      <th>消費税</th>
    @endif
    @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE'),[])))
      <th>金額</th>
    @endif
  </tr>
</thead>
<tbody>
  @foreach($reservePriceBreakdown as $reserveNumber => $rows)
    @foreach($rows as $row)
      <tr>
        <td>{{ $reserveNumber }}</td>
        <td>{{ Arr::get($row, 'partner_manager') }}</td>
        @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE'),[])))
          <td>￥{{ number_format( Arr::get($row, 'gross_ex', 0) ) }}</td>
        @endif
        <td>{{ number_format( Arr::get($row, 'quantity', 0) ) }}</td>
        @if(in_array("消費税", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE'),[])))
          <td>{{ Arr::get($formSelects['zeiKbns'], Arr::get($row, 'zei_kbn'), "-") }}</td>
        @endif
        @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE'),[])))
          <td>￥{{ number_format( Arr::get($row, 'gross', 0)) }}</td>
        @endif
      </tr>
    @endforeach
  @endforeach
  <tr class="total">
    <td colspan="{{ 5 - (in_array("消費税", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE'),[])) ? 0 : 1) - (in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_request_alls.BREAKDOWN_PRICE'),[])) ? 0 : 2) }}">合計金額</td>
    <td>￥{{ number_format(Arr::get($value, 'amount_total', 0)) }}</td>
  </tr></tbody></table>
</div>
@endif
</div>
@endsection