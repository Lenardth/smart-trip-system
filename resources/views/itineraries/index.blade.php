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

@push('styles')
<style>
.itin-toolbar {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    align-items: center;
    flex-wrap: wrap;
}
.itin-toolbar input {
    flex: 1;
    min-width: 200px;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-family: inherit;
    font-size: 14px;
    background: var(--card-bg);
    color: var(--deep);
    outline: none;
}
.itin-toolbar input:focus { border-color: var(--gold); }
.itin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
}
.itin-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(59,31,43,.06);
    transition: transform .2s, box-shadow .2s;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.itin-card:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(59,31,43,.12); }
.itin-card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
.itin-card-header h3 { font-size: 17px; font-weight: 700; color: var(--deep); margin: 0 0 5px; }
.itin-meta { font-size: 12px; color: var(--text-muted); margin: 0; display: flex; flex-wrap: wrap; gap: 4px; align-items: center; }
.itin-ref { font-size: 11px; font-family: monospace; background: var(--gold-dim); color: var(--deep); padding: 3px 8px; border-radius: 4px; white-space: nowrap; }
.itin-dates { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); flex-wrap: wrap; }
.itin-notes { font-size: 13px; color: var(--text-muted); line-height: 1.5; margin: 0; font-style: italic; }
.itin-footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; margin-top: auto; }
.itin-time { font-size: 12px; color: var(--text-sub); display: flex; align-items: center; gap: 5px; }
.itin-actions { display: flex; gap: 6px; flex-wrap: wrap; }
</style>
@endpush

@push('scripts')
<script>
function filterItineraries() {
    const q = document.getElementById('itinSearch').value.toLowerCase();
    document.querySelectorAll('.itin-card').forEach(c => {
        c.style.display = !q || c.dataset.search.includes(q) ? '' : 'none';
    });
}

function deleteItinerary(id, name) {
    const doDelete = () => {
        fetch('/itineraries/' + id, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        }).then(r => r.json()).then(d => {
            if (d.success) location.reload();
        });
    };

    if (typeof Swal === 'undefined') {
        if (confirm('Delete itinerary for ' + name + '?')) doDelete();
    } else {
        Swal.fire({
            title: 'Delete Itinerary?',
            text: name,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f44336',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Delete',
        }).then(r => { if (r.isConfirmed) doDelete(); });
    }
}
</script>
@endpush
