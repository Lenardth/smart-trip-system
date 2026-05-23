@extends('layouts.base')

@push('body-attrs')
class="dashboard-page"
@endpush

@push('scripts')
<script id="dashboard-config" type="application/json">
{
    "pusherKey": @json(config('broadcasting.connections.pusher.key')),
    "pusherCluster": @json(config('broadcasting.connections.pusher.options.cluster', 'mt1')),
    "userId": {{ Auth::id() ?? 'null' }},
    "user": {
        "id": {{ Auth::id() ?? 'null' }},
        "name": @json(Auth::user()->name ?? ''),
        "firstName": @json(Auth::check() ? explode(' ', Auth::user()->name ?? '')[0] : ''),
        "avatar": @json(Auth::user()->profile_picture ? asset('storage/'.Auth::user()->profile_picture) : ''),
        "type": @json(Auth::user()->user_type ?? ''),
        "verified": {{ Auth::user()?->email_verified_at ? 'true' : 'false' }}
    }
}
</script>
@stack('scripts_body')
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