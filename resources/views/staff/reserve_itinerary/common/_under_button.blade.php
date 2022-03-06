<ul id="formControl">

  @if($applicationStep === config('consts.reserves.APPLICATION_STEP_DRAFT')) {{-- 見積 --}}

    <li class="wd50">
      <button class="grayBtn" onClick="event.preventDefault();location.href='{{ $backUrl }}'"><span class="material-icons">arrow_back_ios</span>{{ $mode === 'edit' ? '編集' : '登録' }}せずに戻る</button>
    </li>
    <li class="wd50">
      <button class="blueBtn doubleBan"><span class="material-icons">save</span> この内容で{{ $mode === 'edit' ? '更新' : '登録' }}する</button>
    </li>  

  @elseif($applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE')) {{-- 予約 --}}

    @if(!$reserve->is_canceled && $isEnabled) {{-- 予約時は有効行程のみ編集可。キャンセル予約の場合も登録・編集不可 --}}
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();location.href='{{ $backUrl }}'"><span class="material-icons">arrow_back_ios</span>{{ $mode === 'edit' ? '編集' : '登録' }}せずに戻る</button>
      </li>
      <li class="wd50">
        <button class="blueBtn doubleBan"><span class="material-icons">save</span> この内容で{{ $mode === 'edit' ? '更新' : '登録' }}する</button>
      </li>  
    @else
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();location.href='{{ $backUrl }}'"><span class="material-icons">arrow_back_ios</span>戻る</button></li>
    @endif

  @endif
</ul>