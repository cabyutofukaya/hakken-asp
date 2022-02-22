
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>HAKKEN MANAGEMENT SYSTEM</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
<link href="{{ asset('/staff/css/default.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/staff/css/layout.css') }}" rel="stylesheet" type="text/css" />
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
<script src="{{ mix('/js/manifest.js') }}"></script>
<script src="{{ mix('/js/vendor.js') }}"></script>
<script src="{{ mix('/js/app.js') }}"></script>
</head>
<body>
	
	@if(Auth::guard('staff')->check())
		@include("staff.common.header")
	@endif

	<div id="content">
		@if(Auth::guard('staff')->check())
			@include("staff.common.side_menu")
		@endif

	<main>
    <div class="notFound">
			
			<h1>
				@yield('code')
				<span>@yield('sub_message')</span>
			</h1>
			<p>@yield('message')</p>

			@if(Auth::guard('staff')->check()) {{-- ログイン時 --}}
				<a href='{{ route('staff.asp.estimates.reserve.index', request()->agencyAccount) }}' class="baseBtn">HOME</a>
			@else
				@if(request()->agencyAccount) {{-- アカウト情報あり → ログインページへ --}}
					<a href="{{ route('staff.login', request()->agencyAccount) }}" class="baseBtn">HOME</a>
				@else
					<a href="/" class="baseBtn">HOME</a>
				@endif
			@endif

		</div>
	</main>
</div>
<!-- js -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="{{ asset('staff/js/common.js') }}"></script>
{{-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> 
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script> 
<script>
    flatpickr.localize(flatpickr.l10ns.ja);
    flatpickr('.calendar input', {
        allowInput: true,
		dateFormat: "Y/m/d"
    });
</script> --}}
</body>
</html>