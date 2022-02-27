<ol class="breadCrumbs">
  <li>
    @if($reserve->is_departed) {{-- 催行済 --}}
      <a href="{{ route('staff.estimates.departed.index', $agencyAccount) }}">催行済み一覧</a>
    @elseif($reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE'))
      <a href="{{ route('staff.asp.estimates.reserve.index', $agencyAccount) }}">予約管理</a>
    @endif
  </li>
  <li>
    <a href="{{ $reserveUrl }}">
      @if($reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE'))
        予約情報 {{ $reserve->control_number }}
      @endif
    </a>
  </li>
  <li>
    <span>{{ $current }}</span>
  </li>
</ol>