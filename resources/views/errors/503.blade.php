@extends(is_string(url()->current()) && strpos(url()->current(), env('ADMIN_DOMAIN')) !== false ? 'errors::admin' : 'errors::asp')

@section('code', '503')
@section('sub_message', 'Service Unavailable')
@section('message', __($exception->getMessage() ?: 'Service Unavailable'))
