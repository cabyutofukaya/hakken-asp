<div id="inputArea" class="pt10">
  <h2 class="mb30 documentSubTit"><span class="material-icons">subject</span>出力項目設定</h2>
    
  <ul class="baseList mt20">		
    <li class="wd40">
      <span class="inputLabel">宛名/自社情報共通設定</span>
      <div class="selectBox">
        <select name="document_common_id">
          @foreach($formSelects['documentCommons'] as $val => $str)
            <option value="{{ $val }}"@if($defaultValue['document_common_id'] === $val) selected @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
    <li class="wd40">
      <span class="inputLabel req">表題</span><input type="text" name="title" value="{{ $defaultValue['title'] ?? '' }}">
    </li>
    <li class="wd100"><span class="inputLabel">但し書き</span>
      <textarea rows="5" placeholder="但
上記正に領収いたしました" name="proviso">{{ $defaultValue['proviso'] ?? '' }}</textarea>
    </li>
    <li class="wd100"><span class="inputLabel" placeholdar="例）
    内訳
    税別金額：
    消費税額：">備考</span>
      <textarea rows="5" name="note">{{ $defaultValue['note'] ?? '' }}</textarea>
    </li>
  </ul>
</div>