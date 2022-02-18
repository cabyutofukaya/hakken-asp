<div class="baseInfo mb40">
  <table class="baseTable">
    <tr>
      <th>案件名</th>
      <td>{{ $reserve->name ?? '-' }}</td>
      <th>代表者</th>
      <td>{{ $reserve->representatives[0]->name ?? '-' }}({{ $reserve->representatives[0]->name_kana ?? '-' }})</td>
    </tr>
    <tr>
      <th>出発日</th>
      <td>{{ $reserve->departure_date ?? '-' }}</td>
      <th>帰着日</th>
      <td>{{ $reserve->return_date ?? '-' }}</td>
    </tr>
    <tr>
      <th>出発地</th>
      <td>{{ $reserve->departure->name }} {{ $reserve->departure_place }}</td>
      <th>目的地</th>
      <td>{{ $reserve->destination->name }} {{ $reserve->destination_place }}</td>
    </tr>
    <tr>
      <th>旅行目的</th>
      <td colspan="3">{{ optional($reserve->web_reserve_ext->web_consult)->purpose ?? '-' }}</td>
		</tr>
		<tr>
      <th>興味があること</th>
      <td colspan="3">
        <ul class="tagList">
          @if(optional($reserve->web_reserve_ext->web_consult))
            @foreach($reserve->web_reserve_ext->web_consult->interest as $interest)
              <li>{{ $interest }}</li>
            @endforeach
          @endif
        </ul></td>
		</tr>
    <tr>
      <th>備考</th>
      <td colspan="3">{{ $reserve->note ?? '-' }}</td>
    </tr>
    <tr>
      <th>参加者</th>
      <td colspan="3">AD{{ $reserve->participants->where('age_kbn',config('consts.users.AGE_KBN_AD'))->count() }}名　CH{{ $reserve->participants->where('age_kbn',config('consts.users.AGE_KBN_CH'))->count() }}名　INF{{ $reserve->participants->where('age_kbn',config('consts.users.AGE_KBN_INF'))->count() }}名　他{{ $reserve->participants->where('age_kbn',null)->count() }}名<span class="memberToggle">参加者一覧</span>
        <div class="memberList">
          <table class="baseTable">
            <thead>
              <tr>
                <th>氏名</th>
                <th>性別</th>
                <th>年齢</th>
                <th>年齢区分</th>
              </tr>
            </thead>
            <tbody>
              @foreach($reserve->participants as $row)
                <tr>
                  <td>{{ $row->name ?? '-' }}({{ $row->name_kana ?? '-' }})</td>
                  <td>{{ $row->sex_label }}</td>
                  <td>{{ $row->age_calc ?? '-' }}</td>
                  <td>{{ $row->age_kbn_label ?? '-' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </td>
    </tr>
  </table>
</div>