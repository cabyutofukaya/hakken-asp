@extends(is_string(url()->current()) && strpos(url()->current(), env('ADMIN_DOMAIN')) !== false ? 'errors::admin' : 'errors::asp')

@section('code', '419')
@section('sub_message', 'PAGE EXPIRED')
@section('message', __('Page Expired'))
