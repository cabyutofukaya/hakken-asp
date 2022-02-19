@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">public</span>国・地域追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.master.area.index', $agencyAccount) }}">国・地方マスタ</a></li>
      <li><span>国・地域追加</span></li>
    </ol>
    @can('delete', $vArea)
      <div class="deleteControl">
        <button class="redBtn js-modal-open" data-target="mdDelete">削除</button>
      </div>
    @endcan
  </div>
  
  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.master.area.update', [$agencyAccount, $vArea->uuid]) }}">
    @csrf
    @method('PUT')

    <div id="inputArea">
      <ul class="baseList">		
          <li class="wd40"><span class="inputLabel">国・地域コード</span>
            <input type="text" name="code" value="{{ $defaultValue['code'] ?? null }}" disabled>
          </li>
          <li class="wd40">
            <div id="directionArea"
              jsVars='@json($jsVars)'
              defaultValue='@json($defaultValue)'
            ></div>
            {{-- <span class="inputLabel">方面</span>
            <div class="selectBox">
              <select name="v_direction_uuid" @if($vArea->master) disabled @endif>
                @foreach($formSelects['vDirections'] as $k => $v)
                  <option value="{{ $k }}"@if($k == Arr::get($defaultValue, 'v_direction_uuid', '')) selected @endif>{{ $v }}</option>
                @endforeach
              </select>
            </div> --}}
          </li>
          <li class="wd100"><span class="inputLabel">国・地域名称</span>          
            <input type="text" name="name" value="{{ $defaultValue['name'] ?? null }}" @if($vArea->master) readonly @endif>
          </li>
          <li class="wd100"><span class="inputLabel">国・地域名称(英)</span>          
            <input type="text" name="name_en" value="{{ $defaultValue['name_en'] ?? null }}" @if($vArea->master) readonly @endif>
          </li>
      </ul>
    </div>
    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>更新せずに戻る</button></li>
      @can('update', $vArea)
        <li class="wd50"><button class="blueBtn doubleBan"><span class="material-icons">save</span> この内容で更新する</button></li>
      @endcan
    </ul>
  </form>
</main>

<script src="{{ mix('/staff/js/area-create-edit.js') }}"></script>
@endsection


@section('modal')
  @include('staff.common.modal_delete', [
    'title' => 'この項目を削除しますか？',
    'actionUrl' => route('staff.master.area.destroy', [
      'agencyAccount' => $agencyAccount,
      'uuid' => $vArea->uuid, 
    ])
  ])
@endsection