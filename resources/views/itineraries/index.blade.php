@extends('layouts.authenticated')

@section('title', 'My Itineraries — Smart Booking')
@section('page-title', 'My Itineraries')
@section('page-description', 'All your saved trip plans')

@section('content')

@if($itineraries->count() > 0)

<div class="itin-toolbar">
    <input type="search" id="itinSearch" placeholder="Search itineraries…" oninput="filterItineraries()">
    <a href="{{ route('plan-trip') }}" class="primary-button" style="text-decoration:none;white-space:nowrap;">
        <i class="fas fa-plus"></i> New Itinerary
    </a>
</div>

<div class="itin-grid" id="itinGrid">
    @foreach($itineraries as $itin)
    <div class="itin-card" data-search="{{ strtolower($itin->destination . ' ' . $itin->mood) }}">
        <div class="itin-card-header">
            <div>
                <h3>{{ $itin->destination }}</h3>
                <p class="itin-meta">
                    <i class="fas fa-heart" style="color:var(--gold);"></i> {{ ucfirst($itin->mood) }}
                    &nbsp;·&nbsp;
                    <i class="fas fa-users"></i> {{ ucfirst(str_replace('_', ' ', $itin->companion)) }}
                    &nbsp;·&nbsp;
                    <i class="fas fa-dollar-sign"></i> ${{ number_format($itin->budget) }}
                </p>
            </div>
            <span class="itin-ref">{{ $itin->itinerary_id }}</span>
        </div>
        <div class="itin-dates">
            <span><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($itin->departure_date)->format('M j, Y') }}</span>
            <i class="fas fa-arrow-right" style="color:var(--text-muted);font-size:11px;"></i>
            <span><i class="fas fa-calendar-check"></i> {{ \Carbon\Carbon::parse($itin->return_date)->format('M j, Y') }}</span>
        </div>
        @if($itin->requirements)
            <p class="itin-notes">{{ Str::limit($itin->requirements, 100) }}</p>
        @endif
        <div class="itin-footer">
            <span class="itin-time"><i class="fas fa-clock"></i> {{ $itin->created_at->diffForHumans() }}</span>
            <div class="itin-actions">
                <a href="{{ route('itineraries.show', $itin->id) }}" class="secondary-button" style="padding:7px 14px;font-size:13px;text-decoration:none;">
                    <i class="fas fa-eye"></i> View
                </a>
                <form method="POST" action="{{ route('itineraries.export.post') }}" target="_blank" style="display:inline;">
                    @csrf
                    <input type="hidden" name="mood"          value="{{ $itin->mood }}">
                    <input type="hidden" name="destination"   value="{{ $itin->destination }}">
                    <input type="hidden" name="companion"     value="{{ $itin->companion }}">
                    <input type="hidden" name="travelers"     value="{{ $itin->travelers }}">
                    <input type="hidden" name="departureDate" value="{{ $itin->departure_date }}">
                    <input type="hidden" name="returnDate"    value="{{ $itin->return_date }}">
                    <input type="hidden" name="budget"        value="{{ $itin->budget }}">
                    <input type="hidden" name="requirements"  value="{{ $itin->requirements }}">
                    <button type="submit" class="secondary-button" style="padding:7px 14px;font-size:13px;">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </form>
                <button class="secondary-button" style="padding:7px 14px;font-size:13px;color:var(--danger);border-color:var(--danger);"
                        onclick="deleteItinerary({{ $itin->id }}, '{{ addslashes($itin->destination) }}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

@else

<div class="empty-state" style="margin-top:40px;">
    <i class="fas fa-map-marked-alt"></i>
    <h3>No Itineraries Yet</h3>
    <p>Use the trip planner to generate and save your first itinerary.</p>
    <a href="{{ route('plan-trip') }}" class="primary-button" style="text-decoration:none;margin-top:16px;display:inline-flex;">
        <i class="fas fa-route"></i> Plan a Trip
    </a>
</div>

@endif

@endsection