@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1>
      <span class="material-icons">list</span>科目追加
    </h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.master.subject.index', $agencyAccount) }}">科目マスタ</a></li>
      <li><span>科目追加</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <div id="inputArea">
    <ul class="baseList">
      <li class="wd40"><span class="inputLabel">科目カテゴリ</span>
        <div class="selectBox">
          <select name="category">
            @foreach($formSelects['subjectCategories'] as $code => $str)
              <option value="{{ $code }}" @if($category === $code) selected @endif>{{ $str }}</option>
            @endforeach
          </select>
        </div>
      </li>
    </ul>
    <hr class="sepBorder">

    @foreach(array_keys($formSelects['subjectCategories']) as $code) {{-- オプション科目/航空券科目/ホテル科目 form --}}
      <div data-form="{{ $code }}">
          @include("staff.subject.create.{$code}")
        </div>
    @endforeach

  </div>
      
  <ul id="formControl">
    <li class="wd50">
      <button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button>
    </li>
    <li class="wd50">
      <button class="blueBtn" id="submit"><span class="material-icons">save</span> この内容で登録する</button>
    </li>
  </ul>
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
<script src="{{ mix('/staff/js/subject-create.js') }}"></script>
@endsection
