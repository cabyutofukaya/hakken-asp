<form method="POST" id="airplaneForm" action="{{ route("staff.master.subject.airplane.store",[ $agencyAccount]) }}">
  @csrf

  <input type="hidden" name="category">{{-- categoryプルダウンで選んだ値をjsからセット。phpで値を埋め込むとなぜか値が全て「option」になってしまうため --}}

  @include("staff.subject.common.airplane_form", ['editMode' => 'create'])

</form>