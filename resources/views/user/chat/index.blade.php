@extends('layouts.user.app')

@section('content')
@include("user.common.header")
<main id="userChat">
	<ul class="subNavi">
	<li><a href="./"><img src="common/img/shared/ico_back.svg" alt=""></a></li>
	<li><a href=""><i class="icf-ico_plan"></i></a></li></ul>
	<div id="chat">
	<div class="user">
		<span class="time">2020.12.01 10:15</span>
		<p>こんにちは</p>
		</div>
	<div class="client">
		<img src="common/img/shared/prof_img.jpg" alt="">
		<div class="comment"><span class="time">2020.12.01 10:15</span>
		<p>こんにちは</p></div>
		</div>
	<div class="user">
		<span class="time">2020.12.01 10:15</span>
		<p>プランについて質問です</p>
		</div>
	<div class="client">
		<img src="common/img/shared/prof_img.jpg" alt="">
		<div class="comment"><span class="time">未読<br>
2020.12.01 10:15</span>
		<p>どのようなご質問でしょうか？</p></div>
		</div>
		<div id="chatInput"><input type="text" id="messageInput"><input type="submit" value="" id="sendMessage"></div>
	</div>
</main>

<script src="{{ mix('/user/js/chat-index.js') }}"></script>
@endsection
