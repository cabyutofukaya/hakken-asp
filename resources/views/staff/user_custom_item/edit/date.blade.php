@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">calendar_today</span>日時項目追加</h1>

    @can('forceDelete', $userCustomItem)
      @if(!$userCustomItem->undelete_item)
        <div class="deleteControl">
          <button class="redBtn js-modal-open" data-target="mdDelete">削除</button>
        </div>
      @endif
    @endcan
    
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.system.custom.index', ['agencyAccount' => $agencyAccount, 'tab' => $userCustomItem->user_custom_category->code]) }}">カスタム項目</a></li>
      <li><span>日時項目編集</span></li>
    </ol>
  </div>

  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.system.custom.date.update', [$agencyAccount, $userCustomItem->id]) }}">
    @method('PUT')
    @csrf
    <div id="inputArea">
      <ul class="baseList">
        <li class="wd40"><span class="inputLabel">カテゴリ</span>
          <div class="selectBox">
            <select name="user_custom_category_id" disabled>
              @foreach($formSelects['userCustomCategories'] as $val => $str)
                <option value="{{ $val }}" @if(old('user_custom_category_id', $userCustomItem->user_custom_category_id) == $val) selected @endif>{{ $str }}</option>
              @endforeach
            </select>
          </div>
        </li>
      </ul>
      <ul class="sideList">		
        <li class="wd60"><span class="inputLabel">項目名</span>
          <input 
            type="text" 
            name="name" 
            value="{{ old('name', $userCustomItem->name) }}"
            @if($userCustomItem->unedit_item)disabled @endif
            >
        </li>
        @if($formSelects['positions'])
          <li class="wd40 mr00"><span class="inputLabel">{{ $formSelects['positionLabel'] }}</span>
            <div class="selectBox">
              <select name="display_position" @if($userCustomItem->fixed_item) disabled @endif>
                @foreach($formSelects['positions'] as $val => $str)
                  <option 
                    value="{{ $val }}" 
                    @if(old('user_custom_category_id', $userCustomItem->display_position) == $val) selected @endif
                    >{{ $str }}</option>
                @endforeach
              </select>
            </div>
          </li>
        @endif
      </ul>
        <hr class="sepBorder">
        <ul class="baseList">
          <li><span class="inputLabel">入力形式</span>
            <ul class="baseRadio sideList mt10">
              <li>
                {{-- 時間タイプ(type=text)をカレンダータイプにした場合、データが壊れてしまうので変更不可 --}}
                {{ Arr::get($formSelects['inputTypes'], $userCustomItem->input_type) }}
                <input type="hidden" name="input_type" value="{{ $userCustomItem->input_type }}"/>
              </li>
              {{-- @foreach($formSelects['inputTypes'] as $val => $str)
                <li><input 
                  type="radio" 
                  id="input_type_{{ $val }}" 
                  name="input_type" 
                  value="{{ $val }}" 
                  @if(old('input_type', $userCustomItem->input_type) == $val)checked @endif
                  @if($userCustomItem->unedit_item)disabled @endif
                  >
                <label for="input_type_{{ $val }}">{{ $str }}</label></li>
              @endforeach --}}
            </ul>
          </li>
        </ul>
    </div>
    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();location.href='{{route('staff.system.custom.index', ['agencyAccount' => $agencyAccount, 'tab' => $userCustomItem->user_custom_category->code])}}'">
          <span class="material-icons">arrow_back_ios</span>戻る
        </button>
      </li>
      @can('update', $userCustomItem)
        @if(!$userCustomItem->unedit_item)
          <li class="wd50">
            <button class="blueBtn doubleBan">
              <span class="material-icons">save</span> この内容で保存する
            </button>
          </li>
        @endif
      @endcan
    </ul>
  </form>
</main>

@include('staff.user_custom_item.common.modal_delete')
@endsection