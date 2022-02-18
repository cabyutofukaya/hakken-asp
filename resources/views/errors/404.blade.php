@extends(is_string(url()->current()) && strpos(url()->current(), env('ADMIN_DOMAIN')) !== false ? 'errors::admin' : 'errors::asp')

@section('code', '404')
@section('sub_message', 'NOT FOUND')
@section('message')
{!! nl2br(e("お探しのページが見つかりません。\n一時的にアクセスできない状況にあるか、移動もしくは削除された可能性があります。"))!!}
@endsection