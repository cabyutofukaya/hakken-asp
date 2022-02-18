<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>HAKKEN MANAGEMENT SYSTEM</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
<link href="{{ asset('/staff/css/default.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/staff/css/layout.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('/staff/css/base.css') }}" rel="stylesheet" type="text/css" />
@yield('css')
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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

        @yield('content')
    </div>

    @yield('modal') {{-- モーダル --}}

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="{{ asset('staff/js/common.js') }}"></script>
@yield('js')
</body>
</html>