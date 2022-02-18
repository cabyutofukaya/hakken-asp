<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>HAKKEN MANAGEMENT SYSTEM</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="{{ asset('/admin/css/app.css') }}" rel="stylesheet" type="text/css" />
<script src="{{ mix('/js/manifest.js') }}"></script>
<script src="{{ mix('/js/vendor.js') }}"></script>
<script src="{{ mix('/js/app.js') }}"></script>
</head>
<body>

    @if(Auth::guard('admin')->check())
        @include('admin.common.header')
        <div id="content">
            @include("admin.common.side_menu")
            @include("admin.common.message")
            @yield('content')
        </div>
    @else
        @yield('content')
    @endif

    <script src="{{ asset('admin/js/common.js') }}"></script>
    @yield('js')
</body>
</html>