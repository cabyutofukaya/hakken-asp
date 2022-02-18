@extends('layouts.user.app')

@section('content')
@include("user.common.header")
<main>
	<ul class="subNavi">
	<li><a href="./"><img src="{{ asset('user/img/shared/ico_back.svg') }}" alt=""></a></li>
	<li><a href=""><i class="icf-ico_plan"></i></a></li></ul>
	<div id="online">
	<p class="startTime">2020.11.20 11:30~</p>
		<div id="clientVideo"><div><video id="remoteStream"></video></div></div>
		<div id="userVideo"><video id="localStream"></video></div>
	</div>
</main>

<script src="{{ mix('/user/js/online-index.js') }}"></script>
@endsection
