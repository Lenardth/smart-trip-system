@extends('layouts.authenticated')

@push('scripts')
    @vite(['resources/js/app.js'])
@endpush

@section('content')
    @yield('content')
@endsection
