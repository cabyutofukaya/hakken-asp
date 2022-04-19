{{-- Hakkenサイトプレビュー用レイアウト --}}<!doctype html>
<html>
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>オンライン旅相談【HAKKEN】</title>
  <meta name="title" content="オンライン旅相談【HAKKEN】" />
  <meta name="keywords" content="旅行,オンライン,相談,予約,見積,ビデオチャット" />
  <meta property="og:title" content="オンライン旅相談【HAKKEN】">
  <meta property="og:type" content="article">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://use.typekit.net/ifs1xzg.css">
  <link href="{{ asset('/front/css/default.css') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('/front/css/layout.css') }}" rel="stylesheet" type="text/css" />
  <link href="{{ asset('/front/css/content.css') }}" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
  </head>
  <body>
    @yield('content')
  </body>
</html>