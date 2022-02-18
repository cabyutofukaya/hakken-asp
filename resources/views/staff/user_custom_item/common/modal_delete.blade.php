<div id="mdDelete" class="modal js-modal">
  <div class="modal__bg js-modal-close"></div>
  <div class="modal__content">
    <p class="mdTit mb20">この項目を削除しますか？</p>
    <p class="attention">※保存されていたデータも削除されます。</p>
    <ul class="sideList">
      <li class="wd50"><button class="grayBtn js-modal-close">キャンセル</button></li>
      <li class="wd50 mr00">
        <form method="post" action="{{ route('staff.system.custom.delete', [$agencyAccount, $userCustomItem->id]) }}">
          @method('DELETE')
          @csrf
          <button class="redBtn" type="submit">削除する</button>
        </form>
      </li>
    </ul>
  </div>
</div>