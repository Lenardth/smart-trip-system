@extends('layouts.authenticated')

@section('title', 'Itinerary — {{ $itinerary->destination }}')
@section('page-title', $itinerary->destination)
@section('page-description', 'Trip itinerary details')

@section('content')

<div class="itin-show-wrap">

    <div class="itin-show-header">
        <div class="itin-show-meta">
            <span><i class="fas fa-heart" style="color:var(--gold);"></i> {{ ucfirst($itinerary->mood) }}</span>
            <span><i class="fas fa-users"></i> {{ ucfirst(str_replace('_', ' ', $itinerary->companion)) }}</span>
            <span><i class="fas fa-dollar-sign"></i> ${{ number_format($itinerary->budget) }} budget</span>
            <span><i class="fas fa-user-friends"></i> {{ $itinerary->travelers }} {{ Str::plural('traveller', $itinerary->travelers) }}</span>
        </div>
        <div class="itin-show-ref">{{ $itinerary->itinerary_id }}</div>
    </div>

    <div class="itin-show-dates">
        <div class="date-block">
            <label>Departure</label>
            <strong>{{ \Carbon\Carbon::parse($itinerary->departure_date)->format('D, M j, Y') }}</strong>
        </div>
        <div class="date-arrow"><i class="fas fa-plane"></i></div>
        <div class="date-block">
            <label>Return</label>
            <strong>{{ \Carbon\Carbon::parse($itinerary->return_date)->format('D, M j, Y') }}</strong>
        </div>
        <div class="date-block">
            <label>Duration</label>
            <strong>{{ \Carbon\Carbon::parse($itinerary->departure_date)->diffInDays($itinerary->return_date) }} nights</strong>
        </div>
    </div>

    @if($itinerary->requirements)
    <div class="itin-notes-box">
        <i class="fas fa-sticky-note" style="color:var(--gold);"></i>
        <p>{{ $itinerary->requirements }}</p>
    </div>
    @endif

    <div class="itin-show-actions">
        <a href="{{ route('itineraries.index') }}" class="secondary-button" style="text-decoration:none;">
            <i class="fas fa-arrow-left"></i> All Itineraries
        </a>
        <form method="POST" action="{{ route('itineraries.export.post') }}" target="_blank" style="display:inline;">
            @csrf
            <input type="hidden" name="mood"          value="{{ $itinerary->mood }}">
            <input type="hidden" name="destination"   value="{{ $itinerary->destination }}">
            <input type="hidden" name="companion"     value="{{ $itinerary->companion }}">
            <input type="hidden" name="travelers"     value="{{ $itinerary->travelers }}">
            <input type="hidden" name="departureDate" value="{{ $itinerary->departure_date }}">
            <input type="hidden" name="returnDate"    value="{{ $itinerary->return_date }}">
            <input type="hidden" name="budget"        value="{{ $itinerary->budget }}">
            <input type="hidden" name="requirements"  value="{{ $itinerary->requirements }}">
            <button type="submit" class="primary-button">
                <i class="fas fa-file-pdf"></i> Download PDF
            </button>
        </form>
        <button class="secondary-button" style="color:var(--danger);border-color:var(--danger);"
                onclick="deleteItinerary({{ $itinerary->id }})">
            <i class="fas fa-trash"></i> Delete
        </button>
    </div>

</div>

@endsection