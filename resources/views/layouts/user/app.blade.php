<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>TRAVEL ONLINE</title>
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://use.typekit.net/cnl8vvq.css">
<link rel="stylesheet" href="{{ asset('user/css/default.css') }}">
<link rel="stylesheet" href="{{ asset('user/css/layout.css') }}">
<link rel="stylesheet" href="{{ asset('user/css/base.css') }}">
<link rel="stylesheet" href="{{ asset('user/css/icf.css') }}">
</head>

<body>
  @yield('content')
{{-- <script src="{{ asset('user/js/jquery-2.2.0.min.js') }}"></script> 
<script src="{{ asset('user/js/common.js') }}"></script>  --}}
<script src="{{ mix('/js/manifest.js') }}"></script>
<script src="{{ mix('/js/vendor.js') }}"></script>
<script src="{{ mix('/js/app.js') }}"></script>
</body>
</html>