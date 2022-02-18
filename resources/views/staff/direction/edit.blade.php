@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
<h1><span class="material-icons">explore</span>方面追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.master.direction.index', $agencyAccount) }}">方面マスタ</a></li>
      <li><span>方面追加</span></li>
    </ol>
    @can('delete', $vDirection)
      <div class="deleteControl">
        <button class="redBtn js-modal-open" data-target="mdDelete">削除</button>
      </div>
    @endcan
  </div>
  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.master.direction.update', [$agencyAccount, $vDirection->uuid]) }}">
    @csrf
    @method('PUT')

    <div id="inputArea">
      <ul class="baseList">		
        <li class="wd40"><span class="inputLabel">方面コード</span>
          <input type="text" name="code" value="{{ $defaultValue['code'] ?? null }}" disabled>
        </li>
        <li class="wd100"><span class="inputLabel">方面名称</span>
          <input type="text" name="name" value="{{ $defaultValue['name'] ?? null }}" @if($vDirection->master) readonly @endif >
        </li>
      </ul>
    </div>

    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>更新せずに戻る</button></li>
      @can('update', $vDirection)
        <li class="wd50"><button class="blueBtn"><span class="material-icons">save</span> この内容で更新する</button></li>
      @endcan
    </ul>
  </form>
</main>
@endsection


@section('modal')
  @include('staff.common.modal_delete', [
    'title' => 'この項目を削除しますか？',
    'actionUrl' => route('staff.master.direction.destroy', [
      'agencyAccount' => $agencyAccount,
      'uuid' => $vDirection->uuid, 
    ])
  ])
@endsection