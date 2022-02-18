@extends(is_string(url()->current()) && strpos(url()->current(), env('ADMIN_DOMAIN')) !== false ? 'errors::admin' : 'errors::asp')

@section('code', '401')
@section('sub_message', 'UNAUTHORIZED')
@section('message', __($exception->getMessage() ?: 'Unauthorized'))
