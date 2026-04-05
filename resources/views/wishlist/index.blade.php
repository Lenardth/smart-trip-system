@extends('layouts.authenticated')

@section('title', 'My Wishlist — Smart Booking')
@section('page-title', 'My Wishlist')
@section('page-description', 'Your saved destinations')

@section('content')

@if($wishlistItems->count() > 0)

<div class="wishlist-stats-row">
    <div class="wstat"><span class="wstat-num">{{ $wishlistItems->count() }}</span><span class="wstat-label">Saved</span></div>
    <div class="wstat"><span class="wstat-num">{{ $wishlistItems->pluck('destination.country')->filter()->unique()->count() }}</span><span class="wstat-label">Countries</span></div>
    <div class="wstat"><span class="wstat-num">${{ number_format($wishlistItems->avg(fn($i) => $i->destination->price_from ?? 0)) }}</span><span class="wstat-label">Avg. From</span></div>
    <div class="wstat"><span class="wstat-num">{{ $wishlistItems->pluck('destination.category')->filter()->unique()->count() }}</span><span class="wstat-label">Categories</span></div>
</div>

<div class="wishlist-filter-bar">
    <input type="search" id="wishlistSearch" placeholder="Search destinations…" oninput="filterWishlist()">
    <select id="wishlistCategory" onchange="filterWishlist()">
        <option value="">All Categories</option>
        @foreach($wishlistItems->pluck('destination.category')->filter()->unique()->sort() as $cat)
            <option value="{{ $cat }}">{{ ucwords(str_replace('_',' ',$cat)) }}</option>
        @endforeach
    </select>
    <button class="secondary-button" onclick="clearAllWishlist()" style="font-size:13px;padding:8px 16px;">
        <i class="fas fa-trash-alt"></i> Clear All
    </button>
</div>

<div class="wishlist-grid" id="wishlistGrid">
    @foreach($wishlistItems as $item)
    @php $d = $item->destination; @endphp
    @if($d)
    <div class="wcard"
         data-name="{{ strtolower($d->name) }}"
         data-category="{{ $d->category }}">

        <div class="wcard-img" style="background-image:url('{{ $d->image_url ?: 'https://picsum.photos/seed/'.urlencode($d->name).'/600/400' }}')">
            @if($d->badge)
                <span class="wcard-badge">{{ $d->badge }}</span>
            @endif
            <button class="wcard-remove" onclick="removeWishlist({{ $d->id }}, '{{ addslashes($d->name) }}')" title="Remove">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="wcard-body">
            <h3>{{ $d->name }}</h3>
            <p class="wcard-location"><i class="fas fa-map-marker-alt"></i> {{ $d->country }}</p>
            @if($d->description)
                <p class="wcard-desc">{{ Str::limit($d->description, 90) }}</p>
            @endif
            <div class="wcard-footer">
                <div class="wcard-price">
                    @if($d->price_from)
                        <span>From</span> ${{ number_format($d->price_from) }}
                    @endif
                </div>
                <div class="wcard-actions">
                    <a href="{{ route('destinations.show', $d->id) }}" class="primary-button" style="padding:8px 14px;font-size:13px;text-decoration:none;">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="{{ route('plan-trip') }}?destination={{ urlencode($d->name) }}&mood={{ $d->mood }}" class="secondary-button" style="padding:8px 14px;font-size:13px;text-decoration:none;">
                        <i class="fas fa-route"></i> Plan
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endforeach
</div>

@else

<div class="empty-state" style="margin-top:40px;">
    <i class="fas fa-heart-broken"></i>
    <h3>Your Wishlist is Empty</h3>
    <p>Explore destinations and save the ones you love.</p>
    <a href="{{ route('discover') }}" class="primary-button" style="text-decoration:none;margin-top:16px;display:inline-flex;">
        <i class="fas fa-compass"></i> Browse Destinations
    </a>
</div>

@endif

@endsection

@push('styles')
<style>
.wishlist-stats-row {
    display: flex;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.wstat {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px 22px;
    text-align: center;
    flex: 1;
    min-width: 100px;
}
.wstat-num { display: block; font-size: 22px; font-weight: 700; color: var(--deep); }
.wstat-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; }

.wishlist-filter-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    align-items: center;
}
.wishlist-filter-bar input,
.wishlist-filter-bar select {
    padding: 9px 14px;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-family: inherit;
    font-size: 14px;
    background: var(--card-bg);
    color: var(--deep);
    outline: none;
    flex: 1;
    min-width: 160px;
}
.wishlist-filter-bar input:focus,
.wishlist-filter-bar select:focus { border-color: var(--gold); }

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 22px;
}
.wcard {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(59,31,43,.07);
    transition: transform .25s, box-shadow .25s;
    display: flex;
    flex-direction: column;
}
.wcard:hover { transform: translateY(-4px); box-shadow: 0 8px 22px rgba(59,31,43,.13); }
.wcard-img {
    height: 200px;
    background-size: cover;
    background-position: center;
    position: relative;
    flex-shrink: 0;
}
.wcard-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: var(--deep);
    color: var(--gold);
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 3px;
}
.wcard-remove {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(244,67,54,.85);
    color: #fff;
    border: none;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .2s;
}
.wcard:hover .wcard-remove { opacity: 1; }
.wcard-body { padding: 18px; flex: 1; display: flex; flex-direction: column; }
.wcard-body h3 { font-size: 17px; font-weight: 700; color: var(--deep); margin: 0 0 5px; }
.wcard-location { font-size: 13px; color: var(--text-muted); margin: 0 0 8px; display: flex; align-items: center; gap: 5px; }
.wcard-location i { color: var(--gold); }
.wcard-desc { font-size: 13px; color: var(--text-muted); line-height: 1.5; margin: 0 0 12px; flex: 1; }
.wcard-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; flex-wrap: wrap; gap: 8px; }
.wcard-price { font-size: 16px; font-weight: 700; color: var(--deep); }
.wcard-price span { font-size: 11px; font-weight: normal; color: var(--text-muted); }
.wcard-actions { display: flex; gap: 6px; }
</style>
@endpush

@push('scripts')
<script>
function filterWishlist() {
    const q    = document.getElementById('wishlistSearch').value.toLowerCase();
    const cat  = document.getElementById('wishlistCategory').value;
    document.querySelectorAll('.wcard').forEach(card => {
        const matchName = !q   || card.dataset.name.includes(q);
        const matchCat  = !cat || card.dataset.category === cat;
        card.style.display = (matchName && matchCat) ? '' : 'none';
    });
}

function removeWishlist(id, name) {
    if (typeof Swal === 'undefined') {
        if (!confirm('Remove ' + name + ' from wishlist?')) return;
        doRemove(id);
        return;
    }
    Swal.fire({
        title: 'Remove from Wishlist?',
        text: name,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f44336',
        cancelButtonColor: '#6b5b4f',
        confirmButtonText: 'Remove',
    }).then(r => { if (r.isConfirmed) doRemove(id); });
}

function doRemove(id) {
    fetch('/wishlist/' + id, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            // Remove card from DOM
            const cards = document.querySelectorAll('.wcard');
            cards.forEach(c => {
                if (c.querySelector('.wcard-remove[onclick*="' + id + '"]')) {
                    c.remove();
                }
            });
            // Update badge in sidebar
            try { localStorage.setItem('smartBookingWishlistUpdated', String(Date.now())); } catch(_) {}
            // Reload if grid empty
            if (!document.querySelector('.wcard')) location.reload();
        }
    }).catch(() => {});
}

function clearAllWishlist() {
    if (typeof Swal === 'undefined') {
        if (!confirm('Clear all wishlist items?')) return;
    } else {
        Swal.fire({
            title: 'Clear All?',
            text: 'This will remove all saved destinations.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f44336',
            cancelButtonColor: '#6b5b4f',
            confirmButtonText: 'Yes, clear all',
        }).then(r => { if (r.isConfirmed) location.reload(); });
    }
}
</script>
@endpush