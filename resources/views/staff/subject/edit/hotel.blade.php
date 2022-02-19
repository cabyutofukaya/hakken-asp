@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">list</span>科目追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.master.subject.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')]) }}">科目マスタ</a></li>
      <li><span>科目追加</span></li>
    </ol>

    @can('delete', $subjectHotel)
    <div class="deleteControl">
      <button class="redBtn js-modal-open" data-target="mdDelete">削除</button>
    </div>
    @endcan
  </div>
  
  @include('staff.common.error_message')


  <form method="POST" id="hotelForm" action="{{ route("staff.master.subject.hotel.update",[$agencyAccount, $subjectHotel]) }}">
    @csrf
    @method('PUT')

    <div id="inputArea">
      <ul class="baseList">
        <li class="wd40"><span class="inputLabel">科目カテゴリ</span>
          <div class="selectBox">
            <select name="category" disabled>
              @foreach($formSelects['subjectCategories'] as $code => $str)
                <option value="{{ $code }}" @if($subjectHotel->subject_category->code === $code) selected @endif>{{ $str }}</option>
              @endforeach
            </select>
          </div>
        </li>
      </ul>
      
      <hr class="sepBorder">
      
      @include("staff.subject.common.hotel_form", ['editMode' => 'edit'])

    </div>
    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();location.href='{{ route('staff.master.subject.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')]) }}'"><span class="material-icons">arrow_back_ios</span>更新せずに戻る</button>
      </li>
      @can('update', $subjectHotel)
        <li class="wd50">
          <button class="blueBtn doubleBan" id="submit"><span class="material-icons">save</span> この内容で更新する</button>
        </li>
      @endcan
    </ul>

  </form>

</main>

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
<script src="{{ mix('/staff/js/subject_hotel-edit.js') }}"></script>
@endsection


@section('modal')
  @include('staff.common.modal_delete', [
    'title' => 'この項目を削除しますか？',
    'actionUrl' => route('staff.master.subject.hotel.destroy', [
      'agencyAccount' => $agencyAccount,
      'subjectHotel' => $subjectHotel, 
    ])
  ])
@endsection