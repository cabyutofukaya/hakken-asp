@extends('layouts.user.app')

@section('content')
<header>
    <p id="logo"><img src="common/img/shared/logo.svg" alt="TRAVEL ONLINE"></p>
      <div id="home"><a href="./">TOP</a></div>
  </header>
  <main>
      <div id="mypageTop">
  <p class="name">山田 太郎<span>様</span></p>
          <dl class="mypageList">
          <dt>旅行日</dt>
          <dd>2020年12月20日<span class="small">(日)</span></dd>
          <dt>旅行先</dt>
          <dd>石川県金沢市</dd></dl>
          <p class="baseTit">相談内容</p>
          <dl class="mypageList">
              <dt>出発地</dt>
              <dd>東京都</dd>
              <dt>旅行目的</dt>
              <dd>家族旅行</dd>
              <dt>予算</dt>
              <dd class="blue">総額<span class="large">50,000</span>円</dd>
              <dt>人数</dt>
              <dd><ul class="parson"><li>大人<span>2</span>人</li>
                  <li>子供<span>0</span>人</li>
                  <li>幼児<span>0</span>人</li></ul></dd>
              <dt>興味</dt>
              <dd><ul class="int"><li>温泉</li><li>神社・神宮・寺院</li><li>カフェ</li></ul></dd>
          </dl>
          <p class="baseTit">ご提案一覧</p>
        <div class="teianBox">
          <div class="tantou"><img src="common/img/shared/prof_img.jpg" alt=""><h2><span>株式会社キャブステーション</span>山田 太郎</h2></div>
          <dl class="price">
              <dt>お見積り総額</dt>
              <dd><span>49,800</span>円</dd></dl>
          <p class="planBtn"><a href="">プランの詳細を見る</a></p>
              <ul class="planNav">
              <li><a href="{{ route('user.chat.index', 'c96a7193-e3bf-4119-baed-605de5d6344a') }}" target="_blank"><span class="alart">1</span><i class="icf-ico_message"></i>メッセージ</a></li>
              <li><a href="online.html"><i class="icf-ico_online"></i>オンライン相談</a></li>
              <li><a href="#"><i class="icf-ico_document"></i>見積り明細</a></li></ul>
          </div>
          <div class="teianBox">
          <div class="tantou"><img src="common/img/shared/prof_img.jpg" alt=""><h2><span>株式会社キャブステーション</span>山田 太郎</h2></div>
          <dl class="price">
              <dt>お見積り総額</dt>
              <dd><span>49,800</span>円</dd></dl>
          <p class="planBtn"><a href="">プランの詳細を見る</a></p>
              <ul class="planNav">
              <li><a href="{{ route('user.chat.index', 'c96a7193-e3bf-4119-baed-605de5d6344a') }}" target="_blank"><span class="alart">1</span><i class="icf-ico_message"></i>メッセージ</a></li>
              <li class="red"><a href=""><span class="check">!</span><i class="icf-ico_online"></i>オンライン相談<span>2020.11.20 11:00~</span></a></li>
              <li><a href="#"><i class="icf-ico_document"></i>見積り明細</a></li></ul>
          </div>
      </div>
  </main>
      <footer>© 2021 Cab-Station inc.
      </footer>
@endsection
