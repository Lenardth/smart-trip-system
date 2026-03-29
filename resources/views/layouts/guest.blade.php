@extends('layouts.base')

@push('styles')
    @vite(['resources/css/blade/layouts/guest.css', 'resources/css/blade/base.css'])
@endpush

@push('scripts')
    @stack('scripts_body')
@endpush

@section('body')
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div>
            <a href="/" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                <img src="{{ asset('img/travel-icon.png') }}" alt="Smart Booking Logo" style="width:48px;height:48px;object-fit:contain;">
                <span style="font-size:20px;font-weight:600;color:#1a1a1a;letter-spacing:-0.3px;">Smart Booking</span>
            </a>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
@endsection
