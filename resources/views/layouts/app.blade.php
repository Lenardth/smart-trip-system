@extends('layouts.authenticated')

@push('scripts')
    @vite(['resources/js/app.js'])
    @stack('scripts_body')
@endpush

@section('content')
    @yield('content')
@endsection
