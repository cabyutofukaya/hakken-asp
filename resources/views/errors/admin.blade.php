<!doctype html>
<html>
<head>
<meta charset="utf-8">
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
    <div id="content">
      @include('admin.common.header')
      @include("admin.common.side_menu")
      @include("admin.common.message")
    </div>
  @endif

  <h1>
    @yield('code')
    <span>@yield('sub_message')</span>
  </h1>
  <p>@yield('message')</p>
  
  <a href="/home" class="baseBtn">HOME</a>

  <script src="{{ asset('admin/js/common.js') }}"></script>
</body>
</html>