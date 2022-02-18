@extends('layouts.staff.pdf')

@section('content')
  @foreach($roomingList as $travelDate => $dateRows)
    @foreach($dateRows as $hotelName => $hotelRows)
      <div class="documentPrint">
        <h2 class="blockTitle">ルーミングリスト</h2>
          <dl class="dispRoomingTit">
            <dt>宿泊施設名</dt>
            <dd>{{ $hotelName }}&nbsp;</dd>
            <dt>利用日</dt>
            <dd>{{ $travelDate }}&nbsp;</dd>
          </dl>
          <div class="dispPrice">
            <table class="roomTable">
              <thead>
                <tr>
                  <th class="wd10">No</th>
                  <th>Room No</th>
                  <th>名前</th>
                  <th class="txtalc">性別</th>
                  <th class="txtalc">年齢</th>
                  <th class="txtalc">パスポートNo</th>
                </tr>
              </thead>
            </table>
            @foreach($hotelRows as $roomNumber => $participants)
              <table class="roomTable">
                <tbody>
                  @foreach($participants as $participant)
                    <tr>
                      <td>@if($loop->first){{ $loop->parent->index + 1 }}{{-- 部屋番号が変わるごとにインクリメント --}}@endif</td>
                      <td>@if($loop->first){{ $roomNumber }} @endif</td>
                      <td>{{ $participant->name ?? '-' }}</td>
                      <td class="txtalc">{{ $participant->sex_label ?? '-' }}</td>
                      <td class="txtalc">{{ $participant->age_calc ?? '-' }}</td>
                      <td class="txtalc">{{ $participant->passport_number ?? '-' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endforeach
          </div>
      </div>
    @endforeach
  @endforeach
@endsection