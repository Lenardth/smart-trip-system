@extends('layouts.base')

@push('body-attrs')
class="dashboard-page"
data-pusher-key="{{ config('broadcasting.connections.pusher.key') }}"
data-pusher-cluster="{{ config('broadcasting.connections.pusher.options.cluster', 'mt1') }}"
data-user-id="{{ Auth::id() }}"
data-user-name="{{ Auth::user()->name ?? '' }}"
data-user-first-name="{{ Auth::check() ? explode(' ', Auth::user()->name ?? '')[0] : '' }}"
data-user-avatar="{{ Auth::user()?->profile_picture ? asset('storage/'.Auth::user()->profile_picture) : '' }}"
data-user-type="{{ Auth::user()->user_type ?? '' }}"
data-user-verified="{{ Auth::user()?->email_verified_at ? 'true' : 'false' }}"
@endpush

@section('body')
    @include('partials.dashboard-sidebar')

    <div class="@yield('page-class', 'main-content')" id="@yield('page-id', 'mainContent')">
        @if(!isset($hideHeader) && ($withHeader ?? true))
            @include('partials.dashboard-header')
        @endif
        <main @if(isset($fullPage) && $fullPage) class="no-padding full-height" @endif>
            @yield('content')
        </main>
    </div>
@endsection
