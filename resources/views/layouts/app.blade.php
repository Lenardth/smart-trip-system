@extends('layouts.authenticated')

@section('content')
    {{ $slot ?? '' }}
@endsection