@extends('layouts.staff.app')

@section('css')
{{-- <link href="{{ asset('/staff/css/base.css') }}" rel="stylesheet" type="text/css" /> --}}
@endsection

@section('content')
@include('staff.asp_web_common._reserve_confrim_edit')
@endsection
