@extends(is_string(url()->current()) && strpos(url()->current(), env('ADMIN_DOMAIN')) !== false ? 'errors::admin' : 'errors::asp')

@section('code', '500')
@section('sub_message', 'SERVER ERROR')
@section('message', __('Server Error'))
