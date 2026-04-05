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

@push('styles')
<style>
.itin-show-wrap { max-width: 760px; }
.itin-show-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px 24px;
    margin-bottom: 16px;
}
.itin-show-meta { display: flex; flex-wrap: wrap; gap: 12px; font-size: 14px; color: var(--text-muted); }
.itin-show-meta span { display: flex; align-items: center; gap: 6px; }
.itin-show-meta i { color: var(--gold); }
.itin-show-ref { font-family: monospace; font-size: 13px; background: var(--gold-dim); color: var(--deep); padding: 4px 10px; border-radius: 4px; }
.itin-show-dates {
    display: flex;
    align-items: center;
    gap: 20px;
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px 24px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.date-block { display: flex; flex-direction: column; gap: 4px; }
.date-block label { font-size: 11px; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted); }
.date-block strong { font-size: 15px; color: var(--deep); }
.date-arrow { font-size: 20px; color: var(--gold); }
.itin-notes-box {
    display: flex;
    gap: 12px;
    background: rgba(201,169,110,.08);
    border: 1px solid rgba(201,169,110,.3);
    border-radius: 8px;
    padding: 16px 20px;
    margin-bottom: 16px;
    align-items: flex-start;
}
.itin-notes-box p { margin: 0; font-size: 14px; color: var(--text-muted); line-height: 1.6; }
.itin-show-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
</style>
@endpush

@push('scripts')
<script>
function deleteItinerary(id) {
    const doDelete = () => {
        fetch('/itineraries/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        }).then(r => r.json()).then(d => {
            if (d.success) window.location.href = '/itineraries';
        });
    };

    if (typeof Swal === 'undefined') {
        if (confirm('Delete this itinerary?')) doDelete();
    } else {
        Swal.fire({
            title: 'Delete Itinerary?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f44336',
            confirmButtonText: 'Delete',
        }).then(r => { if (r.isConfirmed) doDelete(); });
    }
}
</script>
@endpush