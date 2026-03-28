{{-- resources/views/layouts/public.blade.php --}}
@extends('layouts.base')

@push('styles')
    @vite(['resources/css/app.css'])
@endpush

@push('scripts')
    @vite(['resources/js/app.js'])
@endpush

@section('body')
    <div style="min-height:100vh; display:flex; flex-direction:column;">
        @include('partials.public-navigation')

        <main style="flex:1;">
            @yield('content')
        </main>

        @include('partials.public-footer')
    </div>
@endsection
