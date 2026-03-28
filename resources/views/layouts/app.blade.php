{{-- resources/views/layouts/app.blade.php --}}
@extends('layouts.authenticated')

@push('styles')
    @vite(['resources/css/app.css'])
@endpush

@push('scripts')
    @vite(['resources/js/app.js'])
@endpush

@section('content')
    @yield('content')
@endsection
