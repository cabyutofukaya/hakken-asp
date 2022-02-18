@extends(is_string(url()->current()) && strpos(url()->current(), env('ADMIN_DOMAIN')) !== false ? 'errors::admin' : 'errors::asp')

@section('code', '403')
@section('sub_message', 'FORBIDDEN')
@section('message', __($exception->getMessage() ?: 'Forbidden'))
