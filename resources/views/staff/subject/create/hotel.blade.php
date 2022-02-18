<form method="POST" id="hotelForm" action="{{ route("staff.master.subject.hotel.store",[ $agencyAccount]) }}">
  @csrf

  <input type="hidden" name="category">{{-- categoryプルダウンで選んだ値をjsからセット。phpで値を埋め込むとなぜか値が全て「option」になってしまうため --}}

  @include("staff.subject.common.hotel_form", ['editMode' => 'create'])

</form>