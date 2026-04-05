@extends('layouts.base')

@push('body-attrs')
class="dashboard-page"
@endpush

@push('scripts')
<script>
(function () {
    var cfg = window.__dashboardConfig = window.__dashboardConfig || {};
    cfg.pusherKey     = @json(config('broadcasting.connections.pusher.key'));
    cfg.pusherCluster = @json(config('broadcasting.connections.pusher.options.cluster', 'mt1'));
    cfg.userId        = {{ Auth::id() ?? 'null' }};
    cfg.user = {
        id:        {{ Auth::id() ?? 'null' }},
        name:      @json(Auth::user()->name ?? ''),
        firstName: @json(Auth::check() ? explode(' ', Auth::user()->name ?? '')[0] : ''),
        avatar:    @json(Auth::user()->avatar ?? ''),
        type:      @json(Auth::user()->user_type ?? ''),
        verified:  {{ Auth::user()?->hasVerifiedEmail() ? 'true' : 'false' }}
    };
})();
</script>
@stack('scripts_body')
@endpush

@section('body')
    @include('partials.dashboard-sidebar')

    <div class="@yield('page-class', 'main-content')" id="@yield('page-id', 'mainContent')">
        @if(!isset($hideHeader) && ($withHeader ?? true))
            @include('partials.dashboard-header')
        @endif
        <main @if(isset($fullPage) && $fullPage) style="padding:0;height:100%;" @endif>
            @yield('content')
        </main>
    </div>
@endsection