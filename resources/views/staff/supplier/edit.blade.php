@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">move_to_inbox</span>仕入れ先追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.master.supplier.index', $agencyAccount) }}">仕入れ先マスタ</a></li>
      <li><span>仕入れ先追加</span></li>
    </ol>
    @can('forceDelete', $supplier)
      <div class="deleteControl">
        <button class="redBtn js-modal-open" data-target="mdDelete">削除</button>
      </div>
    @endcan
  </div>
  
  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.master.supplier.update', [$agencyAccount,$supplier]) }}">
    @csrf
    @method('PUT')

    @include("staff.supplier.common.form", ['editMode' => 'edit'])

    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>更新せずに戻る</button>
      </li>
      @can('update', $supplier)
        <li class="wd50">
          <button class="blueBtn doubleBan"><span class="material-icons">save</span> この内容で更新する</button>
        </li>
      @endcan
    </ul>
  </form>
</main>

@include('staff.common.modal_delete', [
  'title' => 'この項目を削除しますか？',
  'actionUrl' => route('staff.master.supplier.destroy', [
    'agencyAccount' => $agencyAccount,
    'supplier' => $supplier, 
  ])
])
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> 
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script> 
<script>
    flatpickr.localize(flatpickr.l10ns.ja);
    flatpickr('.calendar input', {
        allowInput: true,
		dateFormat: "Y/m/d"
    });
</script>
<script src="{{ mix('/staff/js/supplier-edit.js') }}"></script>
@endsection
