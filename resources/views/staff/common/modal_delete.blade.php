<div id="mdDelete" class="modal js-modal">
  <div class="modal__bg js-modal-close"></div>
  <div class="modal__content">
    <p class="mdTit mb20">{{ isset($title) ? $title : "この設定を削除しますか？" }}</p>
    <ul class="sideList">
      <li class="wd50"><button class="grayBtn js-modal-close">キャンセル</button></li>
			<li class="wd50 mr00">
        <form method="post" action="{{ $actionUrl }}">
          @method('DELETE')
          @csrf
          <button class="redBtn" type="submit">削除する</button>
        </form>
      </li>
    </ul>
  </div>
</div>