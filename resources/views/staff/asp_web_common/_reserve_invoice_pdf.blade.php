<div class="documentPrint">
  <h2 class="blockTitle">
    @if(Arr::get($value, 'document_setting.title'))
      {{ Arr::get($value, 'document_setting.title') }}
    @endif
  </h2>
  <div class="number">
    <p>請求番号：{{ Arr::get($value, 'user_invoice_number') }}</p>
    <p>発行日：{{ Arr::get($value, 'issue_date') }}</p>
  </div>
  <div class="dcHead">
    <div>
      @if(in_array("宛名", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[]))){{-- 宛名エリア --}}
      @if(Arr::get($value, 'document_address.type') === config('consts.reserves.PARTICIPANT_TYPE_PERSON')) {{-- 個人申込 --}}
        <div class="dispSign">
          <p class="dispPostal">
            @if(in_array("郵便番号", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_PERSON'),[])) && Arr::get($value, 'document_address.zip_code'))
              {{ sprintf("〒%s-%s", substr($value['document_address']['zip_code'], 0, 3), substr($value['document_address']['zip_code'], 3)) }}
            @endif
          </p>
          <p class="dispAddress">
            @if(in_array("都道府県", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_PERSON'),[])))
              {{ Arr::get($value, 'document_address.prefecture') }}
            @endif
            @if(in_array("住所1", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_PERSON'),[])))
              {{ Arr::get($value, 'document_address.address1') }}
            @endif
            @if(in_array("住所2", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_PERSON'),[])))
              {{ Arr::get($value, 'document_address.address2') }}
            @endif
          </p>
        </div>
        <p class="dispName">
          @if(in_array("氏名", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_PERSON'),[])))
            {{ Arr::get($value, 'document_address.name') }} 
            @if(Arr::get($value, 'document_address.name'))
              {{ Arr::get($formSelects['honorifics'], Arr::get($value, 'document_address.honorific')) }}{{-- 敬称 --}}
            @endif
          @endif
        </p>
      @elseif(Arr::get($value, 'document_address.type') === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) {{-- 法人申込 --}}
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
        <p class="dispName">
          @if(in_array("法人名", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS'),[])))
            {{ Arr::get($value, 'document_address.company_name') }}<br/>
          @endif
          @if(in_array("部署名", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS'),[])))
            {{ Arr::get($value, 'document_address.department_name') }}<br/>
          @endif
          @if(in_array("担当者名", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.ADDRESS_BUSINESS'),[])))
            {{ Arr::get($value, 'document_address.name') }}
            @if(Arr::get($value, 'document_address.name'))
              {{ Arr::get($formSelects['honorifics'], Arr::get($value, 'document_address.honorific')) }}{{-- 敬称 --}}
            @endif
          @endif
        </p>
      @endif
    @endif {{-- 宛名エリア ここまで --}}

    @if(in_array("予約情報(件名・期間・参加者)", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[]))){{-- 予約情報(件名・期間・参加者)エリア --}}
      @if(in_array("件名", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.RESERVATION_INFO'),[])) && Arr::get($value, 'name'))
        <p class="dispTitle">
          件名 {{ Arr::get($value, 'name') }}
        </p>
      @endif
      @if(in_array("期間", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.RESERVATION_INFO'),[])) && (Arr::get($value, 'departure_date') || Arr::get($value, 'return_date')))
        <p class="dispPeriod">
          期間 {{ Arr::get($value, 'departure_date') }}
            @if(Arr::get($value, 'departure_date') && Arr::get($value, 'return_date'))〜@endif
          {{ Arr::get($value, 'return_date') }}
        </p>
      @endif
      <p class="dispParticipant">
        @if(in_array("代表者", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.RESERVATION_INFO'),[])))
            @if(Arr::get($value, 'representative.name'))
              代表者 {{ $value['representative']['name'] }} 
            @endif

            @if(in_array("代表者(ローマ字)", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.RESERVATION_INFO'),[])))

              @if(Arr::get($value, 'representative.name_roman'))
                (
                  @if(in_array("代表者(ローマ字)_Mr/Ms", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.RESERVATION_INFO'),[])))

                    @if(Arr::get($value, 'representative.sex') === config('consts.users.SEX_MALE'))
                      Mr.
                    @endif

                    @if(Arr::get($value, 'representative.sex') === config('consts.users.SEX_FEMALE'))
                      Ms.
                    @endif

                  @endif
                  {{ $value['representative']['name_roman'] }}
                )
              @endif

            @endif {{-- 代表ローマ字 --}}

            @if(in_array("代表者_代表者(敬称)", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.RESERVATION_INFO'),[])))
              様
            @endif {{-- 代表者敬称 --}}
            <br>
          @endif {{-- 代表者 --}}

        @if(in_array("参加者", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.RESERVATION_INFO'),[])) && Arr::get($formSelects, "participants"))
          参加者 @foreach($formSelects['participants'] as $participant)
            {{ $participant['name'] }} 

            @if(in_array("参加者(ローマ字)", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.RESERVATION_INFO'),[])) && $participant['name_roman'])
              (
                @if(in_array("参加者(ローマ字)_Mr/Ms", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.RESERVATION_INFO'),[])))

                  @if($participant['sex'] === config('consts.users.SEX_MALE'))
                    Mr.
                  @endif

                  @if($participant['sex'] === config('consts.users.SEX_FEMALE'))
                    Ms.
                  @endif
                  
                @endif
                {{ $participant['name_roman'] }}
              )
            @endif {{-- 参加者ローマ字 --}}

            @if(in_array("参加者_参加者(敬称)", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.RESERVATION_INFO'),[])))
              様
            @endif {{-- 参加者敬称 --}}

            @if(!$loop->last) / @endif

          @endforeach
        @endif {{-- 参加者 --}}
      </p>
    @endif {{-- 予約情報(件名・期間・参加者)エリア ここまで --}}
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
      @if(in_array("住所2", Arr::get($value, 'document_common_setting.setting.'.config('consts.document_commons.COMPANY_INFO'),[])))
        <br>{{ Arr::get($value, 'document_common_setting.address2') }}
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

  @if(in_array("検印欄", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[]))){{-- 検印欄エリア --}}
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

  @if(in_array("案内文", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[])) && Arr::get($value, 'document_setting.information')){{-- 案内文 --}}
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

  @if(in_array("振込先", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[])) && Arr::get($value, 'document_setting.account_payable')){{-- 振込先 --}}
    <div class="dispBank">
      <h3>お振込先</h3>
      <p>{!! nl2br(e(Arr::get($value, 'document_setting.account_payable'))) !!}</p>
    </div>
  @endif

  <p class="dispEtc mb30">
    @if(in_array("備考", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[])) && Arr::get($value, 'document_setting.note')){{-- 備考 --}}
      {!! nl2br(e(Arr::get($value, 'document_setting.note'))) !!}
    @endif
  </p>

  @if(in_array("代金内訳", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[]))){{-- 代金内訳 --}}
    <div class="dispPrice">
      <h3>代金内訳</h3>
      <table>
        <thead>
          <tr>
            <th>内容</th>
            @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
              <th>単価</th>
            @endif
            <th>数量</th>
            @if(in_array("消費税", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
              <th>消費税</th>
            @endif
            @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
              <th>金額</th>
            @endif
          </tr>
          </thead>
        <tbody>
          @foreach($optionPriceBreakdown as $row) {{-- オプション科目 --}}
            <tr>
              <td>
                {{ Arr::get($row, 'name') }}
                @if($isCanceled){{ config('consts.const.RESERVE_CANCEL_LABEL') }}@endif {{-- キャンセルの場合はキャセルラベルを表記 --}}
              </td>
              @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
                <td>
                  @if($isCanceled) {{-- キャンセルの場合はキャンセルチャージ金額 --}}
                    ￥{{ number_format( Arr::get($row, 'cancel_charge', 0) ) }}
                  @else
                    ￥{{ number_format( Arr::get($row, 'gross_ex', 0) ) }}
                  @endif
                </td>
              @endif
              <td>
                {{ number_format( Arr::get($row, 'quantity', 0) ) }}
              </td>
              @if(in_array("消費税", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
                <td>
                  @if($isCanceled) {{-- キャンセルの場合は消費税の表記ナシ --}}
                    -
                  @else
                    {{ Arr::get($formSelects['zeiKbns'], Arr::get($row, 'zei_kbn'), "-") }}
                  @endif
                </td>
              @endif
              @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
                <td>
                  @if($isCanceled) {{-- キャンセルの場合はキャンセルチャージ金額で計算 --}}
                    ￥{{ number_format( Arr::get($row, 'cancel_charge', 0) * Arr::get($row, 'quantity', 0) ) }}
                  @else
                   ￥{{ number_format( Arr::get($row, 'gross', 0) * Arr::get($row, 'quantity', 0) ) }}
                  @endif
                </td>
              @endif
            </tr>
          @endforeach

          @foreach($airticketPriceBreakdown as $row) {{-- 航空券 --}}
            <tr>
              <td>
                {{ Arr::get($row, 'name') }} {{ Arr::get($row, 'seat') }}
                @if($isCanceled){{ config('consts.const.RESERVE_CANCEL_LABEL') }}@endif {{-- キャンセルの場合はキャセルラベルを表記 --}}
              </td>
              @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
                <td>
                  @if($isCanceled) {{-- キャンセルの場合はキャンセルチャージ金額 --}}
                    ￥{{ number_format( Arr::get($row, 'cancel_charge', 0) ) }}
                  @else
                    ￥{{ number_format( Arr::get($row, 'gross_ex', 0) ) }}
                  @endif
                </td>
              @endif
              <td>
                {{ number_format( Arr::get($row, 'quantity', 0) ) }}
              </td>
              @if(in_array("消費税", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
                <td>
                  @if($isCanceled) {{-- キャンセルの場合は消費税の表記ナシ --}}
                    -
                  @else
                    {{ Arr::get($formSelects['zeiKbns'], Arr::get($row, 'zei_kbn'), "-") }}
                  @endif
                </td>
              @endif
              @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
                <td>
                  @if($isCanceled) {{-- キャンセルの場合はキャンセルチャージ金額で計算 --}}
                    ￥{{ number_format( Arr::get($row, 'cancel_charge', 0) * Arr::get($row, 'quantity', 0) ) }}
                  @else
                    ￥{{ number_format( Arr::get($row, 'gross', 0) * Arr::get($row, 'quantity', 0) ) }}
                  @endif
                </td>
              @endif
            </tr>
          @endforeach

          @foreach($hotelPriceBreakdown as $row) {{-- ホテル --}}
            <tr>
              <td>
                {{ Arr::get($row, 'name') }} {{ Arr::get($row, 'room_type') }} {{ Arr::get($row, 'quantity') }}名
                @if($isCanceled){{ config('consts.const.RESERVE_CANCEL_LABEL') }}@endif {{-- キャンセルの場合はキャセルラベルを表記 --}}
              </td>
              @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
                <td>
                  @if($isCanceled) {{-- キャンセルの場合はキャンセルチャージ金額 --}}
                    ￥{{ number_format( Arr::get($row, 'cancel_charge', 0) ) }}
                  @else
                    ￥{{ number_format( Arr::get($row, 'gross_ex', 0) ) }}
                  @endif
                </td>
              @endif
              <td>
                {{ number_format( Arr::get($row, 'quantity', 0) ) }}
              </td>
              @if(in_array("消費税", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
                <td>
                  @if($isCanceled) {{-- キャンセルの場合は消費税の表記ナシ --}}
                    -
                  @else
                    {{ Arr::get($formSelects['zeiKbns'], Arr::get($row, 'zei_kbn'), "-") }}
                  @endif
                </td>
              @endif
              @if(in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])))
                <td>
                  @if($isCanceled) {{-- キャンセルの場合はキャンセルチャージ金額で計算 --}}
                    ￥{{ number_format( Arr::get($row, 'cancel_charge', 0) * Arr::get($row, 'quantity', 0) ) }}
                  @else
                    ￥{{ number_format( Arr::get($row, 'gross', 0) * Arr::get($row, 'quantity', 0) ) }}
                  @endif
                </td>
              @endif
            </tr>
          @endforeach

          <tr className="total">
            <td colSpan="{{ 4 - (in_array("消費税", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])) ? 0 : 1) - (in_array("単価・金額", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.BREAKDOWN_PRICE'),[])) ? 0 : 2) }}">合計金額</td>
            <td>
              @if($isCanceled) {{-- キャンセルの場合はキャンセルチャージ金額で計算 --}}
                ￥{{ number_format(collect($optionPrices)->sum('cancel_charge') + collect($airticketPrices)->sum('cancel_charge') + collect($hotelPrices)->sum('cancel_charge')) }}
              @else
                ￥{{ number_format(collect($optionPrices)->sum('gross') + collect($airticketPrices)->sum('gross') + collect($hotelPrices)->sum('gross')) }}
              @endif
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  @endif {{-- 代金内訳 ここまで --}}

  @if(in_array("航空券情報", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[])) && $airticketPrices){{-- 航空券情報 --}}
    <div class="dispSchedule">
      <h3>航空券情報</h3>
        <table>
          <thead>
            <tr>
              <th>氏名</th>
              @if(in_array("座席・クラス", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.AIR_TICKET_INFO'),[])))
                <th>座席/クラス</th>
              @endif
              @if(in_array("航空会社", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.AIR_TICKET_INFO'),[])))
                <th>航空会社</th>
              @endif
              @if(in_array("REF番号", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.AIR_TICKET_INFO'),[])))
                <th>REF番号</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach($airticketPrices as $airticketPrice)
              <tr>
                <td>{{ $airticketPrice['user_name'] }}</td>
                @if(in_array("座席・クラス", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.AIR_TICKET_INFO'),[])))
                  <td>{{ $airticketPrice['seat'] ?? '-'}}/{{ $airticketPrice['booking_class'] ?? '-'}}</td>
                @endif
                @if(in_array("航空会社", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.AIR_TICKET_INFO'),[])))
                  <td>{{ $airticketPrice['airline_company'] ?? '-'}}</td>
                @endif
                @if(in_array("REF番号", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.AIR_TICKET_INFO'),[])))
                  <td>{{ $airticketPrice['reference_number'] ?? '-'}}</td>
                @endif
              </tr>
            @endforeach
          </tbody>
        </table>
    </div>
  @endif

  @if(in_array("ホテル情報", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[])) && $hotelInfo){{-- ホテル情報 --}}
    <div class="dispHotel">
      <h3>宿泊施設情報</h3>
        <table>
          <thead>
            <tr>
              <th>宿泊日</th>
              <th>ホテル名</th>
              <th>部屋タイプ</th>
              <th>数量</th>
            </tr>
          </thead>
          <tbody>
          @foreach($hotelInfo as $date => $hotels)
            @foreach($hotels as $hotel)
              <tr>
                <td>{{ $date }}</td>
                <td>{{ Arr::get($hotel,'hotel_name') }}</td>
                <td>{{ Arr::get($hotel,'room_type') }}</td>
                <td>{{ count(Arr::get($hotel,'rooms',[])) }}</td>
              </tr>
            @endforeach
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
  {{-- ホテル情報 ここまで --}}


  @if(in_array("ホテル連絡先", Arr::get($value, 'document_setting.setting.'.config('consts.document_requests.DISPLAY_BLOCK'),[])) && $hotelContacts){{-- ホテル連絡先 --}}
    <div class="dispHotelInfo">
      <h3>宿泊施設連絡先</h3>
      @foreach($hotelContacts as $hotelContact)
        <div class="dispHotelList">
          <p class="hotelName">{{ Arr::get($hotelContact,"hotel_name") }}</p>
          <p class="hotelAdd">{{ Arr::get($hotelContact,"address") }}</p>
          <p class="hotelContact">
            @if(Arr::get($hotelContact,"tel"))
              TEL:{{ $hotelContact["tel"] }}
            @endif
            @if(Arr::get($hotelContact,"fax"))
               / FAX:{{ $hotelContact["fax"] }}
            @endif
          </p>
          <p class="hotelUrl">{{ Arr::get($hotelContact,"url") }}</p>
        </div>
      @endforeach
    </div>
  @endif {{-- ホテル連絡先 ここまで --}}
