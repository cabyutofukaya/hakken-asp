@extends(is_string(url()->current()) && strpos(url()->current(), env('ADMIN_DOMAIN')) !== false ? 'errors::admin' : 'errors::asp')

@section('code', '429')
@section('sub_message', 'TOO MANY REQUESTS')
@section('message', __('Too Many Requests'))
