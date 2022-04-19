@extends('layouts.staff.front')

@section('content')
<div id="wrapper">
	<header>
    <div id="logo">
			<a href="javascript:void(0)"><img src="{{ asset('front/img/shared/logo.svg') }}" alt="オンライン旅相談 HAKKEN"></a>
		</div>
    <nav>
      <ul>
      <li><a href="javascript:void(0)">新規会員登録</a></li>
      <li><a href="javascript:void(0)">ログイン</a></li>
    </ul>
    </nav>
    <p class="topCatch01">ONLINE TRAVEL CONSULTATION</p>
    <p class="topCatch02">日本中の旅行マイスターに<br>オンラインで相談！</p>
  </header>
  <main>
	<div class="searchHead">
		<div class="backList"><a href="javascript:void(0)">旅行マイスター 一覧へ</a></div></div>
		<div id="profInfo">
			<div class="resultCard">
				<div class="mainCard">
					<div class="bgImg">
						@if($coverPhoto)
							<img src="{{ $coverPhoto }}" alt="カバー写真">
						@endif
					</div>
					<div class="profCard">
						<div class="profImg">
							@if($profilePhoto)
								<img src="{{ $profilePhoto }}" alt="{{ Arr::get($input, "name") }}">
							@endif
						</div>
						<h3><span>
							{{ $webProfile->agency->company_name }} {{-- 会社名はDBに保存されている値から取得 --}}
						</span>
							{{ Arr::get($input, "name") }}
						<span>
							[{{ $webProfile->agency->prefecture->name }}] {{-- 都道府県はDBに保存されている値から取得 --}}
						</span>
					</h3>
				</div>
				{{-- <ul class="valuation">
					<li>相談件数<span>--</span>件</li>
					<li><a href="#voice">評価<span>--</span>pt</a></li>
				</ul> --}}
			</div>

			@if($purposes)
			<p class="tagTitle">得意なカテゴリー</p>
			<ul class="tag">
				@foreach($purposes as $purpose)
					<li>{{ $purpose }}</li>
				@endforeach
			</ul>
			@endif

			<p class="prTxt">{!! nl2br(e(Arr::get($input, "introduction"))) !!}</p>

			<div class="allConsul">
				<p class="baseBtn ico-consult js-modal-open" data-target="mdConsult">この旅行マイスターに相談する</p>
			</div>
		</div>
		<div class="samplePlan">
			<h2>いちおしの旅行プラン</h2>
			<ul class="halfColumn">
				@foreach($webProfile->staff->enabled_web_modelcourses as $webModelcourse)
				<li>
					<span>
						<div class="coursePh">
							@if(data_get($webModelcourse, 'web_modelcourse_photo.file_name'))
								<img src="{{ $consts['imageBaseUrl'] . $webModelcourse->web_modelcourse_photo->file_name }}" alt="{{ $webModelcourse->name }}">
							@endif
						</div>
						<ul class="courseTit">
							<li>
								<h3>{{ $webModelcourse->name }}</h3>
							</li>
							<li class="day">
								{{ Arr::get($consts['stays'], $webModelcourse->stays ) }}
							</li>
						</ul>
					</span>
					@if($webModelcourse->description)
					<p class="courseTxt">{!! nl2br(e(mb_strimwidth($webModelcourse->description, 0, 285, "..."))) !!}</p>
					@endif
				</li>
				@endforeach
			</ul>
		</div>

			{{-- <div id="voice">
				<ul class="valuationList">
					<li>相談件数<span>113</span>件</li>
					<li>評価<span>3.4</span>pt</li>
				</ul>
				<div class="voiceBox">
					<p>テキストが入ります。テキストが入ります。テキストが入ります。テキストが入ります。テキストが入ります。テキストが入ります。テキストが入ります。テキストが入ります。テキストが入ります。テキストが入ります。テキストが入ります。</p>
					<p>2022.01.24 〇〇〇様</p>
				</div>
			</div> --}}
		</div>
		
		<div id="companyFoot">
			<div class="compLogo">
				@if(data_get($webProfile, 'agency.web_company.logo_image'))
					<img src="{{ $consts['imageBaseUrl'] . $webProfile->agency->web_company->logo_image }}" alt="{{ $webProfile->agency->company_name }}">
				@endif
			<a href="#">会社概要</a></div>
			<div>
				<h4>{{ $webProfile->agency->company_name }}</h4>
				<p>〒{{ $webProfile->agency->zip_code_hyphen }} {{ $webProfile->agency->address_label }}</p></div>
			</div>
	</main>
</div>
<footer>
  <div id="footerIn">
    <ul>
      <li><a href="javascript:void(0)">会社案内</a></li>
      <li><a href="javascript:void(0)">プライバシーポリシー</a></li>
      <li><a href="javascript:void(0)">インターネット旅行取引の詳細</a></li>
      <li><a href="javascript:void(0)">利用規約</a></li>
      <li><a href="javascript:void(0)">お問い合わせ</a></li>
    </ul>
    <p id="copy">© {{ date('Y') }} Cab Station Co., Ltd.</p>
  </div>
</footer>
@endsection
