@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
<h1><span class="material-icons">upload</span>JAL-HAD 支払管理</h1>
  <div id="searchBox">
      <div id="inputList">
        <ul class="sideList">
          <li class="wd20"><span class="inputLabel">ステータス</span>
            <div class="selectBox">
      <select>
        <option>すべて</option>
        <option>未払のみ</option></select></div>
          </li>
          <li class="wd30"><span class="inputLabel">予約番号</span>
            <input type="text">
          </li>
          <li class="wd25"><span class="inputLabel">仕入先</span>
            <input type="text">
          </li>
          <li class="wd25 mr00"><span class="inputLabel">出金担当</span>
            <div class="selectBox">
      <select>
        <option>すべて</option>
        <option>豊島 章宏</option>
        <option>山田 太郎</option></select></div>
          </li>
        </ul>
        <ul class="sideList">
      <li class="wd25"><span class="inputLabel">商品名</span>
      <input type="text"></li>
      <li class="wd25"><span class="inputLabel">商品コード</span><input type="text"></li>
        </ul>
    <div class="toggleOption"><p>検索オプション</p></div>
    <div id="searchOption">
      <ul class="sideList customSearch">
      <li><span class="inputLabel">カスタム項目<a href="/system/authority/"><span class="material-icons">settings</span></a></span>
          <input type="text"></li>
      <li><span class="inputLabel">カスタム項目<a href="/system/authority/"><span class="material-icons">settings</span></a></span>
            <input type="text"></li>
      <li><span class="inputLabel">カスタムリスト項目<a href="/system/authority/"><span class="material-icons">settings</span></a></span>
            <div class="selectBox">
              <select>
                <option value="すべて" selected>すべて</option>
                <option value="システム管理者">システム管理者</option>
                <option value="オペレーター">オペレーター</option>
                <option value="経理">経理</option>
                <option value="一般">一般</option>
              </select>
            </div></li>
      <li><span class="inputLabel">カスタムリスト項目<a href="/system/authority/"><span class="material-icons">settings</span></a></span>
            <div class="selectBox">
              <select>
                <option value="すべて" selected>すべて</option>
                <option value="システム管理者">システム管理者</option>
                <option value="オペレーター">オペレーター</option>
                <option value="経理">経理</option>
                <option value="一般">一般</option>
              </select>
            </div></li>
      <li><span class="inputLabel">カスタムリスト項目<a href="/system/authority/"><span class="material-icons">settings</span></a></span>
            <div class="selectBox">
              <select>
                <option value="すべて" selected>すべて</option>
                <option value="システム管理者">システム管理者</option>
                <option value="オペレーター">オペレーター</option>
                <option value="経理">経理</option>
                <option value="一般">一般</option>
              </select>
            </div></li>
      <li><span class="inputLabel">カスタムリスト項目<a href="/system/authority/"><span class="material-icons">settings</span></a></span>
            <div class="selectBox">
              <select>
                <option value="すべて" selected>すべて</option>
                <option value="システム管理者">システム管理者</option>
                <option value="オペレーター">オペレーター</option>
                <option value="経理">経理</option>
                <option value="一般">一般</option>
              </select>
            </div></li>
      <li><span class="inputLabel">カスタムリスト項目<a href="/system/authority/"><span class="material-icons">settings</span></a></span>
            <div class="selectBox">
              <select>
                <option value="すべて" selected>すべて</option>
                <option value="システム管理者">システム管理者</option>
                <option value="オペレーター">オペレーター</option>
                <option value="経理">経理</option>
                <option value="一般">一般</option>
              </select>
            </div></li>
        </ul>		
    </div>
      </div>
      <div id="controlList">
        <ul>
          <li>
            <button class="orangeBtn icon-left"><span class="material-icons">search</span>検索</button>
          </li>
          <li>
            <button class="grayBtn slimBtn">条件クリア</button>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="tableWrap dragTable">
    <div class="tableCont managemnetTable">
      <table>
        <thead>
          <tr>
            <th class="sort"><span>商品コード</span></th>
            <th class="sort"><span>商品名</span></th>
            <th class="txtalc"><span>ステータス</span></th>
            <th class="sort"><span>参加者名</span></th>
            <th class="sort"><span>仕入先</span></th>
            <th class="sort txtalc"><span>仕入額</span></th>
            <th class="sort txtalc"><span>未払金額</span></th>
            <th class="txtalc"><span>利用日</span></th>
            <th class="txtalc"><span>予約詳細</span></th>
            <th class="sort"><span>出金担当</span></th>
            <th class="sort"><span>備考</span></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>JAL-HAD</td>
            <td>航空券</td>
            <td class="txtalc"><span class="status red">未払</span></td>
            <td>発券太郎</td>
            <td>株式会社仕入先A</td>
            <td class="txtalc">￥131,200</td>
            <td class="txtalc"><span class="payPeriod red js-modal-open" data-target="mdPayment">￥131,200</span></td>
            <td class="txtalc">2021/08/04</td>
            <td class="txtalc"><a href="/estimates/reserve/info.html"><span class="material-icons">event_note</span></a></td>
<td>豊島 章宏</td>
            <td>-</td>
          </tr>
          <tr>
            <td>JAL-HAD</td>
            <td>航空券</td>
            <td class="txtalc"><span class="status red">未払</span></td>
            <td>発券二郎</td>
            <td>株式会社仕入先A</td>
            <td class="txtalc">￥131,200</td>
            <td class="txtalc"><span class="payPeriod red js-modal-open" data-target="mdPayment">￥131,200</span></td>
            <td class="txtalc">2021/08/04</td>
            <td class="txtalc"><a href="/estimates/reserve/info.html"><span class="material-icons">event_note</span></a></td>
<td>豊島 章宏</td>
            <td>-</td>
          </tr>
          <tr>
            <td>JAL-HAD</td>
            <td>航空券</td>
            <td class="txtalc"><span class="status red">未払</span></td>
            <td>発券三郎</td>
            <td>株式会社仕入先A</td>
            <td class="txtalc">￥131,200</td>
            <td class="txtalc"><span class="payPeriod red js-modal-open" data-target="mdPayment">￥131,200</span></td>
            <td class="txtalc">2021/08/04</td>
            <td class="txtalc"><a href="/estimates/reserve/info.html"><span class="material-icons">event_note</span></a></td>
<td>豊島 章宏</td>
            <td>-</td>
          </tr>
        </tbody>
      </table>
    <ol id="pageNation">
    <li><a href="#"><span class="material-icons">first_page</span></a></li>
      <li><span class="stay">1</span></li>
      <li><a href="#">2</a></li>
    <li><a href="#"><span class="material-icons">last_page</span></a></li>
    </ol>
    </div>
  </div>
</main>



<div id="mdPayment" class="wideModal modal js-modal mgModal">
  <div class="modal__bg js-modal-close"></div>
  <div class="modal__content">
    <p class="mdTit mb20">出金登録</p>
	  <h3>支払情報</h3>
    <ul class="sideList half mb30">
	  <li>
          <table class="baseTable">
            <tbody>
              <tr>
                <th>予約番号</th>
                <td>CA2108A001</td>
              </tr>
              <tr>
                <th>支払金額</th>
                <td>￥131,200</td>
              </tr>
              <tr>
                <th>未払額</th>
                <td>￥131,200</td>
              </tr>
            </tbody>
          </table></li>
	  <li>
          <table class="baseTable">
            <tbody>
              <tr>
                <th>仕入先</th>
                <td>キャブステーション</td>
              </tr>
              <tr>
                <th>支払予定日</th>
                <td>2021/07/20</td>
              </tr>
              <tr>
                <th>商品コード</th>
                <td>JAL-HAD</td>
              </tr>
              <tr>
                <th>商品名</th>
                <td>航空券</td>
              </tr>
            </tbody>
          </table></li>
	  </ul>
	  
	  <h3>出金履歴</h3>
	  <div class="modalPriceList history">
			<table class="baseTable">
				<thead>
				<tr>
					<th>出金日</th>
					<th>登録日</th>
					<th>出金額</th>
					<th class="txtalc">出金方法</th>
					<th class="txtalc wd10">削除</th>
				</tr></thead>
				<tbody>
				<tr>
					<td>2021/08/20</td>
					<td>2021/08/30</td>
					<td>￥50,000</td>
					<td class="txtalc">銀行振込</td>
					
                <td class="txtalc"><span class="material-icons">delete</span></td></tr>
				</tbody>
				</table>
	  </div>
	  <h3>出金詳細</h3>
	  <ul class="baseList mb20">
	  <li class="wd70"><span class="inputLabel">出金額</span>
		  <div class="buttonSet">
			  <input type="text" class="wd60"><button class="blueBtn wd40">未払金額を反映</button></div></li></ul>
    <ul class="sideList half mb30">
	  <li><span class="inputLabel">出金日</span>
		  <div class="calendar"><input type="text"></div></li>
	  <li><span class="inputLabel">登録日</span>
		  <div class="calendar"><input type="text"></div></li>
	  <li><span class="inputLabel">出金方法</span>
		  <div class="selectBox"><select>
			  <option>銀行振込</option></select></div></li>
	  <li><span class="inputLabel">出金担当者</span>
		  <div class="selectBox"><select>
			  <option>豊島 章宏</option></select></div></li>
	  <li class="wd100 mr00"><span class="inputLabel">備考</span>
		  <textarea cols="3"></textarea></li></ul>
    <ul class="sideList">
      <li class="wd50">
        <button class="grayBtn js-modal-close">閉じる</button>
      </li>
      <li class="wd50 mr00">
        <button class="blueBtn">登録する</button>
      </li>
    </ul>
  </div>
</div>
	
<div id="mdEditPayday" class="modal js-modal mgModal">
  <div class="modal__bg js-modal-close"></div>
  <div class="modal__content">
    <p class="mdTit mb20">支払予定日変更</p>

    <ul class="baseList mb40">
	  <li><span class="inputLabel">支払予定日</span>
		  <div class="calendar"><input type="text" value="2021/11/02"></div></li></ul>
    <ul class="sideList">
      <li class="wd50">
        <button class="grayBtn js-modal-close">閉じる</button>
      </li>
      <li class="wd50 mr00">
        <button class="blueBtn">変更する</button>
      </li>
    </ul>
  </div>
</div>
<script src="{{ mix('/staff/js/management_payment-item.js') }}"></script>
@endsection
