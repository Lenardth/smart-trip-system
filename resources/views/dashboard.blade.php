@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Dashboard</h4>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h5>Welcome, {{ Auth::user()->name }}!</h5>
                    <p class="text-muted">You are logged into the Tender Monitoring System.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-file-contract"></i> Active Tenders</h5>
                                    <p class="card-text display-6">24</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-check-circle"></i> Completed Projects</h5>
                                    <p class="card-text display-6">156</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card text-white bg-warning">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-chart-line"></i> Efficiency Score</h5>
                                    <p class="card-text display-6">94%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="/" class="btn btn-outline-primary">
                            <i class="fas fa-home"></i> Back to Homepage
                        </a>
                        <a href="{{ route('profile') }}" class="btn btn-primary">
                            <i class="fas fa-user"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
