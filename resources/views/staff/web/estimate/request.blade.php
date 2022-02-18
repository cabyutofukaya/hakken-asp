@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1>
      <span class="material-icons">event_note</span>
      オンライン相談依頼 {{ $reserve->request_number }}
    </h1>
    <ol class="breadCrumbs">
      <li>
        <a href="{{ route('staff.web.estimates.normal.index', [$agencyAccount]) }}">WEB見積管理</a>
      </li>
      <li>
        <span>
          オンライン相談依頼 {{ $reserve->request_number }}
        </span>
      </li>
    </ol>
  </div>

  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')

  <div id="declineMessageArea"><!-- React用declineメッセージ出力エリア --></div>
  
  @include('staff.web.common.invalid_message', ['webReserveExt' => $reserve->web_reserve_ext]){{-- 受付無効メッセージ --}}

  <div class="requestBox">
    <h2>
      <span class="material-icons">subject</span>相談内容
    </h2>
    <ul class="sideList half">
      <li><table class="baseTable">
        <tbody>
          <tr>
            <th>依頼番号</th>
            <td>{{ $reserve->request_number }}</td>
          </tr>
          <tr>
            <th>受付No</th>
            <td>{{ optional($reserve->web_reserve_ext->web_consult)->receipt_number ?? '-' }}</td>
          </tr>
          <tr>
            <th>申込日</th>
            <td>{{ optional($reserve->application_date)->val ?? '-' }}</td>
          </tr>
          <tr>
            <th>申込者</th>
            <td>{{ optional($reserve->applicantable)->name ?? '-' }}</td>
          </tr>
          <tr>
            <th>旅行種別</th>
            <td>{{ optional($reserve->travel_type)->val ?? '-' }}</td>
          </tr>
          <tr>
            <th>旅行目的</th>
            <td>{{ optional($reserve->web_reserve_ext->web_consult)->purpose ?? '-' }}</td>
          </tr>
          @if(optional($reserve->web_reserve_ext->web_consult) && $reserve->web_reserve_ext->web_consult->departure_kbn != config('consts.web_consults.DEPARTURE_KBN_DATE')) {{-- 旅行日が具体的でない場合 --}}
            <tr>
              <th>旅行日</th>
              <td>{{ optional($reserve->web_reserve_ext->web_consult)->departure_label }}~{{ optional($reserve->web_reserve_ext->web_consult)->stays_label }}
              </td>
            </tr>
          @endif
          <tr>
            <th>出発日</th>
            <td>{{ $reserve->departure_date ?? '-' }}</td>
          </tr>
          <tr>
            <th>帰着日</th>
            <td>{{ $reserve->return_date ?? '-' }}</td>
          </tr>
          <tr>
            <th>出発地</th>
            <td>{{ optional($reserve->departure)->name ?? '-' }}</td>
          </tr>
          <tr>
            <th>目的地</th>
            <td>{{ $reserve->destination_label ?? '-' }}</td>
          </tr>
        </tbody>
      </table>
    </li>
    <li>
      <table class="baseTable">
        <tbody>
          <tr>
            <th>人数</th>
            <td>
              <ul class="person">
                <li>大人 {{ optional($reserve->web_reserve_ext->web_consult)->adult ?? '0' }}名</li>
                <li>子供 {{ optional($reserve->web_reserve_ext->web_consult)->child ?? '0' }}名</li>
                <li>幼児 {{ optional($reserve->web_reserve_ext->web_consult)->infant ?? '0' }}名</li>
              </ul>
            </td>
          </tr>
          <tr>
            <th>予算の目安</th>
            <td>
              {{ optional($reserve->web_reserve_ext->web_consult)->budget_label ?? '-'}}</td>
            </tr>
          <tr>
            <th>興味があること</th>
            <td>
              <ul class="tagList">
                @if(optional($reserve->web_reserve_ext->web_consult))
                  @foreach($reserve->web_reserve_ext->web_consult->interest as $interest)
                    <li>{{ $interest }}</li>
                  @endforeach
                @endif
              </ul>
            </td>
          </tr>
          <tr>
            <th>要望</th>
            <td>{!! optional($reserve->web_reserve_ext->web_consult) ? nl2br(e($reserve->web_reserve_ext->web_consult->request)) : "-" !!}</td>
          </tr>
          <tr>
            <th>自社担当</th>
            <td>{{ optional($reserve->web_reserve_ext->manager)->name ?? '-' }}</td>
          </tr>
        </tbody>
      </table>
    </li>
  </ul>
  </div>

  @if($reserve->web_reserve_ext->consent_at || $reserve->web_reserve_ext->is_invalid_web_reserve) {{-- 受付済、もしくは受付できないステータスの場合は「戻る」ボタンのみ表示 --}}
    <ul id="formControl">
      <li class="wd20">
        <button class="grayBtn" onClick="event.preventDefault();history.back()">
          <span class="material-icons">arrow_back_ios</span>戻る
        </button>
      </li>
    </ul>
  @else
    <ul id="formControl"
      requstNumber='{{ $reserve->request_number ?? '' }}'
      rejectionAt='{{ optional($reserve->web_reserve_ext)->rejection_at ?? null }}'
      jsVars='@json($jsVars)'
    ></ul>
    <script src="{{ mix('/staff/js/web-estimate-request.js') }}"></script>
  @endif

</main>
@endsection