<form method="POST" id="optionForm" action="{{ route("staff.master.subject.option.store",[ $agencyAccount]) }}">
  @csrf

  <input type="hidden" name="category">{{-- categoryプルダウンで選んだ値をjsからセット。phpで値を埋め込むとなぜか値が全て「option」になってしまうため --}}

  @include("staff.subject.common.option_form", ['editMode' => 'create'])

</form>