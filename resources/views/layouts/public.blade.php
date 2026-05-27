@extends('layouts.base')

@section('body')
    <div class="public-layout-wrap">

        @include('partials.public-navigation')

        <main class="public-layout-main">
            @yield('content')
        </main>

        @include('partials.public-footer')

    </div>
@endsection
