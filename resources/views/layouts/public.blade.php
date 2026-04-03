@extends('layouts.base')

@push('scripts')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts_body')
@endpush

@section('body')
    <div style="min-height:100vh;display:flex;flex-direction:column;">
        @include('partials.public-navigation')

        <main style="flex:1;">
            @yield('content')
        </main>

        @include('partials.public-footer')
    </div>
@endsection
